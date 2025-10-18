<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use App\Models\OnlineUser;

class TrackOnlineUsers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            try {
                // Debug logging
                \Log::info('TrackOnlineUsers: User is authenticated', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'role_id' => $user->role_id,
                    'url' => $request->url()
                ]);
                
                // Store user's last activity in cache for 5 minutes
                $userData = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                    'last_seen' => now()->toISOString(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ];
                
                $cacheKey = 'user_online_' . $user->id;
                
                // Try cache first, fallback to session
                try {
                    Cache::put($cacheKey, $userData, now()->addMinutes(5));
                    
                    // Verify the cache was stored immediately
                    $cacheTest = Cache::has($cacheKey);
                    $cacheData = Cache::get($cacheKey);
                    
                    \Log::info('TrackOnlineUsers: Cache verification', [
                        'cache_key' => $cacheKey,
                        'cache_has' => $cacheTest,
                        'cache_data_exists' => !empty($cacheData),
                        'cache_driver' => config('cache.default', 'unknown')
                    ]);
                    
                    // Update the keys list with proper validation
                    $keys = Cache::get('online_users_keys', []);
                    
                    if (!in_array($cacheKey, $keys)) {
                        $keys[] = $cacheKey;
                    }
                    
                    // Instead of checking Cache::has (which seems unreliable), 
                    // let's just add the current key and let expiration handle cleanup
                    $validKeys = array_unique($keys);
                    
                    Cache::put('online_users_keys', $validKeys, now()->addMinutes(10));
                    
                    \Log::info('TrackOnlineUsers: User data cached successfully', [
                        'cache_key' => $cacheKey,
                        'total_keys' => $keys,
                        'valid_keys' => $validKeys,
                        'keys_count' => count($validKeys)
                    ]);
                } catch (\Exception $cacheException) {
                    \Log::warning('TrackOnlineUsers: Cache failed, using session and database fallback', [
                        'error' => $cacheException->getMessage()
                    ]);
                    
                    // Fallback to session storage
                    Session::put($cacheKey, $userData);
                    $sessionKeys = Session::get('online_users_session_keys', []);
                    if (!in_array($cacheKey, $sessionKeys)) {
                        $sessionKeys[] = $cacheKey;
                        Session::put('online_users_session_keys', $sessionKeys);
                    }
                }
                
                // Always store in database as reliable fallback
                try {
                    OnlineUser::updateOrCreateOnlineUser($userData);
                    \Log::info('TrackOnlineUsers: User data stored in database', [
                        'user_id' => $user->id,
                        'username' => $user->username
                    ]);
                } catch (\Exception $dbException) {
                    \Log::error('TrackOnlineUsers: Database storage failed', [
                        'error' => $dbException->getMessage(),
                        'user_id' => $user->id
                    ]);
                }
                
                // Also store in session as backup
                Session::put('current_user_online', $userData);
                
            } catch (\Exception $e) {
                \Log::error('TrackOnlineUsers: Error tracking user', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id
                ]);
            }
        } else {
            \Log::info('TrackOnlineUsers: User not authenticated', ['url' => $request->url()]);
        }
        
        return $next($request);
    }
    
    /**
     * Get list of currently online users
     */
    private function getOnlineUsersList()
    {
        $onlineUsers = [];
        $cacheKeys = Cache::get('online_users_keys', []);
        
        // Clean up expired keys and collect valid users
        $validKeys = [];
        foreach ($cacheKeys as $key) {
            if (Cache::has($key)) {
                $userData = Cache::get($key);
                $onlineUsers[] = $userData;
                $validKeys[] = $key;
            }
        }
        
        // Add current user's key if authenticated
        if (Auth::check()) {
            $currentUserKey = 'user_online_' . Auth::id();
            if (!in_array($currentUserKey, $validKeys)) {
                $validKeys[] = $currentUserKey;
            }
        }
        
        // Update the keys list
        Cache::put('online_users_keys', $validKeys, now()->addMinutes(10));
        
        return collect($onlineUsers)->unique('id')->values()->all();
    }
}