<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Http\Controllers\ChatController;

class UserShare extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    protected $fillable = [
        'trade_id', 'user_id', 'reff_id', 'ticket_no', 'amount', 'balance', 
        'period', 'share_will_get', 'total_share_count', 'sold_quantity', 'hold_quantity',
        'status', 'status_before_suspension', 'get_from', 'is_ready_to_sell', 'is_sold', 'start_date', 'matured_at',
        // Legacy timer fields (kept for backward compatibility)
        'timer_paused', 'timer_paused_at', 'paused_duration_seconds', 'payment_deadline_minutes',
        // Separate timer system
        'selling_started_at', 'selling_timer_paused', 'selling_timer_paused_at', 'selling_paused_duration_seconds',
        'payment_timer_paused', 'payment_timer_paused_at', 'payment_paused_duration_seconds',
        'profit_share'
    ];
    
    // Model Events
    protected static function booted()
    {
        // End conversations when share status changes to 'completed'
        static::updated(function ($userShare) {
            if ($userShare->isDirty('status') && $userShare->status === 'completed') {
                try {
                    ChatController::endConversationsForCompletedShares($userShare->id);
                } catch (\Exception $e) {
                    \Log::error('Error ending conversations for completed share: ' . $e->getMessage());
                }
            }
        });
    }

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }
    
    public function tradePeriod(): BelongsTo
    {
        return $this->belongsTo(TradePeriod::class ,'period', 'days');
    }

    public function pairedShares(): HasMany
    {
        return $this->hasMany(UserSharePair::class);
    }
   public function pairedWithThis(): HasMany
   {
       return $this->hasMany(UserSharePair::class, 'paired_user_share_id', 'id');
   }
    public function payments(): HasMany
    {
        return $this->hasMany(UserSharePayment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function refferal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reff_id');
    }

    public function logs()
    {
        return $this->morphMany(Log::class, 'logable');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'share_id', 'id');
    }
}
