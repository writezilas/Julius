<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Services\SmtpErrorHandlingService;
use Illuminate\Support\Facades\Log;

/**
 * NotificationPriorityService
 * 
 * Ensures proper priority order for notifications:
 * PRIORITY 1: Internal admin notifications (never fail)
 * PRIORITY 2: Email notifications via SMTP (can fail safely)
 * 
 * This service guarantees that critical internal notifications 
 * are always created regardless of external email service issues.
 */
class NotificationPriorityService
{
    private SmtpErrorHandlingService $smtpService;
    
    public function __construct()
    {
        $this->smtpService = new SmtpErrorHandlingService();
    }
    
    /**
     * Process support request notifications with proper priority
     * 
     * @param \App\Models\Support $support
     * @param \App\Models\User $user
     * @param string $context
     * @return array - ['admin_notification_created' => bool, 'email_sent' => bool]
     */
    public function processSupportNotification($support, $user, string $context = 'support'): array
    {
        $results = [
            'admin_notification_created' => false,
            'email_sent' => false,
            'admin_notification_id' => null,
            'email_error' => null
        ];
        
        // PRIORITY 1: Create admin notification FIRST - This must NEVER fail
        try {
            Log::info('PRIORITY 1 STARTING: Creating admin notification', [
                'support_id' => $support->id,
                'user' => $user->username,
                'context' => $context,
                'priority' => 1,
                'operation' => 'admin_notification_attempt'
            ]);
            
            $notification = AdminNotification::newSupportRequest($support, $user);
            $results['admin_notification_created'] = true;
            $results['admin_notification_id'] = $notification->id;
            
            Log::info('PRIORITY 1 COMPLETED: Admin notification created successfully', [
                'support_id' => $support->id,
                'notification_id' => $notification->id,
                'user' => $user->username,
                'context' => $context,
                'priority' => 1,
                'operation' => 'admin_notification_created'
            ]);
            
        } catch (\Exception $e) {
            // This should almost never happen, but if it does, log it as critical
            Log::critical('PRIORITY 1 FAILED: Admin notification creation failed - This is a critical system error!', [
                'support_id' => $support->id,
                'user' => $user->username,
                'context' => $context,
                'error' => $e->getMessage(),
                'priority' => 1,
                'operation' => 'admin_notification_failed'
            ]);
            // Continue processing even if this fails (extremely rare)
        }
        
        // PRIORITY 2: Attempt email notification (optional) - Can fail safely
        try {
            Log::info('PRIORITY 2 STARTING: Attempting email notification', [
                'support_id' => $support->id,
                'user' => $user->username,
                'context' => $context,
                'priority' => 2,
                'operation' => 'email_notification_attempt'
            ]);
            
            $support_email = get_gs_value('support_email') ?? 'support@autobidder.live';
            
            $emailSent = $this->smtpService->safelySendNotification(
                $support_email,
                new \App\Notifications\NewTicket($support),
                $context,
                ['support_id' => $support->id, 'user' => $user->username]
            );
            
            $results['email_sent'] = $emailSent;
            
            if ($emailSent) {
                Log::info('PRIORITY 2 COMPLETED: Email notification sent successfully', [
                    'support_id' => $support->id,
                    'user' => $user->username,
                    'email_sent_to' => $support_email,
                    'context' => $context,
                    'priority' => 2,
                    'operation' => 'email_notification_success'
                ]);
            } else {
                Log::warning('PRIORITY 2 SKIPPED: Email notification not sent (SMTP not configured or failed)', [
                    'support_id' => $support->id,
                    'user' => $user->username,
                    'reason' => 'SMTP not configured or authentication failed',
                    'context' => $context,
                    'priority' => 2,
                    'operation' => 'email_notification_skipped'
                ]);
            }
            
        } catch (\Exception $e) {
            // Email failure should never affect the support process
            $results['email_error'] = $e->getMessage();
            
            Log::warning('PRIORITY 2 FAILED: Email notification failed but support process continues', [
                'support_id' => $support->id,
                'user' => $user->username,
                'error' => $e->getMessage(),
                'context' => $context,
                'priority' => 2,
                'operation' => 'email_notification_failed'
            ]);
        }
        
        // Log final results
        Log::info('Notification processing completed', [
            'support_id' => $support->id,
            'user' => $user->username,
            'context' => $context,
            'admin_notification_created' => $results['admin_notification_created'],
            'email_sent' => $results['email_sent'],
            'admin_notification_id' => $results['admin_notification_id'],
            'email_failed' => !is_null($results['email_error'])
        ]);
        
        return $results;
    }
    
