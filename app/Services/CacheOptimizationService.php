<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheOptimizationService
{
    /**
     * Cache durations in seconds
     */
    const CACHE_DURATIONS = [
        'users' => 86400,        // 24 hours
        'trades' => 3600,        // 1 hour
        'user_shares' => 1800,   // 30 minutes
        'market_data' => 300,    // 5 minutes
        'dashboard' => 600,      // 10 minutes
        'statistics' => 900,     // 15 minutes
    ];

    /**
     * Get user dashboard data with caching
     */
    public function getUserDashboardData($userId)
    {
        return Cache::remember(
            "user_dashboard_{$userId}",
            self::CACHE_DURATIONS['dashboard'],
            function () use ($userId) {
                return [
                    'user_shares' => DB::table('user_shares')
                        ->where('user_id', $userId)
                        ->select('status', 'amount', 'balance', 'created_at')
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get(),
                    'total_investment' => DB::table('user_shares')
                        ->where('user_id', $userId)
                        ->sum('amount'),
                    'active_shares' => DB::table('user_shares')
                        ->where('user_id', $userId)
                        ->whereIn('status', ['running', 'paired', 'pairing'])
                        ->count(),
                ];
            }
        );
    }

    /**
     * Get market status with caching
     */
    public function getMarketStatus()
    {
        return Cache::remember(
            'market_status',
            self::CACHE_DURATIONS['market_data'],
            function () {
                return DB::table('markets')
                    ->where('is_active', 1)
                    ->first();
            }
        );
    }

    /**
     * Get trade statistics with caching
     */
    public function getTradeStatistics()
    {
        return Cache::remember(
            'trade_statistics',
            self::CACHE_DURATIONS['statistics'],
            function () {
                return [
                    'total_trades' => DB::table('user_shares')->count(),
                    'active_trades' => DB::table('user_shares')
                        ->whereIn('status', ['running', 'paired', 'pairing'])
                        ->count(),
                    'completed_trades' => DB::table('user_shares')
                        ->where('status', 'completed')
                        ->count(),
                    'total_volume' => DB::table('user_shares')->sum('amount'),
                ];
            }
        );
    }

    /**
     * Get user shares with pagination and caching
     */
    public function getUserShares($userId, $page = 1, $perPage = 15)
    {
        $cacheKey = "user_shares_{$userId}_page_{$page}_{$perPage}";
        
        return Cache::remember(
            $cacheKey,
            self::CACHE_DURATIONS['user_shares'],
            function () use ($userId, $page, $perPage) {
                $offset = ($page - 1) * $perPage;
                
                return DB::table('user_shares')
                    ->join('trades', 'user_shares.trade_id', '=', 'trades.id')
                    ->where('user_shares.user_id', $userId)
                    ->select(
                        'user_shares.*',
                        'trades.name as trade_name',
                        'trades.slug as trade_slug'
                    )
                    ->orderBy('user_shares.created_at', 'desc')
                    ->offset($offset)
                    ->limit($perPage)
                    ->get();
            }
        );
    }

    /**
     * Get available shares for pairing with caching
     */
    public function getAvailableShares($tradeId, $limit = 20)
    {
        return Cache::remember(
            "available_shares_trade_{$tradeId}",
            self::CACHE_DURATIONS['user_shares'],
            function () use ($tradeId, $limit) {
                return DB::table('user_shares')
                    ->where('trade_id', $tradeId)
                    ->where('status', 'pending')
                    ->where('hold_quantity', '>', 0)
                    ->select('id', 'user_id', 'amount', 'hold_quantity', 'created_at')
                    ->orderBy('created_at', 'asc')
                    ->limit($limit)
                    ->get();
            }
        );
    }

    /**
     * Get user payment history with caching
     */
    public function getUserPaymentHistory($userId, $limit = 10)
    {
        return Cache::remember(
            "user_payments_{$userId}_{$limit}",
            self::CACHE_DURATIONS['dashboard'],
            function () use ($userId, $limit) {
                return DB::table('user_share_payments')
                    ->join('user_shares', 'user_share_payments.user_share_id', '=', 'user_shares.id')
                    ->where('user_share_payments.sender_id', $userId)
                    ->orWhere('user_share_payments.receiver_id', $userId)
                    ->select(
                        'user_share_payments.*',
                        'user_shares.ticket_no'
                    )
                    ->orderBy('user_share_payments.created_at', 'desc')
                    ->limit($limit)
                    ->get();
            }
        );
    }

    /**
     * Clear cache for specific user
     */
    public function clearUserCache($userId)
    {
        $patterns = [
            "user_dashboard_{$userId}",
            "user_shares_{$userId}_*",
            "user_payments_{$userId}_*",
        ];

        foreach ($patterns as $pattern) {
            if (strpos($pattern, '*') !== false) {
                // For wildcard patterns, we need to implement a cache tag system
                // For now, we'll use a simple approach
                $baseKey = str_replace('_*', '', $pattern);
                for ($page = 1; $page <= 10; $page++) {
                    for ($perPage = 10; $perPage <= 50; $perPage += 5) {
                        Cache::forget("{$baseKey}_page_{$page}_{$perPage}");
                    }
                }
            } else {
                Cache::forget($pattern);
            }
        }
    }

    /**
     * Clear cache for trade-related data
     */
    public function clearTradeCache($tradeId = null)
    {
        Cache::forget('trade_statistics');
        Cache::forget('market_status');
        
        if ($tradeId) {
            Cache::forget("available_shares_trade_{$tradeId}");
        }
    }

    /**
     * Warm up cache for frequently accessed data
     */
    public function warmUpCache()
    {
        // Warm up market status
        $this->getMarketStatus();
        
        // Warm up trade statistics
        $this->getTradeStatistics();
        
        // Warm up available shares for all active trades
        $activeTrades = DB::table('trades')->where('status', 1)->pluck('id');
        foreach ($activeTrades as $tradeId) {
            $this->getAvailableShares($tradeId);
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats()
    {
        // This would need to be implemented based on the cache driver
        // For now, return basic info
        return [
            'driver' => config('cache.default'),
            'prefix' => config('cache.prefix'),
            'last_warmed_up' => Cache::get('cache_warmed_up_at'),
        ];
    }
}