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
        'suspension_reason',
        'status',
        'referral_bonus_at_registration',
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

    /**
     * Get business profile data safely
     * Returns array with empty values if profile is null or invalid JSON
     */
    public function getBusinessProfileData()
    {
        $defaultProfile = [
            'mpesa_no' => '',
            'mpesa_name' => '',
            'mpesa_till_no' => '',
            'mpesa_till_name' => ''
        ];

        if (empty($this->business_profile)) {
            return $defaultProfile;
        }

        try {
            $profile = json_decode($this->business_profile, true);
            if (!is_array($profile)) {
                return $defaultProfile;
            }
            return array_merge($defaultProfile, $profile);
        } catch (\Exception $e) {
            return $defaultProfile;
        }
    }

    public function trade()
    {
        return $this->belongsTo(Trade::class);
    }

    public function tradingCategory()
    {
        return $this->belongsTo(Trade::class, 'trading_category_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function scopeActive()
    {
        return $this->where('status', 'active');
    }
    public function scopeBlocked()
    {
        return $this->where('status', 'blocked');
    }
    public function scopeSuspended()
    {
        return $this->where('status', 'suspended');
    }
    public function scopeValidForTrading()
    {
        return $this->whereIn('status', ['active']);
    }

    /**
     * Check if the user is currently suspended
     */
    public function isSuspended()
    {
        return $this->status === 'suspended' && 
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
        if ($this->status === 'suspended' && 
            $this->suspension_until && 
            $this->suspension_until->isPast()) {
            $this->update([
                'status' => 'active',
                'suspension_until' => null
            ]);
            return true;
        }
        return false;
    }

    /**
     * Suspend user for payment failures with escalating duration
     * 1st: 6 hours, 2nd: 24 hours, 3rd+: 72 hours
     */
    public function suspendForPaymentFailures()
    {
        $paymentFailure = $this->getCurrentPaymentFailure();
        $durationHours = $paymentFailure->markSuspendedWithLevel();
        $suspensionUntil = now()->addHours($durationHours);
        
        $this->update([
            'status' => 'suspended',
            'suspension_until' => $suspensionUntil,
            'suspension_reason' => 'payment_failure'
        ]);

        // Suspend all active trades and shares
        $this->suspendAllActiveTrades();

        // Clear all sessions for this user to force logout from all devices
        $this->clearAllUserSessions();

        // Log the suspension with level info
        \Log::info('User suspended for payment failures: ' . $this->username . 
                  ' (Level ' . $paymentFailure->suspension_level . ', Duration: ' . $durationHours . 'h) until ' . $suspensionUntil);

        return $suspensionUntil;
    }

    /**
     * Lift suspension and resume shares
     */
    public function liftSuspension()
    {
        $this->update([
            'status' => 'active',
            'suspension_until' => null,
            'suspension_reason' => null
        ]);

        // Resume all suspended trades and shares
        $this->resumeAllSuspendedTrades();

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
     * Suspend all active trades and shares for user
     * Includes: running, paired, partially paired, pending, and active statuses
     */
    public function suspendAllActiveTrades()
    {
        $suspendedCount = 0;
        
        // Get all shares that need to be suspended
        $activeShares = $this->shares()
            ->whereIn('status', ['running', 'paired', 'partially_paired', 'pending', 'active'])
            ->orWhere(function($query) {
                // Also suspend completed shares that are ready to sell (running timer)
                $query->where('status', 'completed')
                      ->where('is_ready_to_sell', 0)
                      ->whereNotNull('start_date')
                      ->where('timer_paused', false);
            })
            ->get();

        foreach ($activeShares as $share) {
            // Pause the timer for running shares
            if ($share->start_date && !$share->timer_paused) {
                $share->update([
                    'timer_paused' => true,
                    'timer_paused_at' => now()
                ]);
            }
            
            // For shares in pairing process, mark them as suspended
            if (in_array($share->status, ['running', 'paired', 'partially_paired', 'pending', 'active'])) {
                // Store original status to restore later
                $share->update([
                    'status_before_suspension' => $share->status,
                    'status' => 'suspended_by_system'
                ]);
            }
            
            $suspendedCount++;
        }
        
        // Log suspension of trades
        \Log::info('Suspended ' . $suspendedCount . ' active trades/shares for user: ' . $this->username);
        
        return $suspendedCount;
    }

    /**
     * Resume all suspended trades and shares for user
     */
    public function resumeAllSuspendedTrades()
    {
        $resumedCount = 0;
        
        // Resume paused timers
        $this->resumePausedShares();
        
        // Resume suspended trades
        $suspendedShares = $this->shares()
            ->where('status', 'suspended_by_system')
            ->get();

        foreach ($suspendedShares as $share) {
            // Restore original status
            $originalStatus = $share->status_before_suspension ?? 'running';
            $share->update([
                'status' => $originalStatus,
                'status_before_suspension' => null
            ]);
            
            $resumedCount++;
        }
        
        \Log::info('Resumed ' . $resumedCount . ' suspended trades/shares for user: ' . $this->username);
        
        return $resumedCount;
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
        
        return asset('assets/images/users/default.jpg');
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
