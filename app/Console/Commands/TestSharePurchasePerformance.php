<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use App\Models\Trade;
use App\Services\ShareAvailabilityCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestSharePurchasePerformance extends Command
{
    protected $signature = 'test:share-purchase-performance {--trade-id=}';
    protected $description = 'Test the performance improvements for share purchase operations';
    
    protected $cacheService;
    protected $results = [];

    public function __construct()
    {
        parent::__construct();
        $this->cacheService = new ShareAvailabilityCache();
    }

    public function handle()
    {
        $this->info('🚀 Starting Share Purchase Performance Tests');
        $this->info(str_repeat('=', 60));
        
        // Get trade ID from option or find first active trade
        $tradeId = $this->option('trade-id');
        if ($tradeId) {
            $trade = Trade::find($tradeId);
        } else {
            $trade = Trade::where('status', '1')->first();
        }
        
        if (!$trade) {
            $this->error('❌ No active trades found for testing');
            return 1;
        }
        
        $this->info("📊 Testing with Trade: {$trade->name} (ID: {$trade->id})");
        $this->newLine();
        
        // Run tests
        $this->testDatabaseQueryPerformance($trade->id);
        $this->testCachePerformance($trade->id);
        $this->testHelperFunctionPerformance($trade->id);
        $this->displayResultsSummary();
        
        return 0;
    }
    
    protected function testDatabaseQueryPerformance($tradeId)
    {
        $this->info('🔍 Test 1: Database Query Performance');
        
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
        
        $this->results['database_query'] = [
            'time_ms' => $queryTime,
            'count' => $count
        ];
        
        $this->line("   ⏱️  Query Time: " . number_format($queryTime, 2) . " ms");
        $this->line("   📊 Available Shares: " . number_format($count));
        $this->line("   🔍 Query Performance: " . ($queryTime < 100 ? "✅ Fast" : "⚠️  Slow"));
        $this->newLine();
    }
    
    protected function testCachePerformance($tradeId)
    {
        $this->info('⚡ Test 2: Cache Performance');
        
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
        
        $this->line("   🔄 Cache Miss Time: " . number_format($cacheMissTime, 2) . " ms");
        $this->line("   ⚡ Cache Hit Time: " . number_format($cacheHitTime, 2) . " ms");
        $this->line("   📈 Speed Improvement: " . number_format($cacheMissTime / max($cacheHitTime, 0.001), 1) . "x faster");
        $this->line("   ✅ Consistent Results: " . ($count1 === $count2 ? "Yes" : "No"));
        $this->newLine();
    }
    
    protected function testHelperFunctionPerformance($tradeId)
    {
        $this->info('🧪 Test 3: Helper Function Performance');
        
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
        
        $this->line("   📊 Average Time: " . number_format($averageTime, 2) . " ms");
        $this->line("   ⚡ Best Time: " . number_format($minTime, 2) . " ms");
        $this->line("   🐌 Worst Time: " . number_format($maxTime, 2) . " ms");
        $this->line("   📈 Performance: " . ($averageTime < 50 ? "🟢 Excellent" : ($averageTime < 200 ? "🟡 Good" : "🔴 Needs Improvement")));
        $this->newLine();
    }
    
    protected function displayResultsSummary()
    {
        $this->info('📋 PERFORMANCE SUMMARY');
        $this->info(str_repeat('=', 60));
        
        $this->line('💾 Database Performance:');
        $this->line('   - Query Time: ' . number_format($this->results['database_query']['time_ms'], 2) . ' ms');
        $this->line('   - Status: ' . ($this->results['database_query']['time_ms'] < 100 ? '🟢 Fast' : '🔴 Slow'));
        $this->newLine();
        
        $this->line('⚡ Cache Performance:');
        $this->line('   - Speed Improvement: ' . number_format($this->results['cache_performance']['improvement_factor'], 1) . 'x');
        $this->line('   - Cache Hit Time: ' . number_format($this->results['cache_performance']['cache_hit_time_ms'], 2) . ' ms');
        $this->line('   - Status: ' . ($this->results['cache_performance']['cache_hit_time_ms'] < 5 ? '🟢 Excellent' : '🟡 Good'));
        $this->newLine();
        
        $this->line('🎯 Overall Helper Function:');
        $this->line('   - Average Time: ' . number_format($this->results['helper_function']['average_time_ms'], 2) . ' ms');
        $consistency = max($this->results['helper_function']['times']) / min($this->results['helper_function']['times']);
        $this->line('   - Consistency: ' . ($consistency < 3 ? '🟢 Good' : '🟡 Variable'));
        $this->newLine();
        
        $this->info('🏆 RECOMMENDATIONS:');
        if ($this->results['database_query']['time_ms'] < 100) {
            $this->line('   ✅ Database indexes are working well!');
        } else {
            $this->line('   ❌ Database queries are still slow. Check indexes.');
        }
        
        if ($this->results['cache_performance']['improvement_factor'] > 5) {
            $this->line('   ✅ Caching provides significant performance boost!');
        } else {
            $this->line('   ⚠️  Caching improvement is minimal.');
        }
        
        if ($this->results['helper_function']['average_time_ms'] < 50) {
            $this->line('   ✅ Share availability checks are very fast!');
        } elseif ($this->results['helper_function']['average_time_ms'] < 200) {
            $this->line('   ✅ Share availability checks are reasonably fast.');
        } else {
            $this->line('   ❌ Share availability checks need more optimization.');
        }
    }
}