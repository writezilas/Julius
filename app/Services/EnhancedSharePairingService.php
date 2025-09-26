<?php

namespace App\Services;

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\SharePairingException;

class EnhancedSharePairingService
{
    /**
     * Create a share pairing with atomic state transitions
     */
    public function createPairing(UserShare $buyerShare, UserShare $sellerShare, int $quantity): UserSharePair
    {
        return DB::transaction(function () use ($buyerShare, $sellerShare, $quantity) {
            
            // Validate pairing preconditions
            $this->validatePairingPreconditions($buyerShare, $sellerShare, $quantity);
            
            // Lock rows to prevent concurrent modifications
            $buyerShare = UserShare::where('id', $buyerShare->id)->lockForUpdate()->first();
            $sellerShare = UserShare::where('id', $sellerShare->id)->lockForUpdate()->first();
            
            // Validate availability after locking
            $this->validateAvailability($buyerShare, $sellerShare, $quantity);
            
            // Update states atomically
            $this->updatePairingStates($buyerShare, $sellerShare, $quantity);
            
            // Create the pairing record
            $pairing = $this->createPairingRecord($buyerShare, $sellerShare, $quantity);
            
            // Log the successful pairing
            Log::info('Share pairing created successfully', [
                'pairing_id' => $pairing->id,
                'buyer_ticket' => $buyerShare->ticket_no,
                'seller_ticket' => $sellerShare->ticket_no,
                'quantity' => $quantity,
                'buyer_status' => $buyerShare->status,
                'seller_status' => $sellerShare->status
            ]);
            
            return $pairing;
        });
    }

    /**
     * Remove a pairing and restore states atomically
     */
    public function removePairing(UserSharePair $pairing, string $reason = 'Manual removal'): bool
    {
        return DB::transaction(function () use ($pairing, $reason) {
            
            // Load related shares with locking
            $buyerShare = UserShare::where('id', $pairing->user_share_id)->lockForUpdate()->first();
            $sellerShare = UserShare::where('id', $pairing->paired_user_share_id)->lockForUpdate()->first();
            
            if (!$buyerShare || !$sellerShare) {
                throw new SharePairingException('Cannot remove pairing: Related shares not found');
            }
            
            // Restore seller share quantities
            $sellerShare->decrement('hold_quantity', $pairing->share);
            $sellerShare->increment('total_share_count', $pairing->share);
            
            // Update buyer share status if no other pairings exist
            $remainingPairings = $buyerShare->pairedShares()->where('id', '!=', $pairing->id)->count();
            if ($remainingPairings === 0) {
                $buyerShare->update(['status' => 'pending']);
            }
            
            // Update seller share status
            $this->updateSellerStatusAfterUnpairing($sellerShare);
            
            // Delete the pairing
            $pairing->delete();
            
            Log::info('Share pairing removed successfully', [
                'pairing_id' => $pairing->id,
                'buyer_ticket' => $buyerShare->ticket_no,
                'seller_ticket' => $sellerShare->ticket_no,
                'quantity' => $pairing->share,
                'reason' => $reason
            ]);
            
            return true;
        });
    }

    /**
     * Handle payment failure with proper state transitions
     */
    public function handlePaymentFailure(UserSharePair $pairing): bool
    {
        return DB::transaction(function () use ($pairing) {
            
            // Lock related shares
            $buyerShare = UserShare::where('id', $pairing->user_share_id)->lockForUpdate()->first();
            $sellerShare = UserShare::where('id', $pairing->paired_user_share_id)->lockForUpdate()->first();
            
            // Mark pairing as failed
            $pairing->update(['is_paid' => 2]); // 2 = failed payment
            
            // Restore seller quantities
            $sellerShare->decrement('hold_quantity', $pairing->share);
            $sellerShare->increment('total_share_count', $pairing->share);
            
            // Check if buyer share should transition to failed
            $unpaidPairings = $buyerShare->pairedShares()->where('is_paid', 0)->count();
            $failedPairings = $buyerShare->pairedShares()->where('is_paid', 2)->count();
            $totalPairings = $buyerShare->pairedShares()->count();
            
            if ($unpaidPairings === 0 && $failedPairings === $totalPairings) {
                // All pairings failed - mark buyer share as failed
                $buyerShare->update(['status' => 'failed']);
            } else if ($unpaidPairings === 0) {
                // Some payments succeeded - keep as paired but log partial failure
                Log::warning('Partial payment failure for buyer share', [
                    'buyer_ticket' => $buyerShare->ticket_no,
                    'failed_pairings' => $failedPairings,
                    'total_pairings' => $totalPairings
                ]);
            }
            
            // Update seller status
            $this->updateSellerStatusAfterFailure($sellerShare);
            
            Log::info('Payment failure handled', [
                'pairing_id' => $pairing->id,
                'buyer_ticket' => $buyerShare->ticket_no,
                'seller_ticket' => $sellerShare->ticket_no,
                'buyer_status' => $buyerShare->status,
                'seller_status' => $sellerShare->status
            ]);
            
            return true;
        });
    }

