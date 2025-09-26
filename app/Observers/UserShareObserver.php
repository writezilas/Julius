<?php

namespace App\Observers;

use App\Models\UserShare;
use App\Models\UserSharePair;
use Illuminate\Support\Facades\Log;
use App\Exceptions\SharePairingException;

class UserShareObserver
{
    /**
     * Handle the UserShare "creating" event.
     */
    public function creating(UserShare $userShare): void
    {
        // Set default status if not provided
        if (empty($userShare->status)) {
            $userShare->status = 'pending';
        }
        
        // Validate initial status
        $this->validateStatusTransition(null, $userShare->status, $userShare);
        
        // Initialize quantities if not set
        if ($userShare->total_share_count < 0) {
            $userShare->total_share_count = 0;
        }
        if ($userShare->hold_quantity < 0) {
            $userShare->hold_quantity = 0;
        }
        if ($userShare->sold_quantity < 0) {
            $userShare->sold_quantity = 0;
        }
        
        Log::info('Creating UserShare', [
            'ticket_no' => $userShare->ticket_no,
            'status' => $userShare->status,
            'user_id' => $userShare->user_id
        ]);
    }

    /**
     * Handle the UserShare "created" event.
     */
    public function created(UserShare $userShare): void
    {
        Log::info('UserShare created successfully', [
            'id' => $userShare->id,
            'ticket_no' => $userShare->ticket_no,
            'status' => $userShare->status,
            'user_id' => $userShare->user_id
        ]);
    }

    /**
     * Handle the UserShare "updating" event.
     */
    public function updating(UserShare $userShare): void
    {
        $originalStatus = $userShare->getOriginal('status');
        $newStatus = $userShare->status;
        
        // Validate status transition
        if ($originalStatus !== $newStatus) {
            $this->validateStatusTransition($originalStatus, $newStatus, $userShare);
            
            Log::info('UserShare status transition', [
                'id' => $userShare->id,
                'ticket_no' => $userShare->ticket_no,
                'from_status' => $originalStatus,
                'to_status' => $newStatus,
                'user_id' => $userShare->user_id
            ]);
        }
        
        // Validate quantity constraints
        $this->validateQuantities($userShare);
        
        // Validate pairing consistency
        if (in_array($newStatus, ['paired', 'completed']) && $originalStatus !== $newStatus) {
            $this->validatePairingConsistency($userShare, $newStatus);
        }
        
        // Handle suspension state changes
        if ($newStatus === 'suspended' && $originalStatus !== 'suspended') {
            $userShare->status_before_suspension = $originalStatus;
        } elseif ($originalStatus === 'suspended' && $newStatus !== 'suspended') {
            // Clear suspension status when resuming
            $userShare->status_before_suspension = null;
        }
    }

    /**
     * Handle the UserShare "updated" event.
     */
    public function updated(UserShare $userShare): void
    {
        $changes = $userShare->getChanges();
        
        if (!empty($changes)) {
            Log::info('UserShare updated', [
                'id' => $userShare->id,
                'ticket_no' => $userShare->ticket_no,
                'changes' => $changes
            ]);
        }
        
        // Post-update consistency validation
        if (isset($changes['status'])) {
            $this->validatePostUpdateConsistency($userShare);
        }
    }

    /**
     * Handle the UserShare "deleting" event.
     */
    public function deleting(UserShare $userShare): void
    {
        // Prevent deletion if share has active pairings
        if ($userShare->pairedShares()->where('is_paid', '!=', 2)->exists()) {
            throw new SharePairingException(
                "Cannot delete UserShare {$userShare->ticket_no}: Active pairings exist"
            );
        }
        
        Log::warning('UserShare being deleted', [
            'id' => $userShare->id,
            'ticket_no' => $userShare->ticket_no,
            'status' => $userShare->status,
            'user_id' => $userShare->user_id
        ]);
    }

    /**
     * Handle the UserShare "deleted" event.
     */
    public function deleted(UserShare $userShare): void
    {
        Log::info('UserShare deleted', [
            'id' => $userShare->id,
            'ticket_no' => $userShare->ticket_no
        ]);
    }

    /**
     * Validate status transitions
     */
    private function validateStatusTransition(?string $fromStatus, string $toStatus, UserShare $userShare): void
    {
        $validTransitions = [
            null => ['pending'], // Initial creation
            'pending' => ['paired', 'failed', 'suspended'],
            'paired' => ['completed', 'failed', 'suspended'],
            'completed' => ['suspended', 'paired', 'pending'], // Can go back to paired if needed
            'failed' => ['pending', 'suspended'], // Can retry
            'suspended' => ['pending', 'paired', 'completed', 'failed'], // Can resume to any previous state
            'running' => ['paired', 'completed', 'failed', 'suspended'], // Legacy status
            'partially_paired' => ['paired', 'completed', 'failed', 'suspended'], // Legacy status
            'active' => ['paired', 'completed', 'failed', 'suspended'] // Legacy status
        ];
        
        if (!isset($validTransitions[$fromStatus]) || !in_array($toStatus, $validTransitions[$fromStatus])) {
            throw new SharePairingException(
                "Invalid status transition for UserShare {$userShare->ticket_no}: '{$fromStatus}' -> '{$toStatus}'"
            );
        }
    }

