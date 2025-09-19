<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPaymentFailure extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'consecutive_failures',
        'suspension_level',
        'last_failure_at',
        'suspended_at',
        'suspension_duration_hours',
        'suspension_lifted_at',
        'failure_reason',
    ];

    protected $casts = [
        'last_failure_at' => 'datetime',
        'suspended_at' => 'datetime',
        'suspension_lifted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if user should be suspended based on consecutive failures
     * Allow re-suspension if previous suspension has been lifted (suspended_at is old)
     */
    public function shouldSuspend(): bool
    {
        // User should be suspended if they have 3+ consecutive failures
        if ($this->consecutive_failures < 3) {
            return false;
        }
        
        // If never suspended before, suspend now
        if (!$this->suspended_at) {
            return true;
        }
        
        // If suspended before but suspension was lifted, allow re-suspension
        if ($this->suspended_at && $this->suspension_lifted_at) {
            return true;
        }
        
        // If suspended but suspension has expired, allow re-suspension
        $user = $this->user;
        if ($user && $user->suspension_until && $user->suspension_until->isPast()) {
            return true;
        }
        
        return false;
    }

    /**
     * Reset failure count (called when payment succeeds)
     */
    public function resetFailures(): void
    {
        $this->update([
            'consecutive_failures' => 0,
            'last_failure_at' => null,
            'failure_reason' => null,
        ]);
    }

    /**
     * Increment failure count
     */
    public function incrementFailures(string $reason = null): void
    {
        $this->update([
            'consecutive_failures' => $this->consecutive_failures + 1,
            'last_failure_at' => now(),
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Mark as suspended
     */
    public function markSuspended(): void
    {
        $this->update([
            'suspended_at' => now(),
            'suspension_lifted_at' => null,
        ]);
    }

    /**
     * Mark suspension as lifted
     */
    public function liftSuspension(): void
    {
        $this->update([
            'suspension_lifted_at' => now(),
        ]);
    }

    /**
     * Get suspension duration based on current suspension level
     * 1st time: 6 hours, 2nd time: 24 hours, 3rd+ time: 72 hours
     */
    public function getSuspensionDuration(): int
    {
        $nextLevel = $this->suspension_level + 1;
        
        return match($nextLevel) {
            1 => 6,   // First suspension: 6 hours
            2 => 24,  // Second suspension: 24 hours
            default => 72  // Third and subsequent suspensions: 72 hours
        };
    }

    /**
     * Mark as suspended with escalating duration
     */
    public function markSuspendedWithLevel(): int
    {
        // Increment suspension level
        $newLevel = $this->suspension_level + 1;
        $durationHours = $this->getSuspensionDuration();
        
        $this->update([
            'suspension_level' => $newLevel,
            'suspended_at' => now(),
            'suspension_duration_hours' => $durationHours,
            'suspension_lifted_at' => null,
        ]);
        
        return $durationHours;
    }

    /**
     * Reset suspension level (called after successful payment period)
     */
    public function resetSuspensionLevel(): void
    {
        $this->update([
            'suspension_level' => 0,
        ]);
    }

    /**
     * Check if user should have suspension level reset based on payment history
     * Reset if user has made successful payments for a period without failures
     */
    public function shouldResetSuspensionLevel(): bool
    {
        // If user has been suspension-free and failure-free for 30 days, reset level
        return $this->suspension_level > 0 && 
               $this->consecutive_failures === 0 &&
               (!$this->last_failure_at || $this->last_failure_at->diffInDays(now()) >= 30);
    }
}
