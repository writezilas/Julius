<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSharePair extends Model
{
    use HasFactory;
    public function pairedShare(): BelongsTo
    {
        return $this->belongsTo(UserShare::class, 'paired_user_share_id', 'id');
    }

    public function pairedUserShare(): BelongsTo
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


