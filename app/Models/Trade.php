<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trade extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function periods(): HasMany
    {
        return $this->hasMany(TradePeriod::class);
    }

    public function userShares()
    {
        return $this->hasMany(UserShare::class);
    }
}
