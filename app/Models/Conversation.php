<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_share_id',
        'seller_share_id', 
        'user_share_pair_id',
        'status',
        'ended_at'
    ];

    protected $casts = [
        'ended_at' => 'datetime'
    ];

    // Relationships
    public function buyerShare(): BelongsTo
    {
        return $this->belongsTo(UserShare::class, 'buyer_share_id');
    }

    public function sellerShare(): BelongsTo
    {
        return $this->belongsTo(UserShare::class, 'seller_share_id');
    }

    public function userSharePair(): BelongsTo
    {
        return $this->belongsTo(UserSharePair::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    // Business Logic Methods
    public function getBuyer()
    {
        return $this->buyerShare->user;
    }

    public function getSeller()
    {
        return $this->sellerShare->user;
    }

    public function getOtherParticipant($userId)
    {
        $buyer = $this->getBuyer();
        $seller = $this->getSeller();
        
        if ($buyer->id === $userId) {
            return $seller;
        } elseif ($seller->id === $userId) {
            return $buyer;
        }
        
        return null;
    }

    public function canUserAccess($userId)
    {
        $buyer = $this->getBuyer();
        $seller = $this->getSeller();
        
        return $buyer->id === $userId || $seller->id === $userId;
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function endConversation()
    {
        $this->update([
            'status' => 'ended',
            'ended_at' => now()
        ]);

        // Add system message
        $this->messages()->create([
            'sender_id' => 1, // System user
            'message' => 'This conversation has ended because the trade was completed.',
            'is_system_message' => true
        ]);
    }

    /**
     * Create conversation when shares are paired
     */
    public static function createForPairedShares(UserSharePair $sharePair)
    {
        // Get the buyer and seller shares
        $buyerShare = $sharePair->pairedUserShare; // The one buying
        $sellerShare = $sharePair->pairedShare; // The one selling (paired with)

        // Create conversation
        $conversation = self::create([
            'buyer_share_id' => $buyerShare->id,
            'seller_share_id' => $sellerShare->id,
            'user_share_pair_id' => $sharePair->id,
            'status' => 'active'
        ]);

        // Add welcome system message
        $conversation->messages()->create([
            'sender_id' => 1, // System user
            'message' => 'Chat started! You can now communicate about your trade.',
            'is_system_message' => true
        ]);

        return $conversation;
    }

    /**
     * Get conversations for a user based on their paired shares
     */
    public static function forUser($userId)
    {
        return self::whereHas('buyerShare', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->orWhereHas('sellerShare', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['buyerShare.user', 'sellerShare.user', 'messages' => function ($query) {
            $query->latest()->limit(1);
        }])->orderBy('updated_at', 'desc');
    }

    /**
     * Check if both shares are completed and end conversation
     */
    public function checkAndEndIfCompleted()
    {
        if ($this->buyerShare->status === 'completed' && $this->sellerShare->status === 'completed') {
            $this->endConversation();
            return true;
        }
        return false;
    }
}
