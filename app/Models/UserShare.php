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
    
    public function tradePeriod(): BelongsTo
    {
        return $this->belongsTo(TradePeriod::class ,'period', 'days');
    }

    public function pairedShares(): HasMany
    {
        return $this->hasMany(UserSharePair::class);
    }
   public function pairedWithThis(): BelongsTo
   {
       return $this->belongsTo(UserSharePair::class,  'id', 'paired_user_share_id');
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
