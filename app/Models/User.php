<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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
        'username',
        'phone',
        'refferal_code',
        'business_profile',
        'trading_category_id',
        'business_account_id',
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

    public function trading_category()
    {
        return $this->belongsTo(TradingCategory::class);
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
        return $this->where('status', 'fine');
    }
}
