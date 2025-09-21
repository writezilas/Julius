<?php

namespace App\Services;

use App\Models\UserShare;
use App\Models\UserSharePair;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShareStatusService
{
    /**
     * Get the accurate status for a share with context awareness
     * UPDATED LOGIC: Clear separation between bought and sold share statuses
     * 
     * @param UserShare $share The share to get status for
     * @param string|null $context 'bought' for bought-shares page, 'sold' for sold-shares page, null for auto-detect
     */
    public function getShareStatus(UserShare $share, ?string $context = null): array
    {
        // CONTEXT-SPECIFIC LOGIC: Handle bought vs sold share pages separately
        if ($context === 'bought') {
            return $this->getBoughtShareStatus($share);
        } elseif ($context === 'sold') {
            return $this->getSoldShareStatus($share);
        }
        
        // Auto-detect context when not provided
        $context = $this->detectContext($share);
        if ($context === 'bought') {
            return $this->getBoughtShareStatus($share);
        } else {
            return $this->getSoldShareStatus($share);
        }
    }

    /**
     * Get status for BOUGHT shares (buyer's perspective)
     * UPDATED: Removed "Maturing" status - buying and selling are independent trades
     * UPDATED: Added support for admin-allocated shares
     */
    private function getBoughtShareStatus(UserShare $share): array
    {
        // Admin-allocated shares special handling
        if ($share->get_from === 'allocated-by-admin' && $share->status === 'completed') {
            return [
                'status' => 'Admin Allocated',
                'class' => 'bg-success',
                'description' => 'Share allocated by administrator - no payment required'
            ];
        }

        // Failed shares
        if ($share->status === 'failed') {
            return [
                'status' => 'Failed',
                'class' => 'bg-danger',
                'description' => 'Share purchase failed'
            ];
        }

        // Pending payment (payment deadline timer active)
        if ($share->status === 'pending') {
            return [
                'status' => 'Payment Pending',
                'class' => 'bg-warning',
                'description' => 'Submit payment before deadline'
            ];
        }

        // Payment submitted, waiting for confirmation
        if ($share->status === 'paired') {
            // Use bought-specific pairing stats to prevent cross-contamination
            $pairingStats = $this->getBoughtSharePairingStats($share);
            if ($pairingStats['awaiting_confirmation'] > 0) {
                return [
                    'status' => 'Payment Submitted',
                    'class' => 'bg-info',
                    'description' => 'Payment submitted, awaiting seller confirmation'
                ];
            }
        }

        // Completed shares - payment confirmed, buying process complete
        // NOTE: We don't monitor selling maturity here since buying and selling are independent
        if ($share->status === 'completed') {
            return [
                'status' => 'Completed',
                'class' => 'bg-success',
                'description' => 'Share purchase completed successfully'
            ];
        }

        // Default fallback
        return [
            'status' => 'Processing',
            'class' => 'bg-secondary',
            'description' => 'Share is being processed'
        ];
    }

    /**
     * Get status for SOLD shares (seller's perspective)
     * UPDATED: 6 specific statuses as per new requirements
     */
    private function getSoldShareStatus(UserShare $share): array
    {
        // 1. FAILED - when sold trade fails
        if ($share->status === 'failed') {
            return [
                'status' => 'Failed',
                'class' => 'bg-danger',
                'description' => 'Sold trade failed'
            ];
        }

        // 2. SOLD - when shares are fully paired, paid and confirmed
        if ($share->status === 'sold' || 
            ($share->total_share_count == 0 && $share->hold_quantity == 0 && $share->sold_quantity > 0)) {
            return [
                'status' => 'Sold',
                'class' => 'bg-dark',
                'description' => 'Shares fully sold and confirmed'
            ];
        }

        // 3. RUNNING - when sell maturity timer is running and shares have not matured yet
        if ($share->status === 'completed' && $share->is_ready_to_sell == 0 && !$this->hasShareMatured($share)) {
            return [
                'status' => 'Running',
                'class' => 'bg-info',
                'description' => 'Share is in sell maturation period'
            ];
        }

        // Check if share is matured and ready to sell
        if ($share->is_ready_to_sell == 1 || $this->hasShareMatured($share)) {
            $pairingStats = $this->getSoldSharePairingStats($share);
            $totalInvestmentPlusEarning = ($share->share_will_get ?? 0) + ($share->profit_share ?? 0);
            $totalAmountPaired = $pairingStats['total_amount_paired'];

            // PRIORITY 1: Check for shares awaiting payment confirmation FIRST
            if ($pairingStats['awaiting_confirmation'] > 0) {
                // 5. PAIRED - Amount paired equals (investment + earning), no further pairing, awaiting confirmation
                if ($totalAmountPaired >= $totalInvestmentPlusEarning) {
                    return [
                        'status' => 'Paired',
                        'class' => 'bg-warning',
                        'description' => 'Fully paired, waiting for payment confirmation'
                    ];
                }
                // 5. PARTIALLY PAIRED - Amount paired is less than (investment + earning), further pairing needed
                else {
                    return [
                        'status' => 'Partially Paired',
                        'class' => 'bg-warning',
                        'description' => 'Partially paired, awaiting more buyers and payments'
                    ];
                }
            }

            // PRIORITY 2: Check pairing completeness for unpaid shares (without payment submitted)
            if ($pairingStats['unpaid'] > 0) {
                // 5. PAIRED - Amount paired equals (investment + earning), no further pairing
                if ($totalAmountPaired >= $totalInvestmentPlusEarning) {
                    return [
                        'status' => 'Paired',
                        'class' => 'bg-warning',
                        'description' => 'Fully paired, waiting for payment confirmation'
                    ];
                }
                // 5. PARTIALLY PAIRED - Amount paired is less than (investment + earning), further pairing needed
                else {
                    return [
                        'status' => 'Partially Paired',
                        'class' => 'bg-warning',
                        'description' => 'Partially paired, awaiting more buyers and payments'
                    ];
                }
            }

            // PRIORITY 3: Check payment status for paired shares
            if ($pairingStats['paid'] > 0) {
                // 6. PARTIALLY SOLD - When some shares are sold but others are still available
                if ($pairingStats['unpaid'] > 0 || $pairingStats['awaiting_confirmation'] > 0 || 
                    $share->total_share_count > 0 || $share->hold_quantity > 0) {
                    return [
                        'status' => 'Partially Sold',
                        'class' => 'bg-success',
                        'description' => 'Some shares sold, others available for pairing'
                    ];
                }
                // If all conditions are false, it means all shares are sold
                else {
                    return [
                        'status' => 'Sold',
                        'class' => 'bg-dark',
                        'description' => 'All shares sold and confirmed'
                    ];
                }
            }
            
            // PRIORITY 4: AVAILABLE - When is_ready_to_sell=1, all pairings are paid, and status is still "completed"
            // This handles the case where shares are mature and all paired amounts are paid but not yet marked as "sold"
            if ($share->status === 'completed' && 
                $pairingStats['unpaid'] == 0 && 
                $pairingStats['awaiting_confirmation'] == 0 && 
                $pairingStats['paid'] > 0 && 
                $totalAmountPaired >= $totalInvestmentPlusEarning) {
                return [
                    'status' => 'Available',
                    'class' => 'bg-info',
                    'description' => 'Shares matured and available for sale'
                ];
            }

            // PRIORITY 5: AVAILABLE - Initial status immediately the sell payment time is completed (no pairings)
            if ($pairingStats['total'] == 0) {
                return [
                    'status' => 'Available',
                    'class' => 'bg-info',
                    'description' => 'Shares matured and available for sale'
                ];
            }

            // FALLBACK: AVAILABLE - Share is ready to sell and has shares available
            // This should only be reached if there are no active pairings
            if ($share->total_share_count > 0 || $share->hold_quantity > 0) {
                return [
                    'status' => 'Available',
                    'class' => 'bg-info',
                    'description' => 'Shares matured and available for sale'
                ];
            }
        }

        // Default fallback
        return [
            'status' => 'Processing',
            'class' => 'bg-secondary',
            'description' => 'Share is being processed'
        ];
    }

    /**
     * Auto-detect context based on share properties
     * FIXED: Admin-allocated shares can appear in both bought and sold contexts
     */
    private function detectContext(UserShare $share): string
    {
        // Purchase shares logic
        if ($share->get_from === 'purchase') {
            // If not matured yet, show in bought context
            if ($share->is_ready_to_sell == 0 && !$this->hasShareMatured($share)) {
                return 'bought';
            }
            // If matured, could appear in sold context
            return 'sold';
        }

        // Admin-allocated and referral shares can appear in BOTH contexts
        // The key insight: if they have completed the buying phase and are in maturation,
        // they should be treated as 'sold' context when they need to show sell maturity timer
        if (in_array($share->get_from, ['allocated-by-admin', 'refferal-bonus'])) {
            // If the share is completed but not ready to sell yet (in sell maturation period),
            // it should show in SOLD context to display the sell maturity timer
            if ($share->status === 'completed' && $share->is_ready_to_sell == 0 && 
                $share->start_date && $share->period) {
                return 'sold';
            }
            
            // If ready to sell, could be selling context
            if ($share->is_ready_to_sell == 1) {
                return 'sold';
            }
            
            // Otherwise, default to bought context
            return 'bought';
        }

        // Default to sold context for other cases
        return 'sold';
    }

    /**
     * Get pairing statistics for BOUGHT shares (buyer's perspective only)
     * Only considers pairings where this share is the BUYER
     */
    public function getBoughtSharePairingStats(UserShare $share): array
    {
        $paidPairings = 0;
        $failedPairings = 0;
        $awaitingConfirmation = 0;
        $genuinelyUnpaid = 0;
        
        // ONLY consider when this share is the BUYER (user_share_id = share->id)
        // This prevents inheritance from sold shares - bought shares should be independent
        $buyerSidePairings = UserSharePair::where('user_share_id', $share->id)->get();
        
        foreach ($buyerSidePairings as $pairing) {
            $sellerShare = UserShare::find($pairing->paired_user_share_id);
            
            // Skip pairings where seller share failed
            if (!$sellerShare || $sellerShare->status === 'failed') {
                continue;
            }
            
            if ($pairing->is_paid == 1) {
                $paidPairings++;
            } elseif ($pairing->is_paid == 2) {
                $failedPairings++;
            } else {
                // Check if payment is submitted but not confirmed
                $hasSubmittedPayment = $share->payments()
                    ->where('user_share_pair_id', $pairing->id)
                    ->where('status', 'paid')
                    ->exists();
                if ($hasSubmittedPayment) {
                    $awaitingConfirmation++;
                } else {
                    $genuinelyUnpaid++;
                }
            }
        }
        
        return [
            'paid' => $paidPairings,
            'unpaid' => $genuinelyUnpaid,
            'awaiting_confirmation' => $awaitingConfirmation,
            'failed' => $failedPairings,
            'total' => $paidPairings + $genuinelyUnpaid + $awaitingConfirmation + $failedPairings
        ];
    }
    
    /**
     * Get pairing statistics for a share (general purpose - LEGACY)
     * WARNING: This method includes BOTH buyer and seller perspectives
     * Use getBoughtSharePairingStats() or getSoldSharePairingStats() for context-specific logic
     */
    public function getPairingStats(UserShare $share): array
    {
        // Aggregate pairing stats from BOTH perspectives:
        // - As seller: user_share_id = $share->id (payments live on the buyer's share)
        // - As buyer: paired_user_share_id = $share->id (payments live on this share)
        
        $paidPairings = 0;
        $failedPairings = 0;
        $awaitingConfirmation = 0;
        $genuinelyUnpaid = 0;

        // Seller-side stats
        $sellerPaid = UserSharePair::where('user_share_id', $share->id)
            ->where('is_paid', 1)
            ->count();
        $paidPairings += $sellerPaid;

        $sellerUnpaidPairs = UserSharePair::where('user_share_id', $share->id)
            ->where('is_paid', 0)
            ->get();
        foreach ($sellerUnpaidPairs as $pair) {
            // For seller-side pairs, payments are stored on the SELLER's share (current share)
            // Check if payment exists for this specific pairing
            $hasSubmittedPayment = $share->payments()
                ->where('user_share_pair_id', $pair->id)
                ->where('status', 'paid')
                ->exists();
            if ($hasSubmittedPayment) {
                $awaitingConfirmation++;
            } else {
                $genuinelyUnpaid++;
            }
        }
        $sellerFailed = UserSharePair::where('user_share_id', $share->id)
            ->where('is_paid', 2)
            ->count();
        $failedPairings += $sellerFailed;

        // Buyer-side stats
        $buyerPaid = UserSharePair::where('paired_user_share_id', $share->id)
            ->where('is_paid', 1)
            ->count();
        $paidPairings += $buyerPaid;

        $buyerUnpaidPairs = UserSharePair::where('paired_user_share_id', $share->id)
            ->where('is_paid', 0)
            ->get();
        foreach ($buyerUnpaidPairs as $pair) {
            // Payments for buyer-side pairs are on the BUYER's share (current share)
            // When $share is the buyer (paired_user_share_id), payments are on $share itself
            $hasSubmittedPayment = $share->payments()->where('status', 'paid')->exists();
            if ($hasSubmittedPayment) {
                $awaitingConfirmation++;
            } else {
                $genuinelyUnpaid++;
            }
        }
        $buyerFailed = UserSharePair::where('paired_user_share_id', $share->id)
            ->where('is_paid', 2)
            ->count();
        $failedPairings += $buyerFailed;

        return [
            'paid' => $paidPairings,
            'unpaid' => $genuinelyUnpaid,
            'awaiting_confirmation' => $awaitingConfirmation,
            'failed' => $failedPairings,
            'total' => $paidPairings + $genuinelyUnpaid + $awaitingConfirmation + $failedPairings
        ];
    }

    /**
     * Get specialized pairing statistics for sold shares
     * FIXED: Only considers SELLER-side pairings to prevent inheritance from bought shares
     * When a share appears in sold context, only its selling activities matter, not its buying history
     */
    public function getSoldSharePairingStats(UserShare $share): array
    {
        $paidPairings = 0;
        $failedPairings = 0;
        $awaitingConfirmation = 0;
        $genuinelyUnpaid = 0;
        $totalAmountPaired = 0;
        
        // ONLY consider when this share is the SELLER (paired_user_share_id = share->id)
        // This prevents inheritance from bought shares - sold shares should be independent
        $sellerSidePairings = UserSharePair::where('paired_user_share_id', $share->id)->get();
        
        foreach ($sellerSidePairings as $pairing) {
            $buyerShare = UserShare::find($pairing->user_share_id);
            
            // Skip pairings where buyer share failed due to payment deadline expiry
            if (!$buyerShare || $buyerShare->status === 'failed') {
                continue;
            }
            
            // Count valid pairings only
            $totalAmountPaired += $pairing->share;
            
            if ($pairing->is_paid == 1) {
                $paidPairings++;
            } elseif ($pairing->is_paid == 2) {
                $failedPairings++;
            } else {
                // Check if payment is submitted but not confirmed
                $hasSubmittedPayment = $buyerShare->payments()
                    ->where('user_share_pair_id', $pairing->id)
                    ->where('status', 'paid')
                    ->exists();
                if ($hasSubmittedPayment) {
                    $awaitingConfirmation++;
                } else {
                    $genuinelyUnpaid++;
                }
            }
        }
        
        // DO NOT include buyer-side pairings (user_share_id = share->id)
        // This prevents cross-contamination from the bought shares context
        // When evaluating a share as "sold", we only care about who is buying FROM it, 
        // not who it bought from originally
        
        return [
            'paid' => $paidPairings,
            'unpaid' => $genuinelyUnpaid,
            'awaiting_confirmation' => $awaitingConfirmation,
            'failed' => $failedPairings,
            'total' => $paidPairings + $genuinelyUnpaid + $awaitingConfirmation + $failedPairings,
            'total_amount_paired' => $totalAmountPaired
        ];
    }

    /**
     * Update share status based on current state
     */
    public function updateShareStatusFromQuantities(UserShare $share): bool
    {
        try {
            $originalStatus = $share->status;

            // If share is completely sold (no shares left, some sold)
            if ($share->total_share_count == 0 && 
                $share->hold_quantity == 0 && 
                $share->sold_quantity > 0 && 
                $share->status !== 'sold') {
                
                $share->status = 'sold';
                $share->is_sold = 1;
                $share->save();

                Log::info("Updated share status from '{$originalStatus}' to 'sold'", [
                    'share_id' => $share->id,
                    'ticket_no' => $share->ticket_no,
                    'sold_quantity' => $share->sold_quantity
                ]);

                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Error updating share status from quantities', [
                'share_id' => $share->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get time remaining information for a share
     * UPDATED: Context-aware timer system - bought vs sold shares have different timers
     * 
     * @param UserShare $share The share to get timer for
     * @param string|null $context 'bought' for bought-shares page, 'sold' for sold-shares page, null for auto-detect
     */
    public function getTimeRemaining(UserShare $share, ?string $context = null): array
    {
        // Use provided context or auto-detect if not specified
        if ($context === 'bought') {
            return $this->getBoughtShareTimeRemaining($share);
        } elseif ($context === 'sold') {
            return $this->getSoldShareTimeRemaining($share);
        }
        
        // Auto-detect context based on share properties (backward compatibility)
        $context = $this->detectContext($share);
        
        if ($context === 'bought') {
            return $this->getBoughtShareTimeRemaining($share);
        } else {
            return $this->getSoldShareTimeRemaining($share);
        }
    }
    
    /**
     * Get time remaining for BOUGHT shares (buyer's perspective)
     */
    private function getBoughtShareTimeRemaining(UserShare $share): array
    {
        if (isset($share->status)) {
            switch ($share->status) {
                case 'pending':
                    // Payment deadline timer (bought shares only)
                    if ($share->get_from === 'purchase') {
                        return [
                            'text' => 'timer-active', // Payment deadline countdown
                            'class' => 'countdown-timer payment-deadline',
                            'color' => '#e74c3c'
                        ];
                    }
                    break;
                    
                case 'paired':
                    return [
                        'text' => 'Waiting for seller confirmation',
                        'class' => 'countdown-timer waiting',
                        'color' => '#f39c12'
                    ];
                    
                case 'completed':
                    // For bought shares, completed means the buying process is done
                    return [
                        'text' => 'Purchase Completed',
                        'class' => 'countdown-timer completed',
                        'color' => '#28a745'
                    ];
                    
                case 'failed':
                    return [
                        'text' => 'Purchase Failed',
                        'class' => 'countdown-timer failed',
                        'color' => '#e74c3c'
                    ];
            }
        }

        return [
            'text' => 'Processing',
            'class' => 'countdown-timer',
            'color' => '#95a5a6'
        ];
    }
    
    /**
     * Get time remaining for SOLD shares (seller's perspective)
     */
    private function getSoldShareTimeRemaining(UserShare $share): array
    {
        // For shares that have matured, check if they are paired and awaiting confirmation
        if (isset($share->is_ready_to_sell) && $share->is_ready_to_sell == 1) {
            $pairingStats = $this->getSoldSharePairingStats($share);
            
            // If there are pairings awaiting confirmation, show that instead of "Share Matured"
            if ($pairingStats['awaiting_confirmation'] > 0) {
                return [
                    'text' => 'Awaiting Confirmation',
                    'class' => 'countdown-timer awaiting-confirmation',
                    'color' => '#f39c12'
                ];
            }
            
            // If no pairings or only confirmed pairings, show matured status
            return [
                'text' => 'Share Matured',
                'class' => 'countdown-timer matured',
                'color' => '#27ae60'
            ];
        }

        // Handle different statuses for sold shares
        if (isset($share->status)) {
            switch ($share->status) {
                case 'paired':
                    // Check for payment submission
                    $pairingStats = $this->getPairingStats($share);
                    if ($pairingStats['awaiting_confirmation'] > 0) {
                        return [
                            'text' => 'Payment received - confirm to complete',
                            'class' => 'countdown-timer payment-received',
                            'color' => '#17a2b8'
                        ];
                    }
                    return [
                        'text' => 'Waiting for payments',
                        'class' => 'countdown-timer waiting',
                        'color' => '#f39c12'
                    ];
                    
                case 'completed':
                    // SELL MATURITY TIMER (only for sold shares context)
                    if (isset($share->start_date) && isset($share->period) && $share->is_ready_to_sell == 0) {
                        return [
                            'text' => 'timer-active', // Sell maturity countdown
                            'class' => 'countdown-timer sell-maturity',
                            'color' => '#3498db'
                        ];
                    }
                    break;
                    
                case 'failed':
                    return [
                        'text' => 'Transaction failed',
                        'class' => 'countdown-timer failed',
                        'color' => '#e74c3c'
                    ];
            }
        }

        return [
            'text' => 'Status unknown',
            'class' => 'countdown-timer',
            'color' => '#95a5a6'
        ];
    }
    
    /**
     * Determine if the Details button should be unlocked for a share
     * This uses the same logic as status checking - real-time maturity + database flag
     */
    public function shouldUnlockDetailsButton(UserShare $share): bool
    {
        // If already flagged as ready to sell, unlock
        if (isset($share->is_ready_to_sell) && $share->is_ready_to_sell == 1) {
            return true;
        }
        
        // If share has matured in real-time, also unlock
        return $this->hasShareMatured($share);
    }

    /**
     * Validate and fix share quantity inconsistencies
     */
    public function validateAndFixQuantities(UserShare $share): array
    {
        $issues = [];
        $fixes = [];

        // Check for negative quantities
        if ($share->total_share_count < 0) {
            $issues[] = "Negative total_share_count: {$share->total_share_count}";
            $share->total_share_count = 0;
            $fixes[] = "Reset total_share_count to 0";
        }

        if ($share->hold_quantity < 0) {
            $issues[] = "Negative hold_quantity: {$share->hold_quantity}";
            $share->hold_quantity = 0;
            $fixes[] = "Reset hold_quantity to 0";
        }

        if ($share->sold_quantity < 0) {
            $issues[] = "Negative sold_quantity: {$share->sold_quantity}";
            $share->sold_quantity = 0;
            $fixes[] = "Reset sold_quantity to 0";
        }

        // Check quantity conservation (allow 10% tolerance for profit additions)
        $total = $share->total_share_count + $share->hold_quantity + $share->sold_quantity;
        $expected = $share->share_will_get + ($share->profit_share ?? 0);
        $tolerance = max(1, $expected * 0.1); // 10% tolerance or minimum 1 share

        if (abs($total - $expected) > $tolerance) {
            $issues[] = "Quantity mismatch - Expected: {$expected}, Actual: {$total}";
        }

        if (!empty($fixes)) {
            $share->save();
            Log::info('Fixed share quantity issues', [
                'share_id' => $share->id,
                'ticket_no' => $share->ticket_no,
                'issues' => $issues,
                'fixes' => $fixes
            ]);
        }

        return [
            'issues' => $issues,
            'fixes' => $fixes,
            'has_issues' => !empty($issues)
        ];
    }
    
    /**
     * Check if a share has actually matured based on start date and period
     * This provides real-time maturity checking regardless of is_ready_to_sell flag
     */
    private function hasShareMatured(UserShare $share): bool
    {
        // Only check maturity for shares with proper start date and period
        if (!$share->start_date || !$share->period || $share->status !== 'completed') {
            return false;
        }
        
        try {
            $maturityDate = \Carbon\Carbon::parse($share->start_date)->addDays($share->period);
            $now = \Carbon\Carbon::now();
            
            return $maturityDate <= $now;
        } catch (\Exception $e) {
            \Log::warning('Error checking share maturity', [
                'share_id' => $share->id,
                'start_date' => $share->start_date,
                'period' => $share->period,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
