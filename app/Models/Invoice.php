<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    public $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userShare()
    {
        return $this->belongsTo(UserShare::class, 'share_id', 'id');
    }

    public function referral()
    {
        return $this->belongsTo(User::class, 'reff_user_id', 'id');
    }
}
