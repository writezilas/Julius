<?php

namespace App\Services;

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use App\Events\ShareCompleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Confirm payment for a share pair
     *
     * @param int $pairId The user share pair ID
     * @return bool Success status
     */
    public function confirmPayment(int $pairId): bool
    {
        return DB::transaction(function () use ($pairId) {
            $pair = UserSharePair::with(['pairedUserShare', 'pairedShare'])
                ->findOrFail($pairId);
                
            // Mark this pair as paid
            $pair->is_paid = 1;
            $pair->save();
            
            Log::info("Payment confirmed for pair ID: {$pairId}");
            
            // Check if all pairs for this buyer share are now paid
            $buyerShare = $pair->pairedUserShare;
            $allPairsForBuyer = UserSharePair::where('user_share_id', $buyerShare->id)->get();
            
            $allPaid = $allPairsForBuyer->every(function ($p) {
                return $p->is_paid == 1;
            });
            
            if ($allPaid) {
                $this->completeShareTransaction($buyerShare, $allPairsForBuyer);
            }
            
            return true;
        });
    }
    
    /**
     * Complete the share transaction when all payments are confirmed
     */
    private function completeShareTransaction(UserShare $buyerShare, $allPairs): void
    {
        // Update buyer share to completed
        $buyerShare->status = 'completed';
        $buyerShare->save();
        
        // Update all seller shares to completed
        foreach ($allPairs as $pair) {
            $sellerShare = $pair->pairedShare;
            if ($sellerShare) {
                // Check if seller has any other unpaid pairs
                $otherUnpaidPairs = UserSharePair::where('paired_user_share_id', $sellerShare->id)
                    ->where('id', '!=', $pair->id)
                    ->where('is_paid', 0)
                    ->exists();
                
                // Only mark as completed if no other unpaid pairs exist
                if (!$otherUnpaidPairs) {
                    $sellerShare->status = 'completed';
                    $sellerShare->save();
                }
            }
        }
        
        // Fire event to close chat conversations
        event(new ShareCompleted($buyerShare));
        
        Log::info("Share transaction completed for buyer share ID: {$buyerShare->id}");
    }
    
    /**
     * Decline/reject a payment
     */
    public function declinePayment(int $paymentId, string $reason = null): bool
    {
        return DB::transaction(function () use ($paymentId, $reason) {
            $payment = UserSharePayment::findOrFail($paymentId);
            
            // Update payment status
            $payment->status = 'declined';
            if ($reason) {
                $payment->admin_note = $reason;
            }
            $payment->save();
            
            // The pair remains unpaid (is_paid = 0) so payment deadline logic will handle failure
            
            Log::info("Payment declined for payment ID: {$paymentId}", [
                'reason' => $reason,
                'pair_id' => $payment->user_share_pair_id
            ]);
            
            return true;
        });
    }
    
    /**
     * Get payment statistics for a buyer share
     */
    public function getPaymentStats(UserShare $buyerShare): array
    {
        $pairs = UserSharePair::where('user_share_id', $buyerShare->id)->get();
        
        $stats = [
            'total_pairs' => $pairs->count(),
            'paid_pairs' => $pairs->where('is_paid', 1)->count(),
            'pending_pairs' => $pairs->where('is_paid', 0)->count(),
            'failed_pairs' => $pairs->where('is_paid', 2)->count(),
            'completion_percentage' => 0,
            'is_fully_paid' => false,
        ];
        
        if ($stats['total_pairs'] > 0) {
            $stats['completion_percentage'] = round(
                ($stats['paid_pairs'] / $stats['total_pairs']) * 100, 
                2
            );
            $stats['is_fully_paid'] = $stats['paid_pairs'] == $stats['total_pairs'];
        }
        
        return $stats;
    }
    
    /**
     * Check if a share should be marked as failed due to payment timeout
     */
    public function checkPaymentTimeout(UserShare $buyerShare): bool
    {
        $paymentDeadlineMinutes = $buyerShare->payment_deadline_minutes ?? get_gs_value('bought_time') ?? 60;
        $paymentDeadline = $buyerShare->created_at->addMinutes($paymentDeadlineMinutes);
        
        return now()->gt($paymentDeadline);
    }
    
    /**
     * Mark share as failed and revert pairs
     */
    public function markShareAsFailed(UserShare $buyerShare, string $reason = 'Payment timeout'): bool
    {
        $pairingService = app(SharePairingService::class);
        
        try {
            $pairingService->revertFailedPairing($buyerShare);
            
            Log::info("Share marked as failed: {$buyerShare->ticket_no}", [
                'reason' => $reason,
                'buyer_id' => $buyerShare->user_id
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to mark share as failed: {$e->getMessage()}", [
                'share_id' => $buyerShare->id,
                'ticket_no' => $buyerShare->ticket_no
            ]);
            
            return false;
        }
    }
}