    /**
     * Confirm payment and update states
     */
    public function confirmPayment(UserSharePair $pairing): bool
    {
        return DB::transaction(function () use ($pairing) {
            
            // Lock related shares
            $buyerShare = UserShare::where('id', $pairing->user_share_id)->lockForUpdate()->first();
            $sellerShare = UserShare::where('id', $pairing->paired_user_share_id)->lockForUpdate()->first();
            
            // Mark pairing as paid
            $pairing->update(['is_paid' => 1]);
            
            // Check if all payments for buyer share are complete
            $unpaidPairings = $buyerShare->pairedShares()->where('is_paid', '!=', 1)->count();
            
            if ($unpaidPairings === 0) {
                // All payments completed - transition to completed
                $buyerShare->update(['status' => 'completed']);
            }
            
            // Update seller status
            $this->updateSellerStatusAfterPayment($sellerShare);
            
            Log::info('Payment confirmed', [
                'pairing_id' => $pairing->id,
                'buyer_ticket' => $buyerShare->ticket_no,
                'seller_ticket' => $sellerShare->ticket_no,
                'buyer_status' => $buyerShare->status,
                'seller_status' => $sellerShare->status
            ]);
            
            return true;
        });
    }

    /**
     * Validate pairing preconditions
     */
    private function validatePairingPreconditions(UserShare $buyerShare, UserShare $sellerShare, int $quantity): void
    {
        // Check buyer share status
        if (!in_array($buyerShare->status, ['pending', 'paired'])) {
            throw new SharePairingException(
                "Invalid buyer share status '{$buyerShare->status}'. Expected: pending or paired"
            );
        }
        
        // Check seller share status
        if (!in_array($sellerShare->status, ['completed', 'paired'])) {
            throw new SharePairingException(
                "Invalid seller share status '{$sellerShare->status}'. Expected: completed or paired"
            );
        }
        
        // Check if seller has enough shares
        if ($sellerShare->total_share_count < $quantity) {
            throw new SharePairingException(
                "Insufficient seller shares. Available: {$sellerShare->total_share_count}, Requested: {$quantity}"
            );
        }
        
        // Check if buyer needs shares
        $buyerNeededShares = $buyerShare->share_will_get;
        $alreadyPairedShares = $buyerShare->pairedShares()->sum('share');
        
        if (($alreadyPairedShares + $quantity) > $buyerNeededShares) {
            throw new SharePairingException(
                "Pairing would exceed buyer's required shares. Needed: {$buyerNeededShares}, Already paired: {$alreadyPairedShares}, Requested: {$quantity}"
            );
        }
        
        // Validate quantity
        if ($quantity <= 0) {
            throw new SharePairingException('Pairing quantity must be positive');
        }
    }

    /**
     * Validate availability after locking
     */
    private function validateAvailability(UserShare $buyerShare, UserShare $sellerShare, int $quantity): void
    {
        if (!$buyerShare || !$sellerShare) {
            throw new SharePairingException('One or both shares were deleted during pairing process');
        }
        
        if ($sellerShare->total_share_count < $quantity) {
            throw new SharePairingException(
                "Seller shares became unavailable. Available: {$sellerShare->total_share_count}, Requested: {$quantity}"
            );
        }
    }

    /**
     * Update pairing states atomically
     */
    private function updatePairingStates(UserShare $buyerShare, UserShare $sellerShare, int $quantity): void
    {
        // Update seller quantities
        $sellerShare->decrement('total_share_count', $quantity);
        $sellerShare->increment('hold_quantity', $quantity);
        
        // Update statuses
        $buyerShare->update(['status' => 'paired']);
        $sellerShare->update(['status' => 'paired']);
    }

