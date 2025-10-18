<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Service to handle SMTP configuration validation and error handling
 * Provides centralized email error handling to gracefully skip email notifications
 * when SMTP is misconfigured or fails, preventing application crashes
 */
class SmtpErrorHandlingService
{
    /**
     * Safely send a notification with SMTP error handling
     * 
     * @param mixed $notifiable - User, email string, or notification route
     * @param mixed $notification - Notification instance
     * @param string $context - Context for logging (e.g., 'support', 'payment')
     * @param array $logData - Additional data to include in logs
     * @return bool - true if email sent successfully, false if skipped due to SMTP issues
     */
    public function safelySendNotification($notifiable, $notification, string $context = 'general', array $logData = []): bool
    {
        try {
            // Check if basic SMTP settings are configured
            $smtpHost = env('MAIL_HOST');
            $smtpUsername = env('MAIL_USERNAME'); 
            $smtpPassword = env('MAIL_PASSWORD');
            
            if (empty($smtpHost) || empty($smtpUsername) || empty($smtpPassword)) {
                Log::warning('SMTP settings not configured - skipping email notification', array_merge([
                    'context' => $context,
                    'smtp_host' => $smtpHost ? 'configured' : 'missing',
                    'smtp_username' => $smtpUsername ? 'configured' : 'missing', 
                    'smtp_password' => $smtpPassword ? 'configured' : 'missing',
                    'notification_class' => get_class($notification)
                ], $logData));
                
                return false;
            }
            
            // Attempt to send the notification
            if (is_string($notifiable)) {
                // If notifiable is an email string, use route notification
                Notification::route('mail', $notifiable)->notify($notification);
            } else {
                // If notifiable is a user or other model, use direct notification
                Notification::send($notifiable, $notification);
            }
            
            Log::info('Email notification sent successfully', array_merge([
                'context' => $context,
                'notification_class' => get_class($notification),
                'notifiable_type' => is_string($notifiable) ? 'email_string' : get_class($notifiable)
            ], $logData));
            
            return true;
            
        } catch (\Exception $e) {
            // Log SMTP error but don't fail the entire process
            Log::warning('Failed to send email notification - continuing without email', array_merge([
                'context' => $context,
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'notification_class' => get_class($notification),
                'smtp_host' => env('MAIL_HOST'),
                'smtp_username' => env('MAIL_USERNAME')
            ], $logData));
            
            // Check if this is specifically an SMTP authentication error
            if ($this->isSmtpAuthenticationError($e)) {
                Log::error('SMTP Authentication failed - please check email settings in admin panel', array_merge([
                    'context' => $context,
                    'error_details' => $e->getMessage()
                ], $logData));
            }
            
            return false;
        }
    }
    
    /**
     * Check if an exception is specifically an SMTP authentication error
     * 
     * @param \Exception $e
     * @return bool
     */
    public function isSmtpAuthenticationError(\Exception $e): bool
    {
        $message = $e->getMessage();
        
        return str_contains($message, 'authentication failed') ||
               str_contains($message, 'Expected response code "235"') ||
               str_contains($message, '535 5.7.8 Error: authentication failed') ||
               str_contains($message, 'LOGIN') ||
               str_contains($message, 'PLAIN');
    }
    
    /**
     * Check if SMTP is properly configured
     * 
     * @return array - ['configured' => bool, 'missing' => array]
     */
    public function checkSmtpConfiguration(): array
    {
        $required = [
            'MAIL_HOST' => env('MAIL_HOST'),
            'MAIL_USERNAME' => env('MAIL_USERNAME'),
            'MAIL_PASSWORD' => env('MAIL_PASSWORD'),
            'MAIL_PORT' => env('MAIL_PORT'),
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS')
        ];
        
        $missing = [];
        foreach ($required as $key => $value) {
            if (empty($value)) {
                $missing[] = $key;
            }
        }
        
        return [
            'configured' => empty($missing),
            'missing' => $missing
        ];
    }
    
    /**
     * Get a user-friendly message about SMTP configuration status
     * 
     * @return string
     */
    public function getSmtpStatusMessage(): string
    {
        $config = $this->checkSmtpConfiguration();
        
        if ($config['configured']) {
            return 'Email notifications are enabled and configured.';
        }
        
        return 'Email notifications are disabled. Missing configuration: ' . implode(', ', $config['missing']) . '. Please configure email settings in the admin panel.';
    }
}