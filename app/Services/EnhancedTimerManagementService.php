<?php

namespace App\Services;

use App\Models\UserShare;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EnhancedTimerManagementService
{
    /**
     * ========================================
     * PAYMENT TIMER METHODS (Bought Shares)
     * ========================================
     * These handle payment deadline timers for bought shares
     */
    
    /**
     * Pause payment timer when payment is submitted
     */
    public function pausePaymentTimer(UserShare $share, string $reason = 'Payment submitted'): bool
    {
        try {
            // Only pause if payment timer is not already paused
            if ($share->payment_timer_paused) {
                Log::warning("Payment timer already paused for share: {$share->ticket_no}");
                return false;
            }

            $share->update([
                'payment_timer_paused' => 1,
                'payment_timer_paused_at' => Carbon::now(),
                // Also update legacy fields for backward compatibility
                'timer_paused' => 1,
                'timer_paused_at' => Carbon::now()
            ]);

            Log::info("Payment timer paused for share: {$share->ticket_no}", [
                'share_id' => $share->id,
                'reason' => $reason,
                'timer_type' => 'payment_deadline',
                'paused_at' => Carbon::now()->toDateTimeString()
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to pause payment timer for share {$share->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Resume payment timer when payment is declined or expires
     */
    public function resumePaymentTimer(UserShare $share, string $reason = 'Payment declined/expired'): bool
    {
        try {
            // Only resume if payment timer is actually paused
            if (!$share->payment_timer_paused) {
                Log::info("Payment timer not paused for share: {$share->ticket_no} - no action needed");
                return true;
            }

            // Calculate paused duration
            $pausedAt = Carbon::parse($share->payment_timer_paused_at);
            $pausedDurationSeconds = $pausedAt->diffInSeconds(Carbon::now());

            // Update share to resume payment timer
            $share->update([
                'payment_timer_paused' => 0,
                'payment_timer_paused_at' => null,
                'payment_paused_duration_seconds' => $share->payment_paused_duration_seconds + $pausedDurationSeconds,
                // Also update legacy fields for backward compatibility
                'timer_paused' => 0,
                'timer_paused_at' => null,
                'paused_duration_seconds' => $share->paused_duration_seconds + $pausedDurationSeconds
            ]);

            Log::info("Payment timer resumed for share: {$share->ticket_no}", [
                'share_id' => $share->id,
                'reason' => $reason,
                'timer_type' => 'payment_deadline',
                'paused_duration_seconds' => $pausedDurationSeconds
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to resume payment timer for share {$share->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ==========================================
     * SELLING TIMER METHODS (Investment Maturity)
     * ==========================================
     * These handle investment maturity timers for selling shares
     */
    
    /**
     * Start fresh selling timer when share transitions to selling phase
     */
    public function startSellingTimer(UserShare $share, string $reason = 'Share transitioned to selling phase'): bool
    {
        try {
            // Start fresh selling timer - completely independent from buying phase
            $share->update([
                'selling_started_at' => Carbon::now(),
                'selling_timer_paused' => 0,
                'selling_timer_paused_at' => null,
                'selling_paused_duration_seconds' => 0
            ]);

            Log::info("Selling timer started for share: {$share->ticket_no}", [
                'share_id' => $share->id,
                'reason' => $reason,
                'timer_type' => 'investment_maturity',
                'selling_started_at' => Carbon::now()->toDateTimeString(),
                'investment_period_days' => $share->period
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to start selling timer for share {$share->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Pause selling timer (investment maturity) - rarely used but available
     */
    public function pauseSellingTimer(UserShare $share, string $reason = 'Administrative pause'): bool
    {
        try {
            // Only pause if selling timer is not already paused
            if ($share->selling_timer_paused) {
                Log::warning("Selling timer already paused for share: {$share->ticket_no}");
                return false;
            }

            $share->update([
                'selling_timer_paused' => 1,
                'selling_timer_paused_at' => Carbon::now()
            ]);

            Log::info("Selling timer paused for share: {$share->ticket_no}", [
                'share_id' => $share->id,
                'reason' => $reason,
                'timer_type' => 'investment_maturity',
                'paused_at' => Carbon::now()->toDateTimeString()
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to pause selling timer for share {$share->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Resume selling timer (investment maturity)
     */
    public function resumeSellingTimer(UserShare $share, string $reason = 'Administrative resume'): bool
    {
        try {
            // Only resume if selling timer is actually paused
            if (!$share->selling_timer_paused) {
                Log::info("Selling timer not paused for share: {$share->ticket_no} - no action needed");
                return true;
            }

            // Calculate paused duration
            $pausedAt = Carbon::parse($share->selling_timer_paused_at);
            $pausedDurationSeconds = $pausedAt->diffInSeconds(Carbon::now());

            // Update share to resume selling timer
            $share->update([
                'selling_timer_paused' => 0,
                'selling_timer_paused_at' => null,
                'selling_paused_duration_seconds' => $share->selling_paused_duration_seconds + $pausedDurationSeconds
            ]);

            Log::info("Selling timer resumed for share: {$share->ticket_no}", [
                'share_id' => $share->id,
                'reason' => $reason,
                'timer_type' => 'investment_maturity',
                'paused_duration_seconds' => $pausedDurationSeconds
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to resume selling timer for share {$share->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ========================================
     * TIMER ANALYSIS METHODS
     * ========================================
     */

    /**
     * Get selling timer information (investment maturity)
     */
    public function getSellingTimerInfo(UserShare $share): array
    {
        if (!$share->selling_started_at || !$share->period) {
            return [
                'original_end_time' => null,
                'adjusted_end_time' => null,
                'total_paused_seconds' => 0,
                'currently_paused' => false,
                'effective_runtime' => 0,
                'is_mature' => false
            ];
        }

        $startTime = Carbon::parse($share->selling_started_at);
        $periodDays = $share->period;
        $originalEndTime = $startTime->copy()->addDays($periodDays);

        // Calculate total paused duration for selling timer
        $totalPausedSeconds = $share->selling_paused_duration_seconds ?? 0;

        // If currently paused, add current pause duration
        if ($share->selling_timer_paused && $share->selling_timer_paused_at) {
            $currentPauseDuration = Carbon::parse($share->selling_timer_paused_at)->diffInSeconds(Carbon::now());
            $totalPausedSeconds += $currentPauseDuration;
        }

        // Calculate adjusted end time
        $adjustedEndTime = $originalEndTime->copy()->addSeconds($totalPausedSeconds);

        // Calculate effective runtime
        $now = Carbon::now();
        $effectiveRuntime = 0;
        
        if ($share->selling_timer_paused && $share->selling_timer_paused_at) {
            // Timer is currently paused - calculate up to pause time
            $effectiveRuntime = $startTime->diffInSeconds(Carbon::parse($share->selling_timer_paused_at)) - ($share->selling_paused_duration_seconds ?? 0);
        } else {
            // Timer is running - calculate up to now
            $effectiveRuntime = max(0, $startTime->diffInSeconds($now) - $totalPausedSeconds);
        }

        return [
            'original_end_time' => $originalEndTime,
            'adjusted_end_time' => $adjustedEndTime,
            'total_paused_seconds' => $totalPausedSeconds,
            'currently_paused' => (bool) $share->selling_timer_paused,
            'effective_runtime' => max(0, $effectiveRuntime),
            'period_seconds' => $periodDays * 24 * 60 * 60,
            'completion_percentage' => min(100, ($effectiveRuntime / ($periodDays * 24 * 60 * 60)) * 100),
            'is_mature' => $adjustedEndTime->isPast() && !$share->selling_timer_paused
        ];
    }

    /**
     * Get payment timer information (bought shares)
     */
    public function getPaymentTimerInfo(UserShare $share): array
    {
        if (!$share->created_at || !$share->payment_deadline_minutes) {
            return [
                'deadline_time' => null,
                'adjusted_deadline' => null,
                'total_paused_seconds' => 0,
                'currently_paused' => false,
                'time_remaining' => 0,
                'is_expired' => true
            ];
        }

        $startTime = Carbon::parse($share->created_at);
        $deadlineMinutes = $share->payment_deadline_minutes;
        $originalDeadline = $startTime->copy()->addMinutes($deadlineMinutes);

        // Calculate total paused duration for payment timer
        $totalPausedSeconds = $share->payment_paused_duration_seconds ?? 0;

        // If currently paused, add current pause duration
        if ($share->payment_timer_paused && $share->payment_timer_paused_at) {
            $currentPauseDuration = Carbon::parse($share->payment_timer_paused_at)->diffInSeconds(Carbon::now());
            $totalPausedSeconds += $currentPauseDuration;
        }

        // Calculate adjusted deadline
        $adjustedDeadline = $originalDeadline->copy()->addSeconds($totalPausedSeconds);

        // Calculate time remaining
        $now = Carbon::now();
        $timeRemaining = max(0, $adjustedDeadline->diffInSeconds($now, false));

        return [
            'deadline_time' => $originalDeadline,
            'adjusted_deadline' => $adjustedDeadline,
            'total_paused_seconds' => $totalPausedSeconds,
            'currently_paused' => (bool) $share->payment_timer_paused,
            'time_remaining' => $timeRemaining,
            'is_expired' => $adjustedDeadline->isPast() && !$share->payment_timer_paused
        ];
    }

    /**
     * Check if a share should mature (selling phase timer complete)
     */
    public function shouldShareMature(UserShare $share): bool
    {
        // Skip if already matured
        if ($share->is_ready_to_sell == 1) {
            return false;
        }

        // Must have selling timer started
        if (!$share->selling_started_at) {
            return false;
        }

        // Skip if selling timer is paused
        if ($share->selling_timer_paused) {
            return false;
        }

        // Check if adjusted end time has passed
        $timerInfo = $this->getSellingTimerInfo($share);
        
        return $timerInfo['is_mature'];
    }
}