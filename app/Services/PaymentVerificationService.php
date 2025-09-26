<?php

namespace App\Services;

use App\Models\UserShare;
use App\Models\UserSharePair;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * PaymentVerificationService
 * 
 * Centralizes all payment verification logic to prevent trades from being 
 * incorrectly marked as failed when payments have been submitted.
 * 
 * This service addresses the critical issue where UpdateSharesCommand was
 * marking trades as failed without checking payment submission status.
 */
class PaymentVerificationService
{
    /**
     * Comprehensive check if a trade should be marked as failed
     * 
     * PRIORITY ORDER: Payment confirmation checks ALWAYS come first
     * 1. Check for any form of payment confirmation (highest priority)
     * 2. Check for payment submission signals (timer paused)
     * 3. Only then check timeout conditions
     * 
     * This ensures payment confirmation takes absolute priority over timing.
     */
    public function shouldMarkAsFailed(UserShare $share): bool
    {
        Log::info('Payment verification started', [
            'ticket_no' => $share->ticket_no,
            'current_status' => $share->status
        ]);
        
        // PRIORITY 1: Check for confirmed payments in pairings (HIGHEST PRIORITY)
        if ($this->hasConfirmedPairings($share)) {
            Log::info('Payment verification: CONFIRMED PAYMENTS FOUND - Trade protected', [
                'ticket_no' => $share->ticket_no,
                'confirmed_pairings' => $share->pairedShares()->where('is_paid', 1)->count(),
                'priority_level' => 'HIGHEST'
            ]);
            return false;
        }
        
        // PRIORITY 2: Check for direct payment records (HIGH PRIORITY)
        if ($this->hasPaymentRecords($share)) {
            $paymentDetails = $this->getPaymentRecordsDetails($share);
            Log::info('Payment verification: DIRECT PAYMENT RECORDS FOUND - Trade protected', [
                'ticket_no' => $share->ticket_no,
                'payment_count' => count($paymentDetails),
                'payment_statuses' => array_column($paymentDetails, 'status'),
                'priority_level' => 'HIGH'
            ]);
            return false;
        }
        
        // PRIORITY 3: Check for payment submission signals (MEDIUM PRIORITY)
        if ($this->isPaymentSubmitted($share)) {
            Log::info('Payment verification: PAYMENT SUBMITTED (Timer paused) - Trade protected', [
                'ticket_no' => $share->ticket_no,
                'timer_paused' => $share->timer_paused,
                'payment_timer_paused' => $share->payment_timer_paused,
                'priority_level' => 'MEDIUM'
            ]);
            return false;
        }
        
        // PRIORITY 4: Only check timeout if NO payment evidence found (LOWEST PRIORITY)
        if (!$this->isTimeoutReached($share)) {
            Log::debug('Payment verification: Timeout not reached - Trade protected', [
                'ticket_no' => $share->ticket_no,
                'deadline_minutes' => $share->payment_deadline_minutes ?? 60,
                'created_at' => $share->created_at->toDateTimeString(),
                'timeout_at' => $this->getTimeoutTime($share)->toDateTimeString(),
                'priority_level' => 'LOWEST'
            ]);
            return false;
        }
        
        // All checks passed - NO payment evidence found and timeout reached
        Log::warning('Payment verification: NO PAYMENT EVIDENCE - Trade will be marked as failed', [
            'ticket_no' => $share->ticket_no,
            'reason' => 'No payment submitted, no payment records, no confirmed pairings, and timeout reached',
            'timeout_at' => $this->getTimeoutTime($share)->toDateTimeString()
        ]);
        
        return true;
    }
    
    /**
     * Check if payment has been submitted (timer paused)
     */
    private function isPaymentSubmitted(UserShare $share): bool
    {
        return $share->timer_paused || $share->payment_timer_paused;
    }
    
    /**
     * Check if direct payment records exist for this share
     */
    private function hasPaymentRecords(UserShare $share): bool
    {
        return $share->payments()->exists();
    }
    
    /**
     * Get detailed payment records information
     */
    private function getPaymentRecordsDetails(UserShare $share): array
    {
        return $share->payments()->get()->map(function($payment) {
            return [
                'id' => $payment->id,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'created_at' => $payment->created_at->toDateTimeString()
            ];
        })->toArray();
    }
    
    /**
     * Check if any pairings have confirmed payments
     */
    private function hasConfirmedPairings(UserShare $share): bool
    {
        return $share->pairedShares()->where('is_paid', 1)->exists();
    }
    
