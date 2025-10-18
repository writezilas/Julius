<?php

namespace App\Services;

use App\Models\UserShare;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ShareAvailabilityCache
{
    protected $cachePrefix = 'shares_available_';
    protected $cacheTime = 30; // Cache for 30 seconds to balance performance and accuracy
    
    /**
     * Get available shares count with caching
     */
    public function getAvailableShares($tradeId)
    {
        $cacheKey = $this->cachePrefix . $tradeId;
        
        // Try to get from cache first
        $cachedResult = Cache::get($cacheKey);
        if ($cachedResult !== null) {
            Log::debug("Using cached share availability", [
                'trade_id' => $tradeId,
                'cached_count' => $cachedResult,
                'cache_key' => $cacheKey
            ]);
            return $cachedResult;
        }
        
        // If not in cache, calculate and cache
        $count = $this->calculateAvailableShares($tradeId);
        Cache::put($cacheKey, $count, $this->cacheTime);
        
        Log::debug("Calculated and cached share availability", [
            'trade_id' => $tradeId,
            'calculated_count' => $count,
            'cache_key' => $cacheKey,
            'cache_time' => $this->cacheTime
        ]);
        
        return $count;
    }
    
    /**
     * Calculate available shares from database
     */
    protected function calculateAvailableShares($tradeId)
    {
        $query = UserShare::whereTradeId($tradeId)
            ->whereStatus('completed')
            ->where('is_ready_to_sell', 1)
            ->where('total_share_count', '>', 0);
        
        // Exclude current user if authenticated
        if (auth()->check()) {
            $query->where('user_id', '!=', auth()->user()->id);
        }
        
        // Check for users with active status (not suspended/banned)
        $query->whereHas('user', function ($subQuery) {
            $subQuery->whereIn('status', ['active', 'pending', 'fine']);
        });
        
        return $query->sum('total_share_count');
    }
    
    /**
     * Clear cache for a specific trade
     */
    public function clearCache($tradeId)
    {
        $cacheKey = $this->cachePrefix . $tradeId;
        Cache::forget($cacheKey);
        
        Log::debug("Cleared share availability cache", [
            'trade_id' => $tradeId,
            'cache_key' => $cacheKey
        ]);
    }
    
    /**
     * Clear all share availability caches
     */
    public function clearAllCaches()
    {
        // Get all cache keys with our prefix
        $keys = Cache::get('share_cache_keys', []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        Cache::forget('share_cache_keys');
        
        Log::debug("Cleared all share availability caches");
    }
    
    /**
     * Refresh cache for a trade (used after share status changes)
     */
    public function refreshCache($tradeId)
    {
        $this->clearCache($tradeId);
        return $this->getAvailableShares($tradeId);
    }
}