<?php

namespace App\Services;

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\SharePairingException;

class PaymentStateMachine
{
    /**
     * Payment states for UserSharePair.is_paid field
     */
    const PAYMENT_UNPAID = 0;        // Waiting for payment
    const PAYMENT_PAID = 1;          // Payment confirmed
    const PAYMENT_FAILED = 2;        // Payment failed/expired
    const PAYMENT_PROCESSING = 3;    // Payment submitted, pending confirmation

    /**
     * Payment states for UserSharePayment.status field  
     */
    const PAYMENT_STATUS_PENDING = 'pending';       // Payment submitted, awaiting confirmation
    const PAYMENT_STATUS_CONFIRMED = 'confirmed';   // Payment confirmed by admin
    const PAYMENT_STATUS_REJECTED = 'rejected';     // Payment rejected by admin
    const PAYMENT_STATUS_PROCESSING = 'processing'; // Payment being processed

    /**
     * Submit payment for a pairing
     */
    public function submitPayment(UserSharePair $pairing, array $paymentData): UserSharePayment
    {
        return DB::transaction(function () use ($pairing, $paymentData) {
            
            // Validate pairing can accept payment
            $this->validatePaymentSubmission($pairing);
            
            // Lock the pairing to prevent concurrent modifications
            $pairing = UserSharePair::where('id', $pairing->id)->lockForUpdate()->first();
            
            // Create payment record
            $payment = UserSharePayment::create([
                'user_share_pair_id' => $pairing->id,
                'user_id' => $pairing->user_id,
                'amount' => $paymentData['amount'],
                'payment_method' => $paymentData['payment_method'] ?? 'manual',
                'transaction_reference' => $paymentData['transaction_reference'] ?? null,
                'receipt_image' => $paymentData['receipt_image'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
                'status' => self::PAYMENT_STATUS_PENDING
            ]);
            
            // Update pairing status to processing
            $pairing->update(['is_paid' => self::PAYMENT_PROCESSING]);
            
            // Update buyer share status if this is the first payment
            $buyerShare = $pairing->pairedUserShare;
            if ($buyerShare->pairedShares()->where('is_paid', '>', 0)->count() === 1) {
                // This is the first payment - keep as paired but log progress
                Log::info('First payment submitted for buyer share', [
                    'buyer_ticket' => $buyerShare->ticket_no,
                    'pairing_id' => $pairing->id,
                    'amount' => $paymentData['amount']
                ]);
            }
            
            Log::info('Payment submitted successfully', [
                'payment_id' => $payment->id,
                'pairing_id' => $pairing->id,
                'buyer_ticket' => $buyerShare->ticket_no,
                'amount' => $paymentData['amount'],
                'status' => self::PAYMENT_STATUS_PENDING
            ]);
            
            return $payment;
        });
    }

    /**
     * Confirm payment (admin action)
     */
    public function confirmPayment(UserSharePayment $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            
            // Lock payment and related records
            $payment = UserSharePayment::where('id', $payment->id)->lockForUpdate()->first();
            $pairing = UserSharePair::where('id', $payment->user_share_pair_id)->lockForUpdate()->first();
            
            if (!$payment || !$pairing) {
                throw new SharePairingException('Payment or pairing not found');
            }
            
            // Validate current state
            if ($payment->status !== self::PAYMENT_STATUS_PENDING) {
                throw new SharePairingException(
                    "Cannot confirm payment: Current status is '{$payment->status}', expected 'pending'"
                );
            }
            
            // Update payment status
            $payment->update([
                'status' => self::PAYMENT_STATUS_CONFIRMED,
                'confirmed_at' => now(),
                'confirmed_by' => auth()->id()
            ]);
            
            // Update pairing status
            $pairing->update(['is_paid' => self::PAYMENT_PAID]);
            
            // Check if all buyer share payments are complete
            $buyerShare = UserShare::where('id', $pairing->user_share_id)->lockForUpdate()->first();
            $this->updateBuyerShareStatusAfterPayment($buyerShare);
            
            // Update seller share status
            $sellerShare = UserShare::where('id', $pairing->paired_user_share_id)->lockForUpdate()->first();
            $this->updateSellerShareStatusAfterPayment($sellerShare);
            
            Log::info('Payment confirmed successfully', [
                'payment_id' => $payment->id,
                'pairing_id' => $pairing->id,
                'buyer_ticket' => $buyerShare->ticket_no,
                'seller_ticket' => $sellerShare->ticket_no,
                'amount' => $payment->amount
            ]);
            
            return true;
        });
    }

    /**
     * Reject payment (admin action)
     */
    public function rejectPayment(UserSharePayment $payment, string $reason = null): bool
    {
        return DB::transaction(function () use ($payment, $reason) {
            
            // Lock payment and related records
            $payment = UserSharePayment::where('id', $payment->id)->lockForUpdate()->first();
            $pairing = UserSharePair::where('id', $payment->user_share_pair_id)->lockForUpdate()->first();
            
            if (!$payment || !$pairing) {
                throw new SharePairingException('Payment or pairing not found');
            }
            
            // Update payment status
            $payment->update([
                'status' => self::PAYMENT_STATUS_REJECTED,
                'rejected_at' => now(),
                'rejected_by' => auth()->id(),
                'rejection_reason' => $reason
            ]);
            
            // Reset pairing status to unpaid
            $pairing->update(['is_paid' => self::PAYMENT_UNPAID]);
            
            Log::info('Payment rejected', [
                'payment_id' => $payment->id,
                'pairing_id' => $pairing->id,
                'reason' => $reason
            ]);
            
            return true;
        });
    }

    /**
     * Handle payment timeout/failure
     */
    public function failPayment(UserSharePair $pairing, string $reason = 'Payment timeout'): bool
    {
        return DB::transaction(function () use ($pairing, $reason) {
            
            // Lock pairing and related shares
            $pairing = UserSharePair::where('id', $pairing->id)->lockForUpdate()->first();
            $buyerShare = UserShare::where('id', $pairing->user_share_id)->lockForUpdate()->first();
            $sellerShare = UserShare::where('id', $pairing->paired_user_share_id)->lockForUpdate()->first();
            
            // Mark pairing as failed
            $pairing->update(['is_paid' => self::PAYMENT_FAILED]);
            
            // Mark any pending payments as failed
            $pairing->payment()->where('status', self::PAYMENT_STATUS_PENDING)
                ->update([
                    'status' => self::PAYMENT_STATUS_REJECTED,
                    'rejected_at' => now(),
                    'rejection_reason' => $reason
                ]);
            
            // Restore seller shares
            $sellerShare->decrement('hold_quantity', $pairing->share);
            $sellerShare->increment('total_share_count', $pairing->share);
            
            // Update seller status
            $this->updateSellerShareStatusAfterFailure($sellerShare);
            
            // Update buyer status if needed
            $this->updateBuyerShareStatusAfterFailure($buyerShare);
            
            Log::info('Payment failed', [
                'pairing_id' => $pairing->id,
                'buyer_ticket' => $buyerShare->ticket_no,
                'seller_ticket' => $sellerShare->ticket_no,
                'reason' => $reason
            ]);
            
            return true;
        });
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStatistics(): array
    {
        return [
            'payments' => [
                'total' => UserSharePayment::count(),
                'pending' => UserSharePayment::where('status', self::PAYMENT_STATUS_PENDING)->count(),
                'confirmed' => UserSharePayment::where('status', self::PAYMENT_STATUS_CONFIRMED)->count(),
                'rejected' => UserSharePayment::where('status', self::PAYMENT_STATUS_REJECTED)->count(),
                'processing' => UserSharePayment::where('status', self::PAYMENT_STATUS_PROCESSING)->count(),
            ],
            'pairings' => [
                'unpaid' => UserSharePair::where('is_paid', self::PAYMENT_UNPAID)->count(),
                'paid' => UserSharePair::where('is_paid', self::PAYMENT_PAID)->count(),
                'failed' => UserSharePair::where('is_paid', self::PAYMENT_FAILED)->count(),
                'processing' => UserSharePair::where('is_paid', self::PAYMENT_PROCESSING)->count(),
            ],
            'amounts' => [
                'pending_total' => UserSharePayment::where('status', self::PAYMENT_STATUS_PENDING)->sum('amount'),
                'confirmed_total' => UserSharePayment::where('status', self::PAYMENT_STATUS_CONFIRMED)->sum('amount'),
            ]
        ];
    }

    /**
     * Validate payment submission
     */
    private function validatePaymentSubmission(UserSharePair $pairing): void
    {
        if ($pairing->is_paid !== self::PAYMENT_UNPAID) {
            throw new SharePairingException(
                "Cannot submit payment: Pairing payment status is '{$pairing->is_paid}', expected '0' (unpaid)"
            );
        }
        
        // Check if buyer share is in valid state
        $buyerShare = $pairing->pairedUserShare;
        if (!in_array($buyerShare->status, ['paired', 'pending'])) {
            throw new SharePairingException(
                "Cannot submit payment: Buyer share status is '{$buyerShare->status}', expected 'paired' or 'pending'"
            );
        }
        
        // Check for existing pending payments
        $existingPayment = UserSharePayment::where('user_share_pair_id', $pairing->id)
            ->where('status', self::PAYMENT_STATUS_PENDING)
            ->exists();
            
        if ($existingPayment) {
            throw new SharePairingException(
                "Cannot submit payment: A pending payment already exists for this pairing"
            );
        }
    }

    /**
     * Update buyer share status after payment confirmation
     */
    private function updateBuyerShareStatusAfterPayment(UserShare $buyerShare): void
    {
        // Check if all pairings are paid
        $unpaidPairings = $buyerShare->pairedShares()->where('is_paid', '!=', self::PAYMENT_PAID)->count();
        
        if ($unpaidPairings === 0) {
            // All payments completed
            $buyerShare->update(['status' => 'completed']);
            Log::info('Buyer share completed - all payments confirmed', [
                'buyer_ticket' => $buyerShare->ticket_no
            ]);
        } else {
            // Keep as paired - still have pending payments
            $buyerShare->update(['status' => 'paired']);
        }
    }

    /**
     * Update seller share status after payment confirmation
     */
    private function updateSellerShareStatusAfterPayment(UserShare $sellerShare): void
    {
        // Check if seller has any unpaid pairings
        $unpaidPairings = UserSharePair::where('paired_user_share_id', $sellerShare->id)
            ->where('is_paid', self::PAYMENT_UNPAID)
            ->count();
        
        if ($unpaidPairings === 0 && $sellerShare->hold_quantity <= 0) {
            // No unpaid pairings and no hold quantity - return to completed
            $sellerShare->update(['status' => 'completed']);
        } else {
            // Still has unpaid pairings or hold quantity - keep as paired
            $sellerShare->update(['status' => 'paired']);
        }
    }

    /**
     * Update seller share status after payment failure
     */
    private function updateSellerShareStatusAfterFailure(UserShare $sellerShare): void
    {
        // Refresh to get latest data
        $sellerShare->refresh();
        
        if ($sellerShare->hold_quantity <= 0) {
            // No hold quantity remaining - return to completed
            $sellerShare->update(['status' => 'completed']);
        } else {
            // Still has hold quantity - keep as paired
            $sellerShare->update(['status' => 'paired']);
        }
    }

    /**
     * Update buyer share status after payment failure
     */
    private function updateBuyerShareStatusAfterFailure(UserShare $buyerShare): void
    {
        $totalPairings = $buyerShare->pairedShares()->count();
        $failedPairings = $buyerShare->pairedShares()->where('is_paid', self::PAYMENT_FAILED)->count();
        $paidPairings = $buyerShare->pairedShares()->where('is_paid', self::PAYMENT_PAID)->count();
        
        if ($failedPairings === $totalPairings) {
            // All pairings failed
            $buyerShare->update(['status' => 'failed']);
        } elseif ($paidPairings > 0) {
            // Some payments succeeded - keep as paired or completed
            $unpaidPairings = $buyerShare->pairedShares()->where('is_paid', self::PAYMENT_UNPAID)->count();
            if ($unpaidPairings === 0) {
                $buyerShare->update(['status' => 'completed']);
            } else {
                $buyerShare->update(['status' => 'paired']);
            }
        }
        // Otherwise keep current status
    }
}