    /**
     * Check if payment timeout has been reached
     */
    private function isTimeoutReached(UserShare $share): bool
    {
        return $this->getTimeoutTime($share)->isPast();
    }
    
    /**
     * Get the timeout time for this share
     */
    private function getTimeoutTime(UserShare $share): Carbon
    {
        $deadlineMinutes = $share->payment_deadline_minutes ?? 60;
        return $share->created_at->addMinutes($deadlineMinutes);
    }
    
    /**
     * Get detailed payment status for logging/debugging
     * 
     * This provides comprehensive information about why a trade was or wasn't
     * marked as failed, essential for debugging and audit trails.
     */
    public function getPaymentStatusDetails(UserShare $share): array
    {
        $timeoutTime = $this->getTimeoutTime($share);
        
        return [
            'ticket_no' => $share->ticket_no,
            'user_id' => $share->user_id,
            'status' => $share->status,
            'created_at' => $share->created_at->toDateTimeString(),
            'timeout_at' => $timeoutTime->toDateTimeString(),
            'deadline_minutes' => $share->payment_deadline_minutes ?? 60,
            'timer_paused' => $share->timer_paused ? 'YES' : 'NO',
            'payment_timer_paused' => $share->payment_timer_paused ? 'YES' : 'NO',
            'has_payments' => $this->hasPaymentRecords($share) ? 'YES' : 'NO',
            'payment_count' => $share->payments()->count(),
            'has_confirmed_pairings' => $this->hasConfirmedPairings($share) ? 'YES' : 'NO',
            'confirmed_pairing_count' => $share->pairedShares()->where('is_paid', 1)->count(),
            'total_pairing_count' => $share->pairedShares()->count(),
            'timeout_reached' => $this->isTimeoutReached($share) ? 'YES' : 'NO',
            'should_fail' => $this->shouldMarkAsFailed($share) ? 'YES' : 'NO'
        ];
    }
    
    /**
     * Log payment verification decision for audit trail
     */
    public function logVerificationDecision(UserShare $share, string $context = ''): void
    {
        $details = $this->getPaymentStatusDetails($share);
        $shouldFail = $this->shouldMarkAsFailed($share);
        
        $logLevel = $shouldFail ? 'warning' : 'info';
        $action = $shouldFail ? 'WILL BE MARKED AS FAILED' : 'WILL BE SKIPPED (Protected)';
        
        Log::log($logLevel, "Payment Verification Decision - {$action}", [
            'context' => $context,
            'decision' => $action,
            'details' => $details
        ]);
    }
    
