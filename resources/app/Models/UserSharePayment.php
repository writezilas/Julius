<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSharePayment extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function paired()
    {
        return $this->belongsTo(UserSharePair::class, 'user_share_pair_id', 'id');
    }
    public function logs()
    {
        return $this->morphMany(Log::class, 'logable');
    }

}
