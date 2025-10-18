<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OnlineUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username', 
        'name',
        'email',
        'avatar',
        'role_id',
        'last_seen',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'last_seen' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get online users (active within last 15 minutes)
     */
    public static function getOnlineUsers()
    {
        return static::where('last_seen', '>=', Carbon::now()->subMinutes(15))
                    ->where('role_id', 2) // Only regular users
                    ->orderBy('last_seen', 'desc')
                    ->get();
    }

    /**
     * Update or create online user record
     */
    public static function updateOrCreateOnlineUser($userData)
    {
        return static::updateOrCreate(
            ['user_id' => $userData['id']],
            [
                'username' => $userData['username'],
                'name' => $userData['name'], 
                'email' => $userData['email'],
                'avatar' => $userData['avatar'],
                'role_id' => $userData['role_id'] ?? 2,
                'last_seen' => $userData['last_seen'],
                'ip_address' => $userData['ip_address'],
                'user_agent' => $userData['user_agent']
            ]
        );
    }

    /**
     * Remove offline users (older than 15 minutes)
     */
    public static function cleanupOfflineUsers()
    {
        return static::where('last_seen', '<', Carbon::now()->subMinutes(15))->delete();
    }
}