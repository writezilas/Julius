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
        'last_failure_at',
        'suspended_at',
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
     */
    public function shouldSuspend(): bool
    {
        return $this->consecutive_failures >= 3 && !$this->suspended_at;
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
}
