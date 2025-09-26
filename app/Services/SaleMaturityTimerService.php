<?php

namespace App\Services;

use App\Models\UserShare;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service to handle Sale Maturity Timers
 * 
 * This service manages the timer that determines when shares are ready
 * to be available in the market for sale. This is completely separate
 * from payment deadline timers which handle buyer payment timeouts.
 * 
 * Sale Maturity Timer Rules:
 * 1. Starts when status = 'completed' and start_date is set
 * 2. Duration = period days from start_date
 * 3. Can only be paused by admin intervention
 * 4. Never affected by payment events or buyer actions
 * 5. When complete, sets is_ready_to_sell = 1
 */
class SaleMaturityTimerService
{
    /**
     * Check if a share's maturity timer should cause the share to mature
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

        // Skip if not in completed status
        if ($share->status !== 'completed') {
            return false;
        }

        // Skip if no start date or period set
        if (!$share->start_date || !$share->period) {
            return false;
        }

        // Skip if maturity timer is paused (admin intervention only)
        if ($share->maturity_timer_paused) {
            return false;
        }

        // Check if adjusted maturity end time has passed
        $timerInfo = $this->getMaturityTimer($share);
        
        return $timerInfo['adjusted_end_time'] && 
               $timerInfo['adjusted_end_time']->isPast();
    }

    /**
     * Get maturity timer information for a share
     *
     * @param UserShare $share
     * @return array
     */
    public function getMaturityTimer(UserShare $share): array
    {
        if (!$share->start_date || !$share->period) {
            return [
                'original_end_time' => null,
                'adjusted_end_time' => null,
                'total_paused_seconds' => 0,
                'currently_paused' => false,
                'effective_runtime' => 0,
                'completion_percentage' => 0
            ];
        }

        $startTime = Carbon::parse($share->start_date);
        $periodDays = $share->period;
        $originalEndTime = $startTime->copy()->addDays($periodDays);

        // Calculate total paused duration (maturity timer only)
        $totalPausedSeconds = $share->maturity_paused_duration_seconds ?? 0;

        // If currently paused, add current pause duration
        if ($share->maturity_timer_paused && $share->maturity_timer_paused_at) {
            $currentPauseDuration = Carbon::parse($share->maturity_timer_paused_at)->diffInSeconds(Carbon::now());
            $totalPausedSeconds += $currentPauseDuration;
        }

        // Calculate adjusted end time
        $adjustedEndTime = $originalEndTime->copy()->addSeconds($totalPausedSeconds);

        // Calculate effective runtime (actual running time)
        $now = Carbon::now();
        $effectiveRuntime = 0;
        
        if ($share->maturity_timer_paused && $share->maturity_timer_paused_at) {
            // Timer is currently paused - calculate up to pause time
            $effectiveRuntime = $startTime->diffInSeconds(Carbon::parse($share->maturity_timer_paused_at)) 
                               - ($share->maturity_paused_duration_seconds ?? 0);
        } else {
            // Timer is running - calculate up to now
            $effectiveRuntime = max(0, $startTime->diffInSeconds($now) - $totalPausedSeconds);
        }

        $periodSeconds = $periodDays * 24 * 60 * 60;
        $completionPercentage = $periodSeconds > 0 ? min(100, ($effectiveRuntime / $periodSeconds) * 100) : 0;

        return [
            'original_end_time' => $originalEndTime,
            'adjusted_end_time' => $adjustedEndTime,
            'total_paused_seconds' => $totalPausedSeconds,
            'currently_paused' => (bool) $share->maturity_timer_paused,
            'effective_runtime' => max(0, $effectiveRuntime),
            'period_seconds' => $periodSeconds,
            'completion_percentage' => $completionPercentage
        ];
    }