    /**
     * Create the pairing record
     */
    private function createPairingRecord(UserShare $buyerShare, UserShare $sellerShare, int $quantity): UserSharePair
    {
        return UserSharePair::create([
            'user_id' => $buyerShare->user_id,
            'user_share_id' => $buyerShare->id,
            'paired_user_share_id' => $sellerShare->id,
            'share' => $quantity,
            'is_paid' => 0
        ]);
    }

    /**
     * Update seller status after unpairing
     */
    private function updateSellerStatusAfterUnpairing(UserShare $sellerShare): void
    {
        // Check if seller has any remaining hold quantities
        if ($sellerShare->hold_quantity <= 0) {
            // No more shares on hold - return to completed status
            $sellerShare->update(['status' => 'completed']);
        }
        // Otherwise keep as paired
    }

    /**
     * Update seller status after payment failure
     */
    private function updateSellerStatusAfterFailure(UserShare $sellerShare): void
    {
        $this->updateSellerStatusAfterUnpairing($sellerShare);
    }

    /**
     * Update seller status after successful payment
     */
    private function updateSellerStatusAfterPayment(UserShare $sellerShare): void
    {
        // Check if seller has any unpaid pairings
        $unpaidPairings = UserSharePair::where('paired_user_share_id', $sellerShare->id)
            ->where('is_paid', 0)
            ->count();
        
        if ($unpaidPairings === 0) {
            // All pairings are paid - seller can remain as completed
            $sellerShare->update(['status' => 'completed']);
        }
        // Otherwise keep as paired
    }

    /**
     * Get pairing statistics for monitoring
     */
    public function getPairingStatistics(): array
    {
        return [
            'total_pairings' => UserSharePair::count(),
            'unpaid_pairings' => UserSharePair::where('is_paid', 0)->count(),
            'paid_pairings' => UserSharePair::where('is_paid', 1)->count(),
            'failed_pairings' => UserSharePair::where('is_paid', 2)->count(),
            'buyer_shares_paired' => UserShare::where('status', 'paired')->whereHas('pairedShares')->count(),
            'seller_shares_paired' => UserShare::where('status', 'paired')->whereHas('pairedWithThis')->count(),
            'inconsistent_states' => $this->findInconsistentStates()
        ];
    }

    /**
     * Find shares with inconsistent states
     */
    private function findInconsistentStates(): array
    {
        $inconsistencies = [];
        
        // Find buyer shares marked as 'paired' but with no pairings
        $buyerInconsistencies = UserShare::where('status', 'paired')
            ->whereDoesntHave('pairedShares')
            ->pluck('id', 'ticket_no')
            ->toArray();
        
        if (!empty($buyerInconsistencies)) {
            $inconsistencies['buyers_paired_without_pairings'] = $buyerInconsistencies;
        }
        
        // Find seller shares marked as 'paired' but with no hold quantities
        $sellerInconsistencies = UserShare::where('status', 'paired')
            ->where('hold_quantity', 0)
            ->pluck('id', 'ticket_no')
            ->toArray();
        
        if (!empty($sellerInconsistencies)) {
            $inconsistencies['sellers_paired_without_holds'] = $sellerInconsistencies;
        }
        
        return $inconsistencies;
    }

    /**
     * Fix inconsistent states
     */
    public function fixInconsistentStates(): array
    {
        $fixed = [];
        
        DB::transaction(function () use (&$fixed) {
            // Fix buyer shares paired without pairings
            $invalidBuyers = UserShare::where('status', 'paired')
                ->whereDoesntHave('pairedShares')
                ->get();
            
            foreach ($invalidBuyers as $buyer) {
                $buyer->update(['status' => 'pending']);
                $fixed['buyers_fixed'][] = $buyer->ticket_no;
            }
            
            // Fix seller shares paired without hold quantities
            $invalidSellers = UserShare::where('status', 'paired')
                ->where('hold_quantity', 0)
                ->get();
            
            foreach ($invalidSellers as $seller) {
                $seller->update(['status' => 'completed']);
                $fixed['sellers_fixed'][] = $seller->ticket_no;
            }
        });
        
        return $fixed;
    }
}
