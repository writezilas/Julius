<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasPermissions;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role_id',
        'username',
        'phone',
        'refferal_code',
        'business_profile',
        'trading_category_id',
        'business_account_id',
        'block_until',
        'suspension_until',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'block_until' => 'datetime',
        'suspension_until' => 'datetime',
    ];

    public function trade()
    {
        return $this->belongsTo(Trade::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function scopePending()
    {
        return $this->where('status', 'pending');
    }
    public function scopeBlock()
    {
        return $this->where('status', 'block');
    }
    public function scopeSuspend()
    {
        return $this->where('status', 'suspend');
    }
    public function scopeFine()
    {
        return $this->whereIn('status', ['fine', 'pending']);
    }

    /**
     * Check if the user is currently suspended
     */
    public function isSuspended()
    {
        return $this->status === 'suspend' && 
               $this->suspension_until && 
               $this->suspension_until->isFuture();
    }

    /**
     * Get the remaining suspension time in seconds
     */
    public function getSuspensionRemainingSeconds()
    {
        if (!$this->isSuspended()) {
            return 0;
        }
        
        return $this->suspension_until->diffInSeconds(now());
    }

    /**
     * Check if suspension has expired and update status
     */
    public function checkSuspensionExpiry()
    {
        if ($this->status === 'suspend' && 
            $this->suspension_until && 
            $this->suspension_until->isPast()) {
            $this->update([
                'status' => 'fine',
                'suspension_until' => null
            ]);
            return true;
        }
        return false;
    }

    /**
     * Suspend user for payment failures (12 hours)
     */
    public function suspendForPaymentFailures()
    {
        $suspensionUntil = now()->addHours(12);
        
        $this->update([
            'status' => 'suspend',
            'suspension_until' => $suspensionUntil
        ]);

        // Pause all running sold shares
        $this->pauseRunningShares();

        // Clear all sessions for this user to force logout from all devices
        $this->clearAllUserSessions();

        // Log the suspension
        \Log::info('User suspended for payment failures: ' . $this->username . ' until ' . $suspensionUntil);

        return $suspensionUntil;
    }

    /**
     * Lift suspension and resume shares
     */
    public function liftSuspension()
    {
        $this->update([
            'status' => 'fine',
            'suspension_until' => null
        ]);

        // Resume all paused shares
        $this->resumePausedShares();

        // Mark suspension as lifted in payment failures
        $paymentFailure = $this->paymentFailures()->latest()->first();
        if ($paymentFailure) {
            $paymentFailure->liftSuspension();
        }

        \Log::info('User suspension lifted: ' . $this->username);
    }

    /**
     * Pause all running sold shares for suspended user
     */
    public function pauseRunningShares()
    {
        $runningShares = $this->shares()
            ->where('status', 'completed')
            ->where('is_ready_to_sell', 0)
            ->whereNotNull('start_date')
            ->where('timer_paused', false)
            ->get();

        foreach ($runningShares as $share) {
            $share->update([
                'timer_paused' => true,
                'timer_paused_at' => now()
            ]);
        }

        \Log::info('Paused ' . $runningShares->count() . ' running shares for user: ' . $this->username);
        return $runningShares->count();
    }

    /**
     * Resume all paused shares for user
     */
    public function resumePausedShares()
    {
        $pausedShares = $this->shares()
            ->where('timer_paused', true)
            ->whereNotNull('timer_paused_at')
            ->get();

        foreach ($pausedShares as $share) {
            // Calculate paused duration
            $pausedDuration = $share->timer_paused_at->diffInSeconds(now());
            
            $share->update([
                'timer_paused' => false,
                'timer_paused_at' => null,
                'paused_duration_seconds' => $share->paused_duration_seconds + $pausedDuration
            ]);
        }

        \Log::info('Resumed ' . $pausedShares->count() . ' paused shares for user: ' . $this->username);
        return $pausedShares->count();
    }

    /**
     * Get current payment failure record
     */
    public function getCurrentPaymentFailure()
    {
        return $this->paymentFailures()->firstOrCreate(
            ['user_id' => $this->id],
            ['consecutive_failures' => 0]
        );
    }

    /**
     * Clear all sessions for this user to force logout from all devices
     */
    public function clearAllUserSessions()
    {
        try {
            // Clear all sessions for this user from the sessions table
            \DB::table('sessions')
                ->where('user_id', $this->id)
                ->delete();
                
            \Log::info("All active sessions cleared for user {$this->username} due to suspension");
            
        } catch (\Exception $e) {
            \Log::error("Error clearing sessions for user {$this->username}: " . $e->getMessage());
        }
    }

    protected function avatar(): Attribute
    {
        return new Attribute(
            get: fn($value) => $value ? (explode('/', $value)[0] === "uploads" ? 'storage/'.$value : $value) : $value,
        );
    }

    /**
     * Get the avatar URL with fallback to default image
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset($this->avatar);
        }
        
        return asset('images/default.jpg');
    }

    public function logs()
    {
        return $this->morphMany(Log::class, 'logable');
    }

    public function alllogs(): HasMany
    {
        return $this->hasMany(Log::class);
    }
    
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'refferal_code', 'username');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(UserShare::class);
    }

    public function paymentFailures(): HasMany
    {
        return $this->hasMany(UserPaymentFailure::class);
    }

    public function refferalBy()
    {
        return $this->belongsTo(User::class, 'refferal_code', 'username');
    }

    // Chat System Relationships
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function messageReads(): HasMany
    {
        return $this->hasMany(MessageRead::class);
    }

    /**
     * Get conversations where user is either buyer or seller
     */
    public function conversations()
    {
        return Conversation::forUser($this->id);
    }

    /**
     * Check if user can chat with another user based on paired shares
     */
    public function canChatWith($otherUserId)
    {
        // Check if there's an active conversation between users
        return Conversation::forUser($this->id)
            ->where('status', 'active')
            ->where(function ($query) use ($otherUserId) {
                $query->whereHas('buyerShare', function ($q) use ($otherUserId) {
                    $q->where('user_id', $otherUserId);
                })->orWhereHas('sellerShare', function ($q) use ($otherUserId) {
                    $q->where('user_id', $otherUserId);
                });
            })->exists();
    }

    /**
     * Get unread messages count for user
     */
    public function getUnreadMessagesCount()
    {
        return Message::whereHas('conversation', function ($query) {
            $query->forUser($this->id)->where('status', 'active');
        })->where('sender_id', '!=', $this->id)
        ->whereDoesntHave('messageReads', function ($query) {
            $query->where('user_id', $this->id);
        })->count();
    }
}
