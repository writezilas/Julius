<?php

echo "🎯 MATURE ALL RUNNING SHARES - USING EXISTING LOGIC\n";
echo str_repeat('=', 60) . "\n\n";

try {
    // Load Laravel framework
    require_once 'bootstrap/app.php';
    
    echo "📊 STEP 1: IDENTIFY SHARES TO MATURE\n";
    
    // Get all running shares using the same criteria as the existing logic
    $runningShares = \App\Models\UserShare::where('status', 'completed')
        ->where('is_ready_to_sell', 0)
        ->where('total_share_count', '>', 0)
        ->whereNotNull('start_date')
        ->where('start_date', '!=', '')
        ->with(['user', 'trade'])
        ->get();
    
    if ($runningShares->isEmpty()) {
        echo "   ✅ No running shares found to mature.\n";
        echo "   All shares are either already matured or not started yet.\n";
        exit(0);
    }
    
    echo "   Found " . $runningShares->count() . " running shares to mature:\n\n";
    
    $totalSharesToMature = 0;
    $tradeGroups = [];
    
    foreach ($runningShares as $share) {
        $tradeName = $share->trade->name;
        if (!isset($tradeGroups[$tradeName])) {
            $tradeGroups[$tradeName] = [];
        }
        $tradeGroups[$tradeName][] = $share;
        
        echo "     🔸 Share ID {$share->id} - {$tradeName}\n";
        echo "       User: {$share->user->username} ({$share->user->name})\n";
        echo "       Shares: " . number_format($share->total_share_count) . "\n";
        echo "       Start Date: {$share->start_date}\n";
        echo "       Status: {$share->status} | Ready to sell: " . ($share->is_ready_to_sell ? 'YES' : 'NO') . "\n\n";
        
        $totalSharesToMature += $share->total_share_count;
    }
    
    echo "   📈 Summary by Trade:\n";
    foreach ($tradeGroups as $tradeName => $shares) {
        $tradeTotal = array_sum(array_map(fn($s) => $s->total_share_count, $shares));
        echo "     - {$tradeName}: " . count($shares) . " shares, " . number_format($tradeTotal) . " total quantity\n";
    }
    
    echo "\n   🎯 TOTAL TO MATURE: " . number_format($totalSharesToMature) . " shares across " . $runningShares->count() . " records\n\n";
    
    echo "📊 STEP 2: MATURE SHARES USING EXISTING LOGIC\n";
    
    // Create PaymentFailureService instance (used in existing logic)
    $paymentFailureService = new \App\Services\PaymentFailureService();
    
    $maturedCount = 0;
    $totalProfitAdded = 0;
    $processedShares = [];
    
    foreach ($runningShares as $share) {
        echo "   🔄 Processing Share ID {$share->id}...\n";
        
        // Check if share should be matured (mimicking updateMaturedShareStatus logic)
        if ($share->timer_paused) {
            echo "     ⏸️  Share is paused, skipping...\n";
            continue;
        }
        
        // Get adjusted timer to account for paused duration (existing logic)
        $timerInfo = $paymentFailureService->getAdjustedShareTimer($share);
        $adjustedEndTime = $timerInfo['adjusted_end_time'];
        
        // For maturation script, we'll force mature all running shares
        // But using the existing profit calculation logic
        
        if (function_exists('calculateProfitOfShare')) {
            $profit = calculateProfitOfShare($share);
        } else {
            // Fallback profit calculation (same as in helpers.php)
            $trade = \App\Models\Trade::where('id', $share->trade_id)->first();
            $period = \App\Models\TradePeriod::where('days', $share->period)->first();
            
            if ($period && $trade) {
                $profit = ($period->percentage / 100) * $share->total_share_count;
            } else {
                $profit = 0; // Fallback if period not found
                echo "     ⚠️  Could not calculate profit (period/trade not found)\n";
            }
        }
        
        echo "     💰 Calculated profit: " . number_format($profit) . " shares\n";
        
        // Apply the exact same logic as updateMaturedShareStatus()
        $share->is_ready_to_sell = 1;
        $share->matured_at = now()->format("Y/m/d H:i:s");
        $share->profit_share = $profit;
        $share->total_share_count = $share->total_share_count + $profit;
        $share->save();
        
        // Create profit history record (same as existing logic)
        $profitHistoryData = [
            'user_share_id' => $share->id,
            'shares' => $profit,
        ];
        
        \App\Models\UserProfitHistory::create($profitHistoryData);
        
        // Log the action (same as existing logic)
        \Illuminate\Support\Facades\Log::info('Share manually matured (script): ' . $share->id);
        
        $maturedCount++;
        $totalProfitAdded += $profit;
        
        $processedShares[] = [
            'id' => $share->id,
            'user' => $share->user->username,
            'trade' => $share->trade->name,
            'original_shares' => $share->total_share_count - $profit,
            'profit_added' => $profit,
            'final_shares' => $share->total_share_count
        ];
        
        echo "     ✅ Share matured successfully!\n";
        echo "     📈 Original: " . number_format($share->total_share_count - $profit) . " shares\n";
        echo "     📈 Profit: " . number_format($profit) . " shares\n";
        echo "     📈 Final: " . number_format($share->total_share_count) . " shares\n\n";
    }
    
    echo "📊 STEP 3: MATURATION SUMMARY\n";
    echo "   ✅ Successfully matured: {$maturedCount} shares\n";
    echo "   💰 Total profit added: " . number_format($totalProfitAdded) . " shares\n";
    echo "   📈 Total shares now available: " . number_format($totalSharesToMature + $totalProfitAdded) . " shares\n\n";
    
    if (!empty($processedShares)) {
        echo "📋 DETAILED RESULTS:\n";
        foreach ($processedShares as $processed) {
            echo "   🎯 Share ID {$processed['id']} ({$processed['user']} - {$processed['trade']}):\n";
            echo "     Original: " . number_format($processed['original_shares']) . " shares\n";
            echo "     Profit: " . number_format($processed['profit_added']) . " shares\n";
            echo "     Final: " . number_format($processed['final_shares']) . " shares\n\n";
        }
    }
    
    echo "📊 STEP 4: VERIFY MARKET AVAILABILITY\n";
    
    // Check the market availability after maturation
    $availableShares = checkAvailableSharePerTrade(1); // Check Safaricom (Trade ID 1)
    echo "   📈 Safaricom shares now available in market: " . number_format($availableShares) . "\n";
    
    // Check other trades if any
    $allTrades = \App\Models\Trade::all();
    foreach ($allTrades as $trade) {
        if ($trade->id != 1) { // Skip Safaricom as we already checked it
            $tradeAvailable = checkAvailableSharePerTrade($trade->id);
            if ($tradeAvailable > 0) {
                echo "   📈 {$trade->name} shares available: " . number_format($tradeAvailable) . "\n";
            }
        }
    }
    
    echo "\n🎉 MATURATION COMPLETED SUCCESSFULLY!\n";
    echo "All running shares have been matured and are now available in the market.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}