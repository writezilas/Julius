<?php

namespace App\Services;

use App\Models\UserShare;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TimerManagementService
{
    /**
     * Pause timer for a share when payment is submitted
     *
     * @param UserShare $share
     * @param string $reason
     * @return bool
     */
    public function pauseTimer(UserShare $share, string $reason = 'Payment submitted'): bool
    {
        try {
            // Only pause if timer is not already paused
            if ($share->timer_paused) {
                Log::warning("Timer already paused for share: {$share->ticket_no}");
                return false;
            }

            $share->update([
                'timer_paused' => 1,
                'timer_paused_at' => Carbon::now()
            ]);

            Log::info("Timer paused for share: {$share->ticket_no}", [
                'share_id' => $share->id,
                'reason' => $reason,
                'paused_at' => Carbon::now()->toDateTimeString()
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to pause timer for share {$share->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Resume timer for a share when payment is confirmed
     *
     * @param UserShare $share
     * @param string $reason
     * @return bool
     */
    public function resumeTimer(UserShare $share, string $reason = 'Payment confirmed'): bool
    {
        try {
            // Only resume if timer is actually paused
            if (!$share->timer_paused) {
                Log::info("Timer not paused for share: {$share->ticket_no} - no action needed");
                return true;
            }

            // Calculate paused duration
            $pausedAt = Carbon::parse($share->timer_paused_at);
            $pausedDurationSeconds = $pausedAt->diffInSeconds(Carbon::now());

            // Update share to resume timer
            $share->update([
                'timer_paused' => 0,
                'timer_paused_at' => null,
                'paused_duration_seconds' => $share->paused_duration_seconds + $pausedDurationSeconds
            ]);

            Log::info("Timer resumed for share: {$share->ticket_no}", [
                'share_id' => $share->id,
                'reason' => $reason,
                'paused_duration_seconds' => $pausedDurationSeconds,
                'total_paused_duration' => $share->paused_duration_seconds + $pausedDurationSeconds
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to resume timer for share {$share->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get adjusted timer information for a share
     *
     * @param UserShare $share
     * @return array
     */
    public function getAdjustedTimer(UserShare $share): array
    {
        if (!$share->start_date || !$share->period) {
            return [
                'original_end_time' => null,
                'adjusted_end_time' => null,
                'total_paused_seconds' => 0,
                'currently_paused' => false,
                'effective_runtime' => 0
            ];
        }

        $startTime = Carbon::parse($share->start_date);
        $periodDays = $share->period;
        $originalEndTime = $startTime->copy()->addDays($periodDays);

        // Calculate total paused duration
        $totalPausedSeconds = $share->paused_duration_seconds ?? 0;

        // If currently paused, add current pause duration
        if ($share->timer_paused && $share->timer_paused_at) {
            $currentPauseDuration = Carbon::parse($share->timer_paused_at)->diffInSeconds(Carbon::now());
            $totalPausedSeconds += $currentPauseDuration;
        }

        // Calculate adjusted end time
        $adjustedEndTime = $originalEndTime->copy()->addSeconds($totalPausedSeconds);

        // Calculate effective runtime (actual running time)
        $now = Carbon::now();
        $effectiveRuntime = 0;
        
        if ($share->timer_paused && $share->timer_paused_at) {
            // Timer is currently paused - calculate up to pause time
            $effectiveRuntime = $startTime->diffInSeconds(Carbon::parse($share->timer_paused_at)) - ($share->paused_duration_seconds ?? 0);
        } else {
            // Timer is running - calculate up to now
            $effectiveRuntime = max(0, $startTime->diffInSeconds($now) - $totalPausedSeconds);
        }

        return [
            'original_end_time' => $originalEndTime,
            'adjusted_end_time' => $adjustedEndTime,
            'total_paused_seconds' => $totalPausedSeconds,
            'currently_paused' => (bool) $share->timer_paused,
            'effective_runtime' => max(0, $effectiveRuntime),
            'period_seconds' => $periodDays * 24 * 60 * 60,
            'completion_percentage' => min(100, ($effectiveRuntime / ($periodDays * 24 * 60 * 60)) * 100)
        ];
    }

    /**
     * Check if a share's timer should be matured
     *
     * @param UserShare $share
     * @return bool
     */
    public function shouldMatureShare(UserShare $share): bool
    {
        // Skip if already matured
        if ($share->is_ready_to_sell == 1) {
            return false;
        }

        // Skip if timer is paused
        if ($share->timer_paused) {
            return false;
        }

        // Check if adjusted end time has passed
        $timerInfo = $this->getAdjustedTimer($share);
        
        return $timerInfo['adjusted_end_time'] && 
               $timerInfo['adjusted_end_time']->isPast();
    }

    /**
     * Get timer status for display
     *
     * @param UserShare $share
     * @return array
     */
    public function getTimerStatus(UserShare $share): array
    {
        if ($share->is_ready_to_sell == 1) {
            return [
                'status' => 'matured',
                'display' => 'Share Matured',
                'class' => 'timer-matured',
                'color' => '#27ae60'
            ];
        }

        $timerInfo = $this->getAdjustedTimer($share);

        if ($timerInfo['currently_paused']) {
            return [
                'status' => 'paused',
                'display' => 'Timer Paused - Awaiting Payment Confirmation',
                'class' => 'timer-paused',
                'color' => '#f39c12'
            ];
        }

        if (!$timerInfo['adjusted_end_time']) {
            return [
                'status' => 'not_started',
                'display' => 'Timer Not Started',
                'class' => 'timer-pending',
                'color' => '#95a5a6'
            ];
        }

        if ($timerInfo['adjusted_end_time']->isPast()) {
            return [
                'status' => 'should_mature',
                'display' => 'Ready for Maturation',
                'class' => 'timer-ready',
                'color' => '#e67e22'
            ];
        }

        // Active countdown
        return [
            'status' => 'running',
            'display' => 'timer-active', // Special marker for JavaScript
            'class' => 'timer-running',
            'color' => '#3498db',
            'end_time' => $timerInfo['adjusted_end_time']->toISOString(),
            'completion_percentage' => round($timerInfo['completion_percentage'], 1)
        ];
    }

    /**
     * Resume all stuck timers that should be running
     *
     * @return array
     */
    public function resumeStuckTimers(): array
    {
        $stuckShares = UserShare::where('timer_paused', 1)
            ->whereIn('status', ['completed', 'paired'])
            ->where('is_ready_to_sell', 0)
            ->whereNotNull('timer_paused_at')
            ->get();

        $resumed = 0;
        $skipped = 0;

        foreach ($stuckShares as $share) {
            if ($this->shouldResumeTimer($share)) {
                if ($this->resumeTimer($share, 'Automatic stuck timer resumption')) {
                    $resumed++;
                } else {
                    $skipped++;
                }
            } else {
                $skipped++;
            }
        }

        return [
            'total_stuck' => $stuckShares->count(),
            'resumed' => $resumed,
            'skipped' => $skipped
        ];
    }

    /**
     * Determine if a stuck timer should be resumed
     *
     * @param UserShare $share
     * @return bool
     */
    private function shouldResumeTimer(UserShare $share): bool
    {
        // Admin-allocated shares should always resume
        if ($share->get_from === 'allocated-by-admin') {
            return true;
        }

        // Check for confirmed payments
        $confirmedPayments = $share->payments()->where('status', 'conformed')->count();
        if ($confirmedPayments > 0) {
            return true;
        }

        // Check if all pairs are paid
        $totalPairs = $share->pairedShares()->count();
        $paidPairs = $share->pairedShares()->where('is_paid', 1)->count();
        
        return $totalPairs > 0 && $paidPairs === $totalPairs;
    }

    /**
     * Clean up orphaned timer states
     *
     * @return array
     */
    public function cleanupOrphanedTimers(): array
    {
        // Find shares that have been paused for more than 24 hours without any payments
        $orphanedShares = UserShare::where('timer_paused', 1)
            ->where('timer_paused_at', '<', Carbon::now()->subHours(24))
            ->whereDoesntHave('payments')
            ->whereDoesntHave('pairedShares')
            ->get();

        $cleaned = 0;

        foreach ($orphanedShares as $share) {
            // Reset timer state for truly orphaned shares
            if ($this->resumeTimer($share, 'Orphaned timer cleanup')) {
                $cleaned++;
            }
        }

        return [
            'orphaned_found' => $orphanedShares->count(),
            'cleaned' => $cleaned
        ];
    }
}