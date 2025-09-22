<?php

namespace App\Services;

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use App\Models\User;
use App\Models\Log;
use App\Notifications\PaymentApproved;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Exception;

class PaymentConfirmationService
{
    /**
     * Validates that all required data exists and is in correct state
     */
    public function validatePaymentConfirmation($paymentId)
    {
        $payment = UserSharePayment::find($paymentId);
        
        if (!$payment) {
            return [
                'success' => false,
                'message' => 'Payment record not found.',
                'error_code' => 'PAYMENT_NOT_FOUND'
            ];
        }
        
        if ($payment->status === 'conformed') {
            return [
                'success' => false,
                'message' => 'Payment has already been confirmed.',
                'error_code' => 'ALREADY_CONFIRMED'
            ];
        }
        
        $sharePair = UserSharePair::find($payment->user_share_pair_id);
        
        if (!$sharePair) {
            return [
                'success' => false,
                'message' => 'Share pairing record not found. Please contact support.',
                'error_code' => 'SHARE_PAIR_NOT_FOUND'
            ];
        }
        
        if ($sharePair->is_paid == 1) {
            return [
                'success' => false,
                'message' => 'This share pair has already been processed.',
                'error_code' => 'ALREADY_PROCESSED'
            ];
        }
        
        if ($sharePair->share <= 0) {
            return [
                'success' => false,
                'message' => 'Cannot approve payment for 0 shares.',
                'error_code' => 'INVALID_SHARES'
            ];
        }
        
        $userShare = UserShare::find($sharePair->user_share_id);
        $pairedShare = UserShare::find($sharePair->paired_user_share_id);
        
        if (!$userShare || !$pairedShare) {
            return [
                'success' => false,
                'message' => 'Related share records not found. Please contact support.',
                'error_code' => 'SHARES_NOT_FOUND'
            ];
        }
        
        return [
            'success' => true,
            'payment' => $payment,
            'sharePair' => $sharePair,
            'userShare' => $userShare,
            'pairedShare' => $pairedShare
        ];
    }
    
    /**
     * Validates seller share has sufficient hold quantity
     */
    public function validateSellerQuantity($pairedShare, $sharePair)
    {
        // Refresh model to get latest data
        $pairedShare->refresh();
        
        if ($pairedShare->hold_quantity < $sharePair->share) {
            return [
                'success' => false,
                'message' => 'Payment confirmation failed due to insufficient hold quantity. This may be due to concurrent processing. Please try again or contact support.',
                'error_code' => 'INSUFFICIENT_HOLD_QUANTITY'
            ];
        }
        
        // Additional safety check - ensure we don't go negative
        $newHoldQuantity = $pairedShare->hold_quantity - $sharePair->share;
        if ($newHoldQuantity < 0) {
            return [
                'success' => false,
                'message' => 'Cannot process payment - insufficient shares available. Please contact support.',
                'error_code' => 'WOULD_BE_NEGATIVE'
            ];
        }
        
        return ['success' => true];
    }
    
