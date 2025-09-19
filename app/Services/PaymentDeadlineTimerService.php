<?php

namespace App\Services;

use App\Models\UserShare;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service to handle Payment Deadline Timers
 * 
 * This service manages the timer that determines when buyers must
 * complete their payments before their purchases are marked as failed.
 * This is completely separate from sale maturity timers.
 * 
 * Payment Deadline Timer Rules:
 * 1. Starts when share is created with get_from = 'purchase'
 * 2. Duration = payment_deadline_minutes from created_at
 * 3. Never paused - it's a hard deadline
 * 4. When expired, marks share as 'failed' status
 * 5. Independent of sale maturity timers
 */
class PaymentDeadlineTimerService
{
    /**
     * Check if payment deadline has expired for a share
     *
     * @param UserShare $share
     * @return bool
     */
    public function hasPaymentExpired(UserShare $share): bool
    {
        // Only applies to purchase shares
        if ($share->get_from !== 'purchase') {
            return false;
        }

        // Only applies to shares that need payment
        if (!in_array($share->status, ['pending', 'paired'])) {
            return false;
        }

        // Check if payment has already been submitted
        if ($this->hasPaymentBeenSubmitted($share)) {
            return false;
        }

        $deadline = $this->getPaymentDeadline($share);
        return $deadline->isPast();
    }

    /**
     * Get the payment deadline for a share
     *
     * @param UserShare $share
     * @return Carbon
     */
    public function getPaymentDeadline(UserShare $share): Carbon
    {
        $deadlineMinutes = $share->payment_deadline_minutes ?? get_gs_value('bought_time') ?? 60;
        return Carbon::parse($share->created_at)->addMinutes($deadlineMinutes);
    }

    /**
     * Get payment time remaining information
     *
     * @param UserShare $share
     * @return array
     */
    public function getPaymentTimeRemaining(UserShare $share): array
    {
        if ($share->get_from !== 'purchase') {
            return [
                'applicable' => false,
                'message' => 'Payment deadline not applicable for this share type'
            ];
        }

        $deadline = $this->getPaymentDeadline($share);
        $now = Carbon::now();
        $timeRemaining = $deadline->diffInSeconds($now, false); // false = can be negative

        if ($timeRemaining <= 0) {
            return [
                'applicable' => true,
                'expired' => true,
                'message' => 'Payment deadline expired',
                'deadline' => $deadline,
                'seconds_remaining' => 0
            ];
        }

        $days = floor($timeRemaining / (24 * 60 * 60));
        $hours = floor(($timeRemaining % (24 * 60 * 60)) / (60 * 60));
        $minutes = floor(($timeRemaining % (60 * 60)) / 60);
        $seconds = $timeRemaining % 60;

        $timeString = '';
        if ($days > 0) $timeString .= $days . 'd ';
        if ($hours > 0) $timeString .= $hours . 'h ';
        if ($minutes > 0) $timeString .= $minutes . 'm ';
        $timeString .= $seconds . 's';

        return [
            'applicable' => true,
            'expired' => false,
            'deadline' => $deadline,
            'seconds_remaining' => $timeRemaining,
            'time_string' => trim($timeString),
            'urgency_level' => $this->getUrgencyLevel($timeRemaining)
        ];
    }

