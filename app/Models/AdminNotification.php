<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Helpers\SettingHelper;

class AdminNotification extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title',
        'message',
        'type',
        'data',
        'is_read',
        'read_at'
    ];
    
    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Create a new admin notification
     */
    public static function create($title, $message, $type = 'info', $data = null)
    {
        return self::query()->create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data
        ]);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => Carbon::now()
        ]);
        
        return $this;
    }
    
    /**
     * Get unread notifications
     */
    public static function unread()
    {
        return self::where('is_read', false)
                   ->orderBy('created_at', 'desc');
    }
    
    /**
     * Get recent notifications
     */
    public static function recent($limit = 10)
    {
        return self::orderBy('created_at', 'desc')
                   ->limit($limit);
    }
    
    /**
     * Get unread count
     */
    public static function unreadCount()
    {
        return self::where('is_read', false)->count();
    }
    
    /**
     * Create notification for new user registration
     */
    public static function newUserSignup($user)
    {
        $data = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_username' => $user->username,
            'referral_code' => $user->refferal_code ?? null,
            'signup_time' => $user->created_at->toISOString()
        ];
        
        return self::create(
            'New User Registration',
            "New user '{$user->name}' ({$user->username}) has signed up to the platform" . 
            ($user->refferal_code ? " using referral code: {$user->refferal_code}" : ''),
            'success',
            $data
        );
    }
    
    /**
     * Create notification for new share purchase
     */
    public static function newSharePurchase($user, $trade, $amount, $sharesCount, $ticketNo)
    {
        $data = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_username' => $user->username,
            'trade_id' => $trade->id,
            'trade_name' => $trade->name,
            'purchase_amount' => $amount,
            'shares_purchased' => $sharesCount,
            'ticket_number' => $ticketNo,
            'purchase_time' => now()->toISOString(),
            'trade_price_per_share' => $trade->price ?? $trade->amount ?? 0
        ];
        
        return self::create(
            'New Share Purchase',
            "User '{$user->name}' ({$user->username}) purchased {$sharesCount} shares of '{$trade->name}' for KSH " . number_format($amount, 2) . " (Ticket: {$ticketNo})",
            'info',
            $data
        );
    }
    
    /**
     * Create notification for new support request
     */
    public static function newSupportRequest($support, $user = null)
    {
        // If user is not passed, try to get it from the support record
        if (!$user && $support->user_id) {
            $user = \App\Models\User::find($support->user_id);
        }
        
        $data = [
            'support_id' => $support->id,
            'support_first_name' => $support->first_name,
            'support_last_name' => $support->last_name,
            'support_email' => $support->email,
            'support_phone' => $support->number,
            'support_username' => $support->username,
            'support_message_preview' => substr($support->message, 0, 100) . (strlen($support->message) > 100 ? '...' : ''),
            'support_full_message' => $support->message,
            'support_created_at' => $support->created_at->toISOString(),
            'user_id' => $user->id ?? null,
            'user_name' => $user->name ?? null,
            'user_username' => $user->username ?? null,
            'view_url' => route('admin.support') . '#support-' . $support->id
        ];
        
        $userName = $user ? $user->name . " ({$user->username})" : $support->first_name . ' ' . $support->last_name;
        $messagePreview = substr($support->message, 0, 80) . (strlen($support->message) > 80 ? '...' : '');
        
        return self::create(
            'New Support Request',
            "New support request from {$userName}: \"{$messagePreview}\"",
            'warning',
            $data
        );
    }
    
    /**
     * Get admin email for notifications
     * This demonstrates how to use the admin email setting
     */
    public static function getAdminEmail()
    {
        return SettingHelper::getAdminEmail('admin@autobidder.live');
    }
    
    /**
     * Send critical notification to admin email
     * Example usage of admin email setting
     */
    public static function sendCriticalAlert($title, $message, $data = null)
    {
        // Create the notification record
        $notification = self::create($title, $message, 'critical', $data);
        
        // Here you could send an email to the admin using the admin email setting
        // Example:
        // Mail::to(self::getAdminEmail())->send(new CriticalAlert($notification));
        
        return $notification;
    }
}
