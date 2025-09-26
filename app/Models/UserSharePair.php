<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Http\Controllers\ChatController;

class UserSharePair extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'user_share_id',
        'paired_user_share_id',
        'share',
        'is_paid'
    ];
    
    // Model Events
    protected static function booted()
    {
        // Create conversation when share pair is created
        static::created(function ($sharePair) {
            try {
                // Load relationships
                $sharePair->load(['pairedUserShare', 'pairedShare']);
                
                // Check if both shares have status 'paired'
                if ($sharePair->pairedUserShare && $sharePair->pairedShare &&
                    $sharePair->pairedUserShare->status === 'paired' && 
                    $sharePair->pairedShare->status === 'paired') {
                    
                    ChatController::createConversationForPair($sharePair);
                }
            } catch (\Exception $e) {
                \Log::error('Error creating conversation for share pair: ' . $e->getMessage());
            }
        });
    }
    public function pairedShare(): BelongsTo
    {
        return $this->belongsTo(UserShare::class, 'paired_user_share_id', 'id');
    }

    public function pairedUserShare(): BelongsTo
    {
        return $this->belongsTo(UserShare::class, 'user_share_id', 'id');
    }
    
    public function buyerUserShare(): BelongsTo
    {
        return $this->belongsTo(UserShare::class, 'user_share_id', 'id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(UserSharePayment::class, 'id', 'user_share_pair_id');
    }

    public function userShare(): BelongsTo
    {
        return $this->belongsTo(UserShare::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }
}


