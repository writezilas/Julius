<?php

namespace App\Services;

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use App\Notifications\PaymentDeclined;
use App\Services\SharePairingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class PaymentDeclineService
{
    const MAX_DECLINE_ATTEMPTS = 2;

    /**
     * Handle payment decline with second chance logic
     *
     * @param UserSharePayment $payment
     * @param string|null $declineReason
     * @param bool $byAdmin
     * @return array ['success' => bool, 'message' => string, 'is_final_decline' => bool]
     */
    public function handlePaymentDecline(UserSharePayment $payment, ?string $declineReason = null, bool $byAdmin = false): array
    {
        return DB::transaction(function () use ($payment, $declineReason, $byAdmin) {
            try {
                // Get the share pair
                $sharePair = UserSharePair::findOrFail($payment->user_share_pair_id);
                
                // Increment decline attempts
                $sharePair->increment('decline_attempts');
                $currentAttempts = $sharePair->decline_attempts;
                
                Log::info("Payment decline attempt {$currentAttempts} for payment ID {$payment->id}, share pair ID {$sharePair->id}");
                
                // Update payment status to failed
                $payment->status = 'failed';
                $payment->note_by_receiver = $declineReason;
                if ($byAdmin) {
                    $payment->by_admin = 1;
                }
                $payment->save();

                // Reset payment status on share pair
                $sharePair->is_paid = 0;
                $sharePair->save();

                // Get related shares and users
                $buyerShare = UserShare::findOrFail($sharePair->user_share_id);
                $sellerShare = UserShare::findOrFail($sharePair->paired_user_share_id);
                $buyer = $buyerShare->user;

                // Resume timer for buyer share when payment is declined
                if ($buyerShare->timer_paused) {
                    $this->resumeTimerForBuyerShare($buyerShare);
                }

                // Return shares back to seller's available quantity temporarily
                $sellerShare->increment('hold_quantity', $sharePair->share);
                $sellerShare->decrement('sold_quantity', $sharePair->share);

                $isFinalDecline = $currentAttempts >= self::MAX_DECLINE_ATTEMPTS;
                
                if ($isFinalDecline) {
                    // Final decline - break the pairing and re-match both parties
                    $this->handleFinalDecline($sharePair, $buyerShare, $sellerShare, $payment);
                    
                    // Notify buyer of final decline
                    Notification::send($buyer, new PaymentDeclined($payment, false, $declineReason));
                    
                    return [
                        'success' => true,
                        'message' => 'Payment permanently declined. Both buyer and seller will be re-matched with new partners.',
                        'is_final_decline' => true
                    ];
                } else {
                    // First decline - give second chance
                    $this->handleFirstDecline($sharePair, $sellerShare, $payment);
                    
                    // Notify buyer of first decline (second chance)
                    Notification::send($buyer, new PaymentDeclined($payment, true, $declineReason));
                    
                    return [
                        'success' => true,
                        'message' => 'Payment declined. Buyer has been notified and given a second chance to confirm payment.',
                        'is_final_decline' => false
                    ];
                }
                
            } catch (\Exception $e) {
                Log::error('Error in payment decline handling: ' . $e->getMessage(), [
                    'payment_id' => $payment->id,
                    'trace' => $e->getTraceAsString()
                ]);
                
                throw $e;
            }
        });
    }

    /**
     * Handle first decline (second chance scenario)
     */
    private function handleFirstDecline(UserSharePair $sharePair, UserShare $sellerShare, UserSharePayment $payment): void
    {
        // Keep the pairing intact but reset seller's share allocation
        // Shares are returned to seller's hold_quantity (done in main method)
        
        Log::info("First payment decline for share pair {$sharePair->id}. Giving buyer second chance.");
    }

    /**
     * Handle final decline (break pairing and re-match)
     */
    private function handleFinalDecline(UserSharePair $sharePair, UserShare $buyerShare, UserShare $sellerShare, UserSharePayment $payment): void
    {
        Log::info("Final payment decline for share pair {$sharePair->id}. Breaking pairing and re-matching.");
        
        // Return all shares from hold_quantity back to available total_share_count for seller
        $sellerShare->increment('total_share_count', $sharePair->share);
        $sellerShare->decrement('hold_quantity', $sharePair->share);
        
        // If seller has no more hold_quantity, mark as ready to sell again
        if ($sellerShare->hold_quantity <= 0) {
            $sellerShare->status = 'completed';
            $sellerShare->is_ready_to_sell = 1;
        }
        $sellerShare->save();

        // Mark buyer share as failed - it will be re-matched by the system
        $buyerShare->status = 'failed';
        $buyerShare->save();

        // Delete the failed share pair
        $sharePair->delete();

        // Trigger re-matching for buyer (this will be done by cron jobs or manually)
        $this->triggerBuyerRematching($buyerShare);
        
        Log::info("Share pair {$sharePair->id} dissolved. Buyer share {$buyerShare->id} marked as failed for re-matching.");
    }

    /**
     * Trigger re-matching process for buyer
     */
    private function triggerBuyerRematching(UserShare $buyerShare): void
    {
        // This could trigger a job or call the share pairing service directly
        // For now, we'll just log it and let the system's existing re-matching logic handle it
        
        Log::info("Buyer share {$buyerShare->id} queued for re-matching", [
            'user_id' => $buyerShare->user_id,
            'trade_id' => $buyerShare->trade_id,
            'shares_needed' => $buyerShare->share_will_get,
            'ticket_no' => $buyerShare->ticket_no
        ]);

        // You could dispatch a job here for immediate re-matching:
        // dispatch(new RematchFailedShareJob($buyerShare));
        
        // Or call the pairing service directly if you want immediate re-matching:
        try {
            $pairingService = new SharePairingService();
            // Reset buyer share to pending status for re-pairing
            $buyerShare->status = 'pending';
            $buyerShare->save();
            
            // Attempt to re-pair
            $pairingService->pairWithSellers($buyerShare, $buyerShare->trade, $buyerShare->share_will_get);
            
            // If successful, activate the buyer share
            $buyerShare->status = 'paired';
            $buyerShare->start_date = now();
            $buyerShare->save();
            
            Log::info("Successfully re-matched buyer share {$buyerShare->id}");
            
        } catch (\Exception $e) {
            Log::warning("Failed to immediately re-match buyer share {$buyerShare->id}: " . $e->getMessage());
            // Leave as failed status - will be picked up by scheduled jobs
            $buyerShare->status = 'failed';
            $buyerShare->save();
        }
    }

    /**
     * Get decline attempts for a share pair
     */
    public function getDeclineAttempts(int $sharePairId): int
    {
        $sharePair = UserSharePair::find($sharePairId);
        return $sharePair ? $sharePair->decline_attempts : 0;
    }

    /**
     * Check if a share pair has reached maximum decline attempts
     */
    public function hasReachedMaxDeclineAttempts(int $sharePairId): bool
    {
        return $this->getDeclineAttempts($sharePairId) >= self::MAX_DECLINE_ATTEMPTS;
    }

    /**
     * Reset decline attempts for a share pair (used when payment is approved)
     */
    public function resetDeclineAttempts(int $sharePairId): void
    {
        UserSharePair::where('id', $sharePairId)->update(['decline_attempts' => 0]);
    }
    
    /**
     * Resume timer for buyer share when payment is declined
     */
    private function resumeTimerForBuyerShare(UserShare $buyerShare): void
    {
        try {
            // Calculate how long the timer was paused
            $pausedDuration = 0;
            if ($buyerShare->timer_paused_at) {
                $pausedDuration = now()->diffInSeconds($buyerShare->timer_paused_at);
            }
            
            // Add the paused duration to the existing paused duration
            $totalPausedDuration = ($buyerShare->paused_duration_seconds ?? 0) + $pausedDuration;
            
            // Resume the timer
            $buyerShare->timer_paused = false;
            $buyerShare->timer_paused_at = null;
            $buyerShare->paused_duration_seconds = $totalPausedDuration;
            $buyerShare->save();
            
            Log::info("Timer resumed for buyer share {$buyerShare->ticket_no} after payment decline. Total paused duration: {$totalPausedDuration} seconds");
            
        } catch (\Exception $e) {
            Log::error("Error resuming timer for buyer share {$buyerShare->id}: " . $e->getMessage());
        }
    }
}