    /**
     * Creates payment confirmation logs
     */
    public function createPaymentLogs($payment, $user = null)
    {
        try {
            if (!$user) {
                $user = User::find($payment->sender_id);
            }
            
            if ($user) {
                // Save log for payment receiver
                $log = new Log();
                $log->remarks = "You confirmed a payment from " . $user->username;
                $log->type = "payment";
                $log->value = $payment->amount;
                $log->user_id = auth()->user()->id;
                $payment->logs()->save($log);

                // Save log for payers
                $log = new Log();
                $log->remarks = "Your payment is confirmed by " . auth()->user()->username;
                $log->type = "payment";
                $log->value = $payment->amount;
                $log->user_id = $user->id;
                $payment->logs()->save($log);
            }
            
            return true;
        } catch (Exception $e) {
            \Log::error('Failed to create payment logs: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sends payment approval notification safely
     */
    public function sendPaymentNotification($payment)
    {
        try {
            $user = User::find($payment->sender_id);
            
            if (!$user) {
                \Log::error('Sender user not found for payment ID: ' . $payment->id . ', Sender ID: ' . $payment->sender_id);
                return false;
            }
            
            Notification::send($user, new PaymentApproved($payment));
            return true;
        } catch (Exception $th) {
            \Log::error('Failed to send payment approval notification. File:' . $th->getFile() . ' Line:' . $th->getLine() . ' Message:' . $th->getMessage());
            return false;
        }
    }
    
    /**
     * Handles the timer management for completed shares
     */
    public function manageTimers($userShare)
    {
        try {
            if ($userShare->share_will_get == $userShare->total_share_count) {
                $enhancedTimerService = new \App\Services\EnhancedTimerManagementService();
                
                // Clear payment timer - payment phase is complete
                $userShare->update([
                    'payment_timer_paused' => 0,
                    'payment_timer_paused_at' => null,
                    // Also clear legacy timer fields for backward compatibility
                    'timer_paused' => 0,
                    'timer_paused_at' => null
                ]);
                
                // Start selling timer (investment maturity) - this is when investment period begins
                if ($userShare->get_from === 'purchase') {
                    $enhancedTimerService->startSellingTimer($userShare, 'Payment confirmed by seller - investment period starts now');
                    
                    \Log::info("Investment period started for buyer share {$userShare->ticket_no} after payment confirmation", [
                        'share_id' => $userShare->id,
                        'timer_type' => 'investment_started',
                        'investment_period_days' => $userShare->period,
                        'selling_started_at' => now()->toDateTimeString()
                    ]);
                }
                
                \Log::info("Payment timer cleared for buyer share {$userShare->ticket_no} after payment confirmation", [
                    'share_id' => $userShare->id,
                    'timer_type' => 'payment_cleared',
                    'investment_timer_started' => ($userShare->get_from === 'purchase')
                ]);
            }
            
            return true;
        } catch (Exception $e) {
            \Log::error("Failed to manage timers for share {$userShare->id}: " . $e->getMessage());
            // Return true because timer management failure shouldn't fail the entire transaction
            return true;
        }
    }
    
    /**
     * Validates that seller share status transition won't violate constraints
     */
    public function validateStatusTransition($pairedShare, $newStatus)
    {
        // Check the chk_ready_to_sell_logic constraint
        // (is_ready_to_sell = 0) OR (is_ready_to_sell = 1 AND status IN ('completed', 'failed', 'sold'))
        if ($pairedShare->is_ready_to_sell == 1 && !in_array($newStatus, ['completed', 'failed', 'sold'])) {
            return [
                'success' => false,
                'message' => 'Cannot change share status to ' . $newStatus . ' when is_ready_to_sell is 1. Only completed, failed, or sold statuses are allowed.',
                'error_code' => 'CONSTRAINT_VIOLATION'
            ];
        }
        
        return ['success' => true];
    }
    
    /**
     * Prepares seller share for status transition by handling constraint requirements
     */
    public function prepareSellerShareForStatusChange($pairedShare, $newStatus)
    {
        // If we're setting to a status not allowed by the constraint, clear is_ready_to_sell
        if ($pairedShare->is_ready_to_sell == 1 && !in_array($newStatus, ['completed', 'failed', 'sold'])) {
            $pairedShare->is_ready_to_sell = 0;
            \Log::info("Cleared is_ready_to_sell flag for share {$pairedShare->ticket_no} due to status transition to {$newStatus}");
        }
        
        return $pairedShare;
    }
    
    /**
     * Determines appropriate error message based on exception
     */
    public function getErrorMessage(Exception $e)
    {
        if (strpos($e->getMessage(), 'Integrity constraint violation') !== false) {
            // Check for specific constraint violations to provide better error messages
            if (strpos($e->getMessage(), 'chk_ready_to_sell_logic') !== false) {
                return 'Payment confirmation failed due to share status constraint. The system has been updated to handle this issue automatically.';
            } elseif (strpos($e->getMessage(), 'chk_quantities') !== false) {
                return 'Payment confirmation failed due to invalid share quantities. Please contact support.';
            } else {
                return 'Payment confirmation failed due to data integrity issues. Please contact support with trade reference.';
            }
        } elseif (strpos($e->getMessage(), 'Connection') !== false || strpos($e->getMessage(), 'database') !== false) {
            return 'Payment confirmation failed due to database connectivity. Please try again in a few moments.';
        } elseif (strpos($e->getMessage(), 'Call to a member function') !== false) {
            return 'Payment confirmation failed due to missing data. Please contact support to resolve this issue.';
        } elseif (strpos($e->getMessage(), 'Deadlock') !== false) {
            return 'Payment confirmation failed due to concurrent processing. Please try again in a few seconds.';
        }
        
        return 'Failed to confirm payment receive. Please try again later.';
    }
}