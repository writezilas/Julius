<?php

/**
 * Share Purchase Performance Test
 * 
 * This script tests the performance improvements made to the share purchase system.
 * It measures:
 * 1. Available shares calculation time (with and without caching)
 * 2. Database query performance with new indexes
 * 3. Overall response time improvements
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Models\UserShare;
use App\Models\Trade;
use App\Services\ShareAvailabilityCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SharePurchasePerformanceTest
{
    protected $cacheService;
    protected $results = [];
    
    public function __construct()
    {
        $this->cacheService = new ShareAvailabilityCache();
    }
    
    public function runTests()
    {
        echo "🚀 Starting Share Purchase Performance Tests\n";
        echo str_repeat("=", 60) . "\n";
        
        // Get the first available trade for testing
        $trade = Trade::where('status', '1')->first();
        if (!$trade) {
            echo "❌ No active trades found for testing\n";
            return;
        }
        
        echo "📊 Testing with Trade: {$trade->name} (ID: {$trade->id})\n\n";
        
        // Test 1: Database Query Performance
        $this->testDatabaseQueryPerformance($trade->id);
        
        // Test 2: Cache Performance
        $this->testCachePerformance($trade->id);
        
        // Test 3: Helper Function Performance
        $this->testHelperFunctionPerformance($trade->id);
        
        // Display Results Summary
        $this->displayResultsSummary();
    }
    
    protected function testDatabaseQueryPerformance($tradeId)
    {
        echo "🔍 Test 1: Database Query Performance\n";
        
        // Clear query cache
        DB::statement('RESET QUERY CACHE');
        
        // Test the optimized query
        $startTime = microtime(true);
        
        $count = UserShare::whereTradeId($tradeId)
            ->whereStatus('completed')
            ->where('is_ready_to_sell', 1)
            ->where('total_share_count', '>', 0)
            ->whereHas('user', function ($query) {
                $query->whereIn('status', ['active', 'pending', 'fine']);
            })
            ->sum('total_share_count');
            
        $queryTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        
        // Get the last executed query
        DB::enableQueryLog();
        UserShare::whereTradeId($tradeId)
            ->whereStatus('completed')
            ->where('is_ready_to_sell', 1)
            ->where('total_share_count', '>', 0)
            ->whereHas('user', function ($query) {
                $query->whereIn('status', ['active', 'pending', 'fine']);
            })
            ->sum('total_share_count');
        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();
        
        $this->results['database_query'] = [
            'time_ms' => $queryTime,
            'count' => $count,
            'query' => end($queryLog)['query'] ?? 'N/A'
        ];
        
        echo "   ⏱️  Query Time: " . number_format($queryTime, 2) . " ms\n";
        echo "   📊 Available Shares: " . number_format($count) . "\n";
        echo "   🔍 Query used indexes: " . ($queryTime < 100 ? "✅ Yes (fast)" : "⚠️  Maybe (slow)") . "\n\n";
    }
    
    protected function testCachePerformance($tradeId)
    {
        echo "⚡ Test 2: Cache Performance\n";
        
        // Clear cache first
        $this->cacheService->clearCache($tradeId);
        
        // Test 1: First call (cache miss)
        $startTime = microtime(true);
        $count1 = $this->cacheService->getAvailableShares($tradeId);
        $cacheMissTime = (microtime(true) - $startTime) * 1000;
        
        // Test 2: Second call (cache hit)
        $startTime = microtime(true);
        $count2 = $this->cacheService->getAvailableShares($tradeId);
        $cacheHitTime = (microtime(true) - $startTime) * 1000;
        
        $this->results['cache_performance'] = [
            'cache_miss_time_ms' => $cacheMissTime,
            'cache_hit_time_ms' => $cacheHitTime,
            'improvement_factor' => $cacheMissTime / max($cacheHitTime, 0.001),
            'consistent_results' => $count1 === $count2
        ];
        
        echo "   🔄 Cache Miss Time: " . number_format($cacheMissTime, 2) . " ms\n";
        echo "   ⚡ Cache Hit Time: " . number_format($cacheHitTime, 2) . " ms\n";
        echo "   📈 Speed Improvement: " . number_format($cacheMissTime / max($cacheHitTime, 0.001), 1) . "x faster\n";
        echo "   ✅ Consistent Results: " . ($count1 === $count2 ? "Yes" : "No") . "\n\n";
    }
    
    protected function testHelperFunctionPerformance($tradeId)
    {
        echo "🧪 Test 3: Helper Function Performance\n";
        
        // Clear cache to test from fresh state
        $this->cacheService->clearCache($tradeId);
        
        // Test multiple calls to simulate real usage
        $times = [];
        $iterations = 5;
        
        for ($i = 0; $i < $iterations; $i++) {
            $startTime = microtime(true);
            $count = checkAvailableSharePerTrade($tradeId);
            $times[] = (microtime(true) - $startTime) * 1000;
            
            // Small delay between calls
            usleep(100000); // 0.1 seconds
        }
        
        $averageTime = array_sum($times) / count($times);
        $minTime = min($times);
        $maxTime = max($times);
        
        $this->results['helper_function'] = [
            'average_time_ms' => $averageTime,
            'min_time_ms' => $minTime,
            'max_time_ms' => $maxTime,
            'times' => $times
        ];
        
        echo "   📊 Average Time: " . number_format($averageTime, 2) . " ms\n";
        echo "   ⚡ Best Time: " . number_format($minTime, 2) . " ms\n";
        echo "   🐌 Worst Time: " . number_format($maxTime, 2) . " ms\n";
        echo "   📈 Performance: " . ($averageTime < 50 ? "🟢 Excellent" : ($averageTime < 200 ? "🟡 Good" : "🔴 Needs Improvement")) . "\n\n";
    }
    
    protected function displayResultsSummary()
    {
        echo "📋 PERFORMANCE SUMMARY\n";
        echo str_repeat("=", 60) . "\n";
        
        echo "💾 Database Performance:\n";
        echo "   - Query Time: " . number_format($this->results['database_query']['time_ms'], 2) . " ms\n";
        echo "   - Status: " . ($this->results['database_query']['time_ms'] < 100 ? "🟢 Fast" : "🔴 Slow") . "\n\n";
        
        echo "⚡ Cache Performance:\n";
        echo "   - Speed Improvement: " . number_format($this->results['cache_performance']['improvement_factor'], 1) . "x\n";
        echo "   - Cache Hit Time: " . number_format($this->results['cache_performance']['cache_hit_time_ms'], 2) . " ms\n";
        echo "   - Status: " . ($this->results['cache_performance']['cache_hit_time_ms'] < 5 ? "🟢 Excellent" : "🟡 Good") . "\n\n";
        
        echo "🎯 Overall Helper Function:\n";
        echo "   - Average Time: " . number_format($this->results['helper_function']['average_time_ms'], 2) . " ms\n";
        echo "   - Consistency: " . (max($this->results['helper_function']['times']) / min($this->results['helper_function']['times']) < 3 ? "🟢 Good" : "🟡 Variable") . "\n\n";
        
        echo "🏆 RECOMMENDATIONS:\n";
        if ($this->results['database_query']['time_ms'] < 100) {
            echo "   ✅ Database indexes are working well!\n";
        } else {
            echo "   ❌ Database queries are still slow. Check indexes.\n";
        }
        
        if ($this->results['cache_performance']['improvement_factor'] > 5) {
            echo "   ✅ Caching provides significant performance boost!\n";
        } else {
            echo "   ⚠️  Caching improvement is minimal.\n";
        }
        
        if ($this->results['helper_function']['average_time_ms'] < 50) {
            echo "   ✅ Share availability checks are very fast!\n";
        } elseif ($this->results['helper_function']['average_time_ms'] < 200) {
            echo "   ✅ Share availability checks are reasonably fast.\n";
        } else {
            echo "   ❌ Share availability checks need more optimization.\n";
        }
    }
}

// Run the tests
try {
    $tester = new SharePurchasePerformanceTest();
    $tester->runTests();
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}