    /**
     * Pause the maturity timer (admin intervention only)
     *
     * @param UserShare $share
     * @param string $reason
     * @return bool
     */
    public function pauseMaturityTimer(UserShare $share, string $reason = 'Admin paused maturity timer'): bool
    {
        try {
            // Only pause if timer is not already paused
            if ($share->maturity_timer_paused) {
                Log::warning("Maturity timer already paused for share: {$share->ticket_no}");
                return false;
            }

            // Only allow pausing for shares that are actively maturing
            if ($share->status !== 'completed' || $share->is_ready_to_sell == 1) {
                Log::warning("Cannot pause maturity timer - share not in maturation phase: {$share->ticket_no}");
                return false;
            }

            $share->update([
                'maturity_timer_paused' => 1,
                'maturity_timer_paused_at' => Carbon::now(),
                'maturity_pause_reason' => $reason
            ]);

            Log::info("Maturity timer paused for share: {$share->ticket_no}", [
                'share_id' => $share->id,
                'reason' => $reason,
                'paused_at' => Carbon::now()->toDateTimeString()
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to pause maturity timer for share {$share->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Resume the maturity timer
     *
     * @param UserShare $share
     * @return bool
     */
    public function resumeMaturityTimer(UserShare $share): bool
    {
        try {
            // Only resume if timer is actually paused
            if (!$share->maturity_timer_paused) {
                Log::info("Maturity timer not paused for share: {$share->ticket_no} - no action needed");
                return true;
            }

            // Calculate paused duration
            $pausedAt = Carbon::parse($share->maturity_timer_paused_at);
            $pausedDurationSeconds = $pausedAt->diffInSeconds(Carbon::now());

            // Update share to resume timer
            $share->update([
                'maturity_timer_paused' => 0,
                'maturity_timer_paused_at' => null,
                'maturity_paused_duration_seconds' => $share->maturity_paused_duration_seconds + $pausedDurationSeconds,
                'maturity_pause_reason' => null
            ]);

            Log::info("Maturity timer resumed for share: {$share->ticket_no}", [
                'share_id' => $share->id,
                'paused_duration_seconds' => $pausedDurationSeconds,
                'total_paused_duration' => $share->maturity_paused_duration_seconds + $pausedDurationSeconds
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to resume maturity timer for share {$share->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get maturity timer status for display
     *
     * @param UserShare $share
     * @return array
     */
    public function getMaturityStatus(UserShare $share): array
    {
        if ($share->is_ready_to_sell == 1) {
            return [
                'status' => 'matured',
                'display' => 'Share Matured',
                'class' => 'timer-matured',
                'color' => '#27ae60'
            ];
        }

        if ($share->status !== 'completed') {
            return [
                'status' => 'not_started',
                'display' => 'Not Started',
                'class' => 'timer-pending',
                'color' => '#95a5a6'
            ];
        }

        $timerInfo = $this->getMaturityTimer($share);

        if ($timerInfo['currently_paused']) {
            $reason = $share->maturity_pause_reason ?? 'Admin intervention';
            return [
                'status' => 'paused',
                'display' => "Maturity Timer Paused - {$reason}",
                'class' => 'timer-paused',
                'color' => '#f39c12'
            ];
        }

        if (!$timerInfo['adjusted_end_time']) {
            return [
                'status' => 'not_started',
                'display' => 'Maturity Timer Not Started',
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

        // Active maturity countdown
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
     * Mature a share when its timer has completed
     *
     * @param UserShare $share
     * @return bool
     */
    public function matureShare(UserShare $share): bool
    {
        try {
            if (!$this->shouldMatureShare($share)) {
                return false;
            }

            $share->update([
                'is_ready_to_sell' => 1,
                'matured_at' => Carbon::now()
            ]);

            Log::info("Share matured via maturity timer", [
                'share_id' => $share->id,
                'ticket_no' => $share->ticket_no,
                'user_id' => $share->user_id,
                'matured_at' => Carbon::now()->toDateTimeString()
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to mature share {$share->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all shares that should be matured
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSharesReadyToMature()
    {
        return UserShare::where('status', 'completed')
            ->where('is_ready_to_sell', 0)
            ->where('maturity_timer_paused', 0)
            ->whereNotNull('start_date')
            ->whereNotNull('period')
            ->get()
            ->filter(function ($share) {
                return $this->shouldMatureShare($share);
            });
    }

    /**
     * Mature all shares that are ready
     *
     * @return array
     */
    public function matureAllReadyShares(): array
    {
        $readyShares = $this->getSharesReadyToMature();
        $matured = 0;
        $failed = 0;

        foreach ($readyShares as $share) {
            if ($this->matureShare($share)) {
                $matured++;
            } else {
                $failed++;
            }
        }

        return [
            'total_ready' => $readyShares->count(),
            'matured' => $matured,
            'failed' => $failed
        ];
    }
}