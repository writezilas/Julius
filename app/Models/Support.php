<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Support extends Model
{
    use HasFactory;

    public $guarded = ['_token'];
    
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'number',
        'username',
        'message',
        'admin_reply',
        'replied_at',
        'replied_by',
        'admin_notified',
        'status'
    ];
    
    protected $casts = [
        'replied_at' => 'datetime',
        'admin_notified' => 'boolean',
    ];

    protected function name(): Attribute
    {
        return new Attribute(
            get: fn () => $this->first_name . ' '. $this->last_name,
        );
    }
    
    /**
     * Get the user who submitted the support request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the admin who replied to the support request
     */
    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }
    
    /**
     * Check if the support request has been replied to
     */
    public function hasReply(): bool
    {
        return !is_null($this->admin_reply);
    }
    
    /**
     * Check if the support request is pending (no reply yet)
     */
    public function isPending(): bool
    {
        return is_null($this->admin_reply) && $this->status === 0;
    }
}