    /**
     * Validate share has all required relationships loaded for verification
     */
    public function validateShareForVerification(UserShare $share): bool
    {
        // Check if required relationships are loaded
        $hasPayments = $share->relationLoaded('payments');
        $hasPairedShares = $share->relationLoaded('pairedShares');
        
        if (!$hasPayments || !$hasPairedShares) {
            Log::warning('Share verification attempted without proper relationship loading', [
                'ticket_no' => $share->ticket_no,
                'has_payments_loaded' => $hasPayments,
                'has_paired_shares_loaded' => $hasPairedShares
            ]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Get summary statistics for verification decisions
     */
    public function getVerificationStats(array $shares): array
    {
        $stats = [
            'total_shares' => count($shares),
            'should_fail' => 0,
            'protected_by_timer' => 0,
            'protected_by_payments' => 0,
            'protected_by_pairings' => 0,
            'timeout_not_reached' => 0
        ];
        
        foreach ($shares as $share) {
            if ($this->shouldMarkAsFailed($share)) {
                $stats['should_fail']++;
            } else {
                // Determine protection reason
                if ($this->isPaymentSubmitted($share)) {
                    $stats['protected_by_timer']++;
                } elseif ($this->hasPaymentRecords($share)) {
                    $stats['protected_by_payments']++;
                } elseif ($this->hasConfirmedPairings($share)) {
                    $stats['protected_by_pairings']++;
                } else {
                    $stats['timeout_not_reached']++;
                }
            }
        }
        
        return $stats;
    }
    
    /**
     * Recover trades that were incorrectly marked as failed when they had payments
     * 
     * This method identifies and fixes trades that:
     * 1. Are currently marked as 'failed'
     * 2. Have payment evidence (payments, confirmed pairings, timer paused)
     * 3. Should be restored to their correct status
     */
    public function recoverIncorrectlyFailedTrades(array $ticketNumbers = null): array
    {
        Log::info('Starting recovery of incorrectly failed trades', [
            'specific_tickets' => $ticketNumbers ?? 'ALL'
        ]);
        
        // Find failed trades that might have been incorrectly failed
        $query = UserShare::with(['pairedShares.payment', 'payments'])
            ->where('status', 'failed');
            
        if ($ticketNumbers) {
            $query->whereIn('ticket_no', $ticketNumbers);
        }
        
        $failedTrades = $query->get();
        
        $recovery_stats = [
            'total_examined' => $failedTrades->count(),
            'incorrectly_failed' => 0,
            'recovered_trades' => [],
            'recovery_reasons' => []
        ];
        
        foreach ($failedTrades as $trade) {
            $hasPaymentEvidence = false;
            $recoveryReason = [];
            
            // Check for payment evidence
            if ($this->hasConfirmedPairings($trade)) {
                $hasPaymentEvidence = true;
                $recoveryReason[] = 'Confirmed pairings exist';
            }
            
            if ($this->hasPaymentRecords($trade)) {
                $hasPaymentEvidence = true;
                $paymentDetails = $this->getPaymentRecordsDetails($trade);
                $recoveryReason[] = 'Direct payment records exist (' . count($paymentDetails) . ' payments)';
            }
            
            if ($this->isPaymentSubmitted($trade)) {
                $hasPaymentEvidence = true;
                $recoveryReason[] = 'Timer paused (payment submitted)';
            }
            
            // If payment evidence found, this trade was incorrectly failed
            if ($hasPaymentEvidence) {
                $recovery_stats['incorrectly_failed']++;
                $recovery_stats['recovered_trades'][] = [
                    'ticket_no' => $trade->ticket_no,
                    'user_id' => $trade->user_id,
                    'amount' => $trade->amount,
                    'recovery_reasons' => $recoveryReason
                ];
                
                $recovery_stats['recovery_reasons'][] = [
                    'ticket_no' => $trade->ticket_no,
                    'reasons' => $recoveryReason
                ];
                
                // Log the incorrectly failed trade
                Log::error('INCORRECTLY FAILED TRADE DETECTED', [
                    'ticket_no' => $trade->ticket_no,
                    'user_id' => $trade->user_id,
                    'recovery_reasons' => $recoveryReason,
                    'timer_paused' => $trade->timer_paused,
                    'payment_timer_paused' => $trade->payment_timer_paused,
                    'payment_count' => $this->hasPaymentRecords($trade) ? $trade->payments()->count() : 0,
                    'confirmed_pairings' => $this->hasConfirmedPairings($trade) ? $trade->pairedShares()->where('is_paid', 1)->count() : 0
                ]);
            }
        }
        
        Log::info('Recovery analysis completed', $recovery_stats);
        
        return $recovery_stats;
    }
    
    /**
     * Actually restore incorrectly failed trades to 'paired' status
     * 
     * WARNING: This method modifies the database. Use with caution.
     */
    public function restoreIncorrectlyFailedTrades(array $ticketNumbers = null, bool $dryRun = true): array
    {
        $recoveryStats = $this->recoverIncorrectlyFailedTrades($ticketNumbers);
        
        if ($recoveryStats['incorrectly_failed'] === 0) {
            Log::info('No incorrectly failed trades found to restore');
            return $recoveryStats;
        }
        
        if ($dryRun) {
            Log::info('DRY RUN: Would restore ' . $recoveryStats['incorrectly_failed'] . ' trades');
            $recoveryStats['dry_run'] = true;
            return $recoveryStats;
        }
        
        Log::warning('RESTORING INCORRECTLY FAILED TRADES', [
            'count' => $recoveryStats['incorrectly_failed']
        ]);
        
        $restored = 0;
        
        // Find and restore the trades
        $query = UserShare::where('status', 'failed');
        if ($ticketNumbers) {
            $query->whereIn('ticket_no', $ticketNumbers);
        }
        
        foreach ($query->get() as $trade) {
            // Re-verify this trade should be restored
            if (!$this->shouldMarkAsFailed($trade)) {
                // Restore to paired status
                $trade->status = 'paired';
                $trade->save();
                $restored++;
                
                Log::info('TRADE RESTORED', [
                    'ticket_no' => $trade->ticket_no,
                    'from_status' => 'failed',
                    'to_status' => 'paired'
                ]);
            }
        }
        
        $recoveryStats['actually_restored'] = $restored;
        $recoveryStats['dry_run'] = false;
        
        return $recoveryStats;
    }
}
