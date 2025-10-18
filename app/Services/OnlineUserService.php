<?php

namespace App\Services;

use App\Models\User;
use App\Models\OnlineUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class OnlineUserService
{
    /**
     * Get all currently online users
     */
    public static function getOnlineUsers()
    {
        $onlineUsers = [];
        
        try {
            // Primary: Try database first (most reliable)
            $dbUsers = OnlineUser::getOnlineUsers();
            
            if ($dbUsers->count() > 0) {
                foreach ($dbUsers as $dbUser) {
                    $onlineUsers[] = [
                        'id' => $dbUser->user_id,
                        'name' => $dbUser->name,
                        'username' => $dbUser->username,
                        'avatar' => $dbUser->avatar,
                        'email' => $dbUser->email,
                        'last_seen' => $dbUser->last_seen,
                        'ip_address' => $dbUser->ip_address,
                        'user_agent' => $dbUser->user_agent,
                    ];
                }
                
                // Clean up old records while we're here
                OnlineUser::cleanupOfflineUsers();
                
            } else {
                // Fallback: Try cache
                $keys = Cache::get('online_users_keys', []);
                
                foreach ($keys as $key) {
                    if (Cache::has($key)) {
                        $userData = Cache::get($key);
                        
                        if ($userData && isset($userData['id'])) {
                            // Only include users with role_id = 2 (regular users)
                            $user = User::find($userData['id']);
                            if ($user && $user->role_id == 2) {
                                $onlineUsers[] = $userData;
                            }
                        }
                    }
                }
                
                // Final fallback: Try session
                if (empty($onlineUsers)) {
                    if (Session::has('current_user_online')) {
                        $currentUserData = Session::get('current_user_online');
                        if ($currentUserData && isset($currentUserData['id'])) {
                            $user = User::find($currentUserData['id']);
                            if ($user && $user->role_id == 2) {
                                $onlineUsers[] = $currentUserData;
                            }
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('OnlineUserService: Error getting online users', [
                'error' => $e->getMessage()
            ]);
        }
        
        return collect($onlineUsers)->unique('id')->sortBy('name')->values();
    }
    
    /**
     * Get count of online users
     */
    public static function getOnlineUsersCount()
    {
        return self::getOnlineUsers()->count();
    }
    
    /**
     * Check if a specific user is online
     */
    public static function isUserOnline($userId)
    {
        return Cache::has('user_online_' . $userId);
    }
    
    /**
     * Get user's last seen time
     */
    public static function getUserLastSeen($userId)
    {
        $userData = Cache::get('user_online_' . $userId);
        return $userData ? $userData['last_seen'] : null;
    }
    
    /**
     * Remove user from online list (for logout)
     */
    public static function removeUserFromOnline($userId)
    {
        $key = 'user_online_' . $userId;
        Cache::forget($key);
        
        // Update the keys list
        $keys = Cache::get('online_users_keys', []);
        $keys = array_diff($keys, [$key]);
        Cache::put('online_users_keys', array_values($keys), now()->addMinutes(10));
    }
    
    /**
     * Clean up expired users
     */
    public static function cleanupExpiredUsers()
    {
        $keys = Cache::get('online_users_keys', []);
        $validKeys = [];
        
        foreach ($keys as $key) {
            if (Cache::has($key)) {
                $validKeys[] = $key;
            }
        }
        
        Cache::put('online_users_keys', $validKeys, now()->addMinutes(10));
    }
}