    /**
     * Process admin reply email notifications with proper priority
     * (Admin replies don't need admin notifications, just emails to users)
     * 
     * @param \App\Models\Support $support
     * @param \App\Models\User $admin
     * @param string $context
     * @return array - ['email_sent' => bool, 'error' => string|null]
     */
    public function processAdminReplyNotification($support, $admin, string $context = 'support_reply'): array
    {
        $results = [
            'email_sent' => false,
            'error' => null
        ];
        
        try {
            Log::info('Admin reply email notification starting', [
                'support_id' => $support->id,
                'user_email' => $support->email,
                'admin' => $admin->username,
                'context' => $context,
                'operation' => 'admin_reply_email_attempt'
            ]);
            
            $emailSent = $this->smtpService->safelySendNotification(
                $support->email,
                new \App\Notifications\AdminSupportReply($support, $admin),
                $context,
                [
                    'support_id' => $support->id,
                    'user_email' => $support->email,
                    'admin' => $admin->username
                ]
            );
            
            $results['email_sent'] = $emailSent;
            
            if ($emailSent) {
                Log::info('Admin reply email notification sent successfully', [
                    'support_id' => $support->id,
                    'user_email' => $support->email,
                    'admin' => $admin->username,
                    'context' => $context,
                    'operation' => 'admin_reply_email_success'
                ]);
            } else {
                Log::warning('Admin reply email notification skipped (SMTP not configured)', [
                    'support_id' => $support->id,
                    'user_email' => $support->email,
                    'admin' => $admin->username,
                    'reason' => 'SMTP not configured or authentication failed',
                    'context' => $context,
                    'operation' => 'admin_reply_email_skipped'
                ]);
            }
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
            
            Log::warning('Admin reply email notification failed', [
                'support_id' => $support->id,
                'user_email' => $support->email,
                'admin' => $admin->username,
                'error' => $e->getMessage(),
                'context' => $context,
                'operation' => 'admin_reply_email_failed'
            ]);
        }
        
        return $results;
    }
    
    /**
     * Get user-friendly message based on notification results
     * 
     * @param array $results
     * @param string $type - 'support' or 'admin_reply'
     * @return array - ['success' => string, 'warning' => string|null]
     */
    public function getResultMessages(array $results, string $type = 'support'): array
    {
        if ($type === 'support') {
            if ($results['admin_notification_created'] && $results['email_sent']) {
                return [
                    'success' => 'Support request submitted successfully. We will reach you as soon as possible.',
                    'warning' => null
                ];
            } elseif ($results['admin_notification_created'] && !$results['email_sent']) {
                return [
                    'success' => 'Support request submitted successfully. We will reach you as soon as possible.',
                    'warning' => 'Email notification could not be sent due to server configuration, but your request has been received.'
                ];
            } else {
                return [
                    'success' => 'Support request submitted successfully. We will reach you as soon as possible.',
                    'warning' => 'There was an issue with the notification system, but your request has been saved.'
                ];
            }
        } elseif ($type === 'admin_reply') {
            if ($results['email_sent']) {
                return [
                    'success' => 'Reply sent successfully.',
                    'warning' => null
                ];
            } else {
                return [
                    'success' => 'Reply saved successfully.',
                    'warning' => 'Email notification could not be sent due to SMTP configuration.'
                ];
            }
        }
        
        return [
            'success' => 'Operation completed successfully.',
            'warning' => null
        ];
    }
}