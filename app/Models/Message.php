<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'type',
        'file_path',
        'file_name',
        'is_system_message'
    ];

    protected $casts = [
        'is_system_message' => 'boolean'
    ];

    // Relationships
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function messageReads(): HasMany
    {
        return $this->hasMany(MessageRead::class);
    }

    // Business Logic Methods
    public function isReadBy($userId)
    {
        return $this->messageReads()->where('user_id', $userId)->exists();
    }

    public function markAsReadBy($userId)
    {
        return $this->messageReads()->firstOrCreate([
            'user_id' => $userId
        ], [
            'read_at' => now()
        ]);
    }

    public function getUnreadCount($userId)
    {
        return $this->where('sender_id', '!=', $userId)
            ->whereDoesntHave('messageReads', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->count();
    }

    public function isFileMessage()
    {
        return in_array($this->type, ['file', 'image']);
    }

    public function getFileUrl()
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }
}