    /**
     * Validate quantity constraints
     */
    private function validateQuantities(UserShare $userShare): void
    {
        if ($userShare->total_share_count < 0) {
            throw new SharePairingException(
                "UserShare {$userShare->ticket_no}: total_share_count cannot be negative"
            );
        }
        
        if ($userShare->hold_quantity < 0) {
            throw new SharePairingException(
                "UserShare {$userShare->ticket_no}: hold_quantity cannot be negative"
            );
        }
        
        if ($userShare->sold_quantity < 0) {
            throw new SharePairingException(
                "UserShare {$userShare->ticket_no}: sold_quantity cannot be negative"
            );
        }
        
        // Logical validation: total shares should be sum of available + held + sold
        // But we'll only warn about severe inconsistencies to avoid breaking existing data
        $total = $userShare->total_share_count + $userShare->hold_quantity + $userShare->sold_quantity;
        if ($userShare->share_will_get && abs($total - $userShare->share_will_get) > ($userShare->share_will_get * 0.1)) {
            Log::warning('UserShare quantity inconsistency detected', [
                'id' => $userShare->id,
                'ticket_no' => $userShare->ticket_no,
                'expected_total' => $userShare->share_will_get,
                'actual_total' => $total,
                'total_share_count' => $userShare->total_share_count,
                'hold_quantity' => $userShare->hold_quantity,
                'sold_quantity' => $userShare->sold_quantity
            ]);
        }
    }

    /**
     * Validate pairing consistency during status changes
     */
    private function validatePairingConsistency(UserShare $userShare, string $newStatus): void
    {
        if ($newStatus === 'paired') {
            // For buyer shares, should have active pairings
            if ($userShare->get_from === 'purchase') {
                $activePairings = $userShare->pairedShares()->where('is_paid', '!=', 2)->count();
                if ($activePairings === 0) {
                    Log::warning('Buyer share marked as paired without active pairings', [
                        'id' => $userShare->id,
                        'ticket_no' => $userShare->ticket_no
                    ]);
                }
            }
            // For seller shares, should have hold_quantity > 0
            else {
                if ($userShare->hold_quantity <= 0) {
                    Log::warning('Seller share marked as paired without hold quantity', [
                        'id' => $userShare->id,
                        'ticket_no' => $userShare->ticket_no,
                        'hold_quantity' => $userShare->hold_quantity
                    ]);
                }
            }
        }
        
        if ($newStatus === 'completed') {
            // For buyer shares, all pairings should be paid
            if ($userShare->get_from === 'purchase') {
                $unpaidPairings = $userShare->pairedShares()->where('is_paid', 0)->count();
                if ($unpaidPairings > 0) {
                    Log::warning('Buyer share marked as completed with unpaid pairings', [
                        'id' => $userShare->id,
                        'ticket_no' => $userShare->ticket_no,
                        'unpaid_pairings' => $unpaidPairings
                    ]);
                }
            }
        }
    }

    /**
     * Validate consistency after update
     */
    private function validatePostUpdateConsistency(UserShare $userShare): void
    {
        // Refresh to get latest relationships
        $userShare->load(['pairedShares', 'pairedWithThis']);
        
        // Check for orphaned pairings
        $orphanedBuyerPairings = $userShare->pairedShares()
            ->whereDoesntHave('pairedShare')
            ->count();
            
        if ($orphanedBuyerPairings > 0) {
            Log::error('Orphaned buyer pairings detected', [
                'user_share_id' => $userShare->id,
                'ticket_no' => $userShare->ticket_no,
                'orphaned_count' => $orphanedBuyerPairings
            ]);
        }
        
        // Check seller pairing consistency
        if ($userShare->status === 'paired' && $userShare->get_from !== 'purchase') {
            $activePairings = UserSharePair::where('paired_user_share_id', $userShare->id)
                ->where('is_paid', '!=', 2)
                ->count();
                
            if ($activePairings === 0 && $userShare->hold_quantity > 0) {
                Log::warning('Seller share has hold quantity but no active pairings', [
                    'user_share_id' => $userShare->id,
                    'ticket_no' => $userShare->ticket_no,
                    'hold_quantity' => $userShare->hold_quantity
                ]);
            }
        }
    }
}
