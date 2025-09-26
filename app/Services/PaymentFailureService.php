<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPaymentFailure;
use App\Models\UserShare;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PaymentFailureService
{
    /**
     * Handle payment failure for a user share
     *
     * @param int $userId
     * @param string $reason
     * @return array
     */
    public function handlePaymentFailure(int $userId, string $reason = 'Payment timeout'): array
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($userId);
            $paymentFailure = $user->getCurrentPaymentFailure();

            // Increment failure count
            $paymentFailure->incrementFailures($reason);

            Log::info("Payment failure recorded for user {$user->username}. Consecutive failures: {$paymentFailure->consecutive_failures}");

            $response = [
                'user_id' => $userId,
                'consecutive_failures' => $paymentFailure->consecutive_failures,
                'suspended' => false,
                'suspension_until' => null,
            ];

            // Check if user should be suspended (3 consecutive failures)
            if ($paymentFailure->shouldSuspend()) {
                $suspensionUntil = $user->suspendForPaymentFailures();
                
                // Get the updated payment failure record to get suspension info
                $paymentFailure->refresh();

                // Automatically logout the user from all sessions
                $this->logoutUserFromAllSessions($user);

                $response['suspended'] = true;
                $response['suspension_until'] = $suspensionUntil;
                $response['suspension_level'] = $paymentFailure->suspension_level;
                $response['suspension_duration_hours'] = $paymentFailure->suspension_duration_hours;
                $response['auto_logged_out'] = true;

                Log::warning("User {$user->username} suspended due to 3 consecutive payment failures until {$suspensionUntil} (Level {$paymentFailure->suspension_level}, Duration: {$paymentFailure->suspension_duration_hours}h) and automatically logged out");
            }

            DB::commit();
            return $response;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment failure handling error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reset payment failures for a user (called when payment succeeds)
     *
     * @param int $userId
     * @return void
     */
    public function resetPaymentFailures(int $userId): void
    {
        try {
            $user = User::findOrFail($userId);
            $paymentFailure = $user->paymentFailures()->latest()->first();

            if ($paymentFailure && $paymentFailure->consecutive_failures > 0) {
                $paymentFailure->resetFailures();
                Log::info("Payment failures reset for user {$user->username}");
            }

        } catch (\Exception $e) {
            Log::error('Error resetting payment failures: ' . $e->getMessage());
        }
    }

    /**
     * Process expired suspensions and lift them
     *
     * @return int Number of suspensions lifted
     */
    public function processExpiredSuspensions(): int
    {
        // Find users with expired suspensions - check both status='suspend' and inconsistent states
        $suspendedUsers = User::where(function($query) {
                $query->where('status', 'suspend')
                      ->orWhere('suspension_until', '<=', now());
            })
            ->whereNotNull('suspension_until')
            ->where('suspension_until', '<=', now())
            ->get();

        $lifted = 0;

        foreach ($suspendedUsers as $user) {
            try {
                // Only lift if suspension is actually expired
                if ($user->suspension_until && $user->suspension_until->isPast()) {
                    $user->liftSuspension();
                    $lifted++;
                    Log::info("Suspension lifted for user {$user->username} (was in inconsistent state: status={$user->status})");
                }
            } catch (\Exception $e) {
                Log::error("Error lifting suspension for user {$user->username}: " . $e->getMessage());
            }
        }

        if ($lifted > 0) {
            Log::info("Lifted {$lifted} expired suspensions");
        }

        return $lifted;
    }

    /**
     * Get users approaching suspension (2 consecutive failures)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersApproachingSuspension()
    {
        return UserPaymentFailure::where('consecutive_failures', 2)
            ->whereNull('suspended_at')
            ->with('user')
            ->get();
    }

    /**
     * Get currently suspended users
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSuspendedUsers()
    {
        return User::where('status', 'suspend')
            ->whereNotNull('suspension_until')
            ->with(['paymentFailures' => function($query) {
                $query->latest()->first();
            }])
            ->get();
    }

    /**
     * Get statistics about payment failures and suspensions
     *
     * @return array
     */
    public function getFailureStatistics(): array
    {
        return [
            'total_failures_today' => UserPaymentFailure::whereDate('last_failure_at', today())->sum('consecutive_failures'),
            'users_with_failures' => UserPaymentFailure::where('consecutive_failures', '>', 0)->count(),
            'users_approaching_suspension' => UserPaymentFailure::where('consecutive_failures', 2)->whereNull('suspended_at')->count(),
            'currently_suspended' => User::where('status', 'suspend')->whereNotNull('suspension_until')->count(),
            'suspensions_lifted_today' => UserPaymentFailure::whereDate('suspension_lifted_at', today())->count(),
            'level_1_suspensions' => UserPaymentFailure::where('suspension_level', 1)->whereNotNull('suspended_at')->count(),
            'level_2_suspensions' => UserPaymentFailure::where('suspension_level', 2)->whereNotNull('suspended_at')->count(),
            'level_3_plus_suspensions' => UserPaymentFailure::where('suspension_level', '>=', 3)->whereNotNull('suspended_at')->count(),
        ];
    }

    /**
     * Process suspension level resets for users who have been good
     *
     * @return int Number of levels reset
     */
    public function processSuspensionLevelResets(): int
    {
        $resetCount = 0;
        
        $usersToReset = UserPaymentFailure::where('suspension_level', '>', 0)
            ->where('consecutive_failures', 0)
            ->where(function($query) {
                $query->whereNull('last_failure_at')
                      ->orWhere('last_failure_at', '<=', now()->subDays(30));
            })
            ->with('user')
            ->get();

        foreach ($usersToReset as $paymentFailure) {
            try {
                $oldLevel = $paymentFailure->suspension_level;
                $paymentFailure->resetSuspensionLevel();
                $resetCount++;
                
                Log::info("Suspension level reset from {$oldLevel} to 0 for user {$paymentFailure->user->username} due to 30 days without failures");
                
            } catch (\Exception $e) {
                Log::error("Error resetting suspension level for user {$paymentFailure->user->username}: " . $e->getMessage());
            }
        }

        if ($resetCount > 0) {
            Log::info("Reset suspension levels for {$resetCount} users");
        }

        return $resetCount;
    }

    /**
     * Check if share timer should be adjusted for paused duration
     *
     * @param UserShare $share
     * @return array
     */
    public function getAdjustedShareTimer(UserShare $share): array
    {
        if (!$share->start_date || !$share->period) {
            return [
                'original_end_time' => null,
                'adjusted_end_time' => null,
                'paused_duration' => 0,
                'is_paused' => false
            ];
        }

        $originalEndTime = \Carbon\Carbon::parse($share->start_date)->addDays($share->period);
        $pausedDuration = $share->paused_duration_seconds;

        // If currently paused, add current pause duration
        if ($share->timer_paused && $share->timer_paused_at) {
            $pausedDuration += $share->timer_paused_at->diffInSeconds(now());
        }

        $adjustedEndTime = $originalEndTime->addSeconds($pausedDuration);

        return [
            'original_end_time' => $originalEndTime,
            'adjusted_end_time' => $adjustedEndTime,
            'paused_duration' => $pausedDuration,
            'is_paused' => $share->timer_paused
        ];
    }

    /**
     * Logout user from all active sessions
     *
     * @param User $user
     * @return void
     */
    private function logoutUserFromAllSessions(User $user): void
    {
        try {
            // If this is the currently authenticated user, logout immediately
            if (Auth::check() && Auth::id() == $user->id) {
                Auth::logout();
                Session::invalidate();
                Session::regenerateToken();
                Log::info("Current user {$user->username} logged out immediately due to suspension");
            }
            
            // Handle different session drivers
            $sessionDriver = config('session.driver');
            
            if ($sessionDriver === 'database') {
                // Clear all sessions for this user from the sessions table
                DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->delete();
                Log::info("Database sessions cleared for suspended user {$user->username}");
            } elseif ($sessionDriver === 'file') {
                // For file sessions, we'll rely on middleware to handle logout on next request
                // and the JavaScript monitor to force refresh
                Log::info("File-based sessions detected - user {$user->username} will be logged out via middleware and JavaScript monitor");
            } else {
                Log::info("Session driver '{$sessionDriver}' detected for user {$user->username} - relying on middleware for logout");
            }
            
        } catch (\Exception $e) {
            Log::error("Error logging out user {$user->username} from all sessions: " . $e->getMessage());
        }
    }
}
