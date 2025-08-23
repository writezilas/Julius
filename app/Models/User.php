<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasPermissions;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role_id',
        'username',
        'phone',
        'refferal_code',
        'business_profile',
        'trading_category_id',
        'business_account_id',
        'block_until',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function trade()
    {
        return $this->belongsTo(Trade::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function scopePending()
    {
        return $this->where('status', 'pending');
    }
    public function scopeBlock()
    {
        return $this->where('status', 'block');
    }
    public function scopeSuspend()
    {
        return $this->where('status', 'suspend');
    }
    public function scopeFine()
    {
        return $this->whereIn('status', ['fine', 'pending']);
    }

    protected function avatar(): Attribute
    {
        return new Attribute(
            get: fn($value) => $value ? (explode('/', $value)[0] === "uploads" ? 'storage/'.$value : $value) : $value,
        );
    }

    /**
     * Get the avatar URL with fallback to default image
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset($this->avatar);
        }
        
        return asset('images/default.jpg');
    }

    public function logs()
    {
        return $this->morphMany(Log::class, 'logable');
    }

    public function alllogs(): HasMany
    {
        return $this->hasMany(Log::class);
    }
    
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'refferal_code', 'username');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(UserShare::class);
    }

    public function refferalBy()
    {
        return $this->belongsTo(User::class, 'refferal_code', 'username');
    }
}