    /**
     * Get payment timer status for display
     *
     * @param UserShare $share
     * @return array
     */
    public function getPaymentTimerStatus(UserShare $share): array
    {
        if ($share->get_from !== 'purchase') {
            return [
                'status' => 'not_applicable',
                'display' => 'No payment deadline',
                'class' => 'payment-timer-na',
                'color' => '#95a5a6'
            ];
        }

        if ($share->status === 'failed') {
            return [
                'status' => 'expired',
                'display' => 'Payment Expired',
                'class' => 'payment-timer-expired',
                'color' => '#e74c3c'
            ];
        }

        if ($share->status === 'completed') {
            return [
                'status' => 'paid',
                'display' => 'Payment Made',
                'class' => 'payment-timer-completed',
                'color' => '#27ae60'
            ];
        }

        if ($this->hasPaymentBeenSubmitted($share)) {
            return [
                'status' => 'submitted',
                'display' => 'Waiting for Payment Confirmation',
                'class' => 'payment-timer-submitted',
                'color' => '#17a2b8'
            ];
        }

        $timeInfo = $this->getPaymentTimeRemaining($share);
        
        if ($timeInfo['expired']) {
            return [
                'status' => 'expired',
                'display' => 'Payment Expired',
                'class' => 'payment-timer-expired',
                'color' => '#e74c3c'
            ];
        }

        // Active payment deadline countdown
        $urgencyClass = 'payment-timer-normal';
        if ($timeInfo['urgency_level'] === 'urgent') {
            $urgencyClass = 'payment-timer-urgent';
        } elseif ($timeInfo['urgency_level'] === 'warning') {
            $urgencyClass = 'payment-timer-warning';
        }

        return [
            'status' => 'active',
            'display' => $timeInfo['time_string'],
            'class' => $urgencyClass,
            'color' => $this->getUrgencyColor($timeInfo['urgency_level']),
            'seconds_remaining' => $timeInfo['seconds_remaining'],
            'deadline' => $timeInfo['deadline']->toISOString()
        ];
    }

    /**
     * Check if payment has been submitted for a share
     *
     * @param UserShare $share
     * @return bool
     */
    private function hasPaymentBeenSubmitted(UserShare $share): bool
    {
        // Check direct payments
        $hasDirectPayment = $share->payments()->where('status', 'paid')->exists();
        if ($hasDirectPayment) {
            return true;
        }

        // Check payment via share pairing (payment might be on seller's share)
        $hasPaidPairs = $share->pairedShares()->where('is_paid', 1)->exists();
        if ($hasPaidPairs) {
            return true;
        }

        return false;
    }

    /**
     * Get urgency level based on time remaining
     *
     * @param int $secondsRemaining
     * @return string
     */
    private function getUrgencyLevel(int $secondsRemaining): string
    {
        if ($secondsRemaining < 300) { // Less than 5 minutes
            return 'urgent';
        } elseif ($secondsRemaining < 1800) { // Less than 30 minutes
            return 'warning';
        } else {
            return 'normal';
        }
    }

    /**
     * Get color based on urgency level
     *
     * @param string $urgencyLevel
     * @return string
     */
    private function getUrgencyColor(string $urgencyLevel): string
    {
        switch ($urgencyLevel) {
            case 'urgent':
                return '#e74c3c'; // Red
            case 'warning':
                return '#f39c12'; // Orange
            default:
                return '#3498db'; // Blue
        }
    }

    /**
     * Get all shares with expired payment deadlines
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSharesWithExpiredPayments()
    {
        return UserShare::where('get_from', 'purchase')
            ->whereIn('status', ['pending', 'paired'])
            ->get()
            ->filter(function ($share) {
                return $this->hasPaymentExpired($share) && !$this->hasPaymentBeenSubmitted($share);
            });
    }

    /**
     * Mark shares as failed when payment deadline expires
     *
     * @param UserShare $share
     * @return bool
     */
    public function markPaymentExpired(UserShare $share): bool
    {
        try {
            if (!$this->hasPaymentExpired($share)) {
                return false;
            }

            $deadlineMinutes = $share->payment_deadline_minutes ?? get_gs_value('bought_time') ?? 60;
            $reason = "Payment deadline expired after {$deadlineMinutes} minutes";

            $share->update([
                'status' => 'failed'
            ]);

            Log::info("Share marked as failed due to payment deadline expiry", [
                'share_id' => $share->id,
                'ticket_no' => $share->ticket_no,
                'user_id' => $share->user_id,
                'deadline_minutes' => $deadlineMinutes,
                'reason' => $reason
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to mark payment as expired for share {$share->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process all expired payment deadlines
     *
     * @return array
     */
    public function processExpiredPaymentDeadlines(): array
    {
        $expiredShares = $this->getSharesWithExpiredPayments();
        $processed = 0;
        $failed = 0;

        foreach ($expiredShares as $share) {
            if ($this->markPaymentExpired($share)) {
                $processed++;
            } else {
                $failed++;
            }
        }

        return [
            'total_expired' => $expiredShares->count(),
            'processed' => $processed,
            'failed' => $failed
        ];
    }
}