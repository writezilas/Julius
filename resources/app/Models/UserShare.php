<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserShare extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    public function pairedShares(): HasMany
    {
        return $this->hasMany(UserSharePair::class);
    }
//    public function pairedWithThis(): HasMany
//    {
//        return $this->hasMany(UserSharePair::class, 'paired_user_share_id', 'id');
//    }
    public function payments(): HasMany
    {
        return $this->hasMany(UserSharePayment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->morphMany(Log::class, 'logable');
    }
}
