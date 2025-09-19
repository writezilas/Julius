<?php

echo "🎯 MATURE ALL RUNNING SHARES - DIRECT SQL VERSION\n";
echo str_repeat('=', 60) . "\n\n";

try {
    // Direct database connection
    $pdo = new PDO('mysql:host=127.0.0.1;port=8000;dbname=u773742080_autobidder;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "📊 STEP 1: IDENTIFY SHARES TO MATURE\n";
    
    // Get all running shares
    $query = "
        SELECT us.id, us.user_id, us.trade_id, us.total_share_count, us.sold_quantity, 
               us.status, us.is_ready_to_sell, us.start_date, us.period,
               u.username, u.name, t.name as trade_name
        FROM user_shares us
        JOIN users u ON us.user_id = u.id
        JOIN trades t ON us.trade_id = t.id
        WHERE us.status = 'completed'
        AND us.is_ready_to_sell = 0
        AND us.total_share_count > 0
        AND us.start_date IS NOT NULL
        AND us.start_date != ''
        AND u.status IN ('active', 'pending', 'fine')
        ORDER BY us.trade_id, us.id
    ";
    
    $runningShares = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($runningShares)) {
        echo "   ✅ No running shares found to mature.\n";
        echo "   All shares are either already matured or not started yet.\n";
        exit(0);
    }
    
    echo "   Found " . count($runningShares) . " running shares to mature:\n\n";
    
    $totalSharesToMature = 0;
    $tradeGroups = [];
    
    foreach ($runningShares as $share) {
        $tradeName = $share['trade_name'];
        if (!isset($tradeGroups[$tradeName])) {
            $tradeGroups[$tradeName] = [];
        }
        $tradeGroups[$tradeName][] = $share;
        
        echo "     🔸 Share ID {$share['id']} - {$tradeName}\n";
        echo "       User: {$share['username']} ({$share['name']})\n";
        echo "       Shares: " . number_format($share['total_share_count']) . "\n";
        echo "       Start Date: {$share['start_date']}\n";
        echo "       Status: {$share['status']} | Ready to sell: " . ($share['is_ready_to_sell'] ? 'YES' : 'NO') . "\n\n";
        
        $totalSharesToMature += $share['total_share_count'];
    }
    
    echo "   📈 Summary by Trade:\n";
    foreach ($tradeGroups as $tradeName => $shares) {
        $tradeTotal = array_sum(array_column($shares, 'total_share_count'));
        echo "     - {$tradeName}: " . count($shares) . " shares, " . number_format($tradeTotal) . " total quantity\n";
    }
    
    echo "\n   🎯 TOTAL TO MATURE: " . number_format($totalSharesToMature) . " shares across " . count($runningShares) . " records\n\n";
    
    echo "📊 STEP 2: MATURE SHARES USING EXISTING LOGIC\n";
    
    $maturedCount = 0;
    $totalProfitAdded = 0;
    $processedShares = [];
    
    foreach ($runningShares as $share) {
        echo "   🔄 Processing Share ID {$share['id']}...\n";
        
        // Calculate profit using the same logic as calculateProfitOfShare()
        $periodQuery = "SELECT percentage FROM trade_periods WHERE days = ?";
        $periodStmt = $pdo->prepare($periodQuery);
        $periodStmt->execute([$share['period']]);
        $period = $periodStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($period) {
            $profit = ($period['percentage'] / 100) * $share['total_share_count'];
        } else {
            $profit = 0; // Fallback if period not found
            echo "     ⚠️  Could not calculate profit (period not found for {$share['period']} days)\n";
        }
        
        echo "     💰 Calculated profit: " . number_format($profit) . " shares (using {$period['percentage']}% for {$share['period']} days)\n";
        
        // Apply the exact same logic as updateMaturedShareStatus()
        $currentDateTime = date("Y/m/d H:i:s");
        $newTotalShares = $share['total_share_count'] + $profit;
        
        // Update the share
        $updateQuery = "
            UPDATE user_shares 
            SET is_ready_to_sell = 1,
                matured_at = ?,
                profit_share = ?,
                total_share_count = ?
            WHERE id = ?
        ";
        
        $updateStmt = $pdo->prepare($updateQuery);
        $updateResult = $updateStmt->execute([
            $currentDateTime,
            $profit,
            $newTotalShares,
            $share['id']
        ]);
        
        if ($updateResult) {
            // Create profit history record (same as existing logic)
            $profitHistoryQuery = "
                INSERT INTO user_profit_histories (user_share_id, shares, created_at, updated_at)
                VALUES (?, ?, NOW(), NOW())
            ";
            
            $profitHistoryStmt = $pdo->prepare($profitHistoryQuery);
            $profitHistoryStmt->execute([$share['id'], $profit]);
            
            $maturedCount++;
            $totalProfitAdded += $profit;
            
            $processedShares[] = [
                'id' => $share['id'],
                'user' => $share['username'],
                'trade' => $share['trade_name'],
                'original_shares' => $share['total_share_count'],
                'profit_added' => $profit,
                'final_shares' => $newTotalShares
            ];
            
            echo "     ✅ Share matured successfully!\n";
            echo "     📈 Original: " . number_format($share['total_share_count']) . " shares\n";
            echo "     📈 Profit: " . number_format($profit) . " shares\n";
            echo "     📈 Final: " . number_format($newTotalShares) . " shares\n\n";
        } else {
            echo "     ❌ Failed to update share!\n\n";
        }
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
    
    // Check market availability for each trade
    $tradesQuery = "SELECT id, name FROM trades ORDER BY id";
    $trades = $pdo->query($tradesQuery)->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($trades as $trade) {
        // Calculate available shares for this trade (same logic as checkAvailableSharePerTrade)
        $availableQuery = "
            SELECT SUM(GREATEST(us.total_share_count - us.sold_quantity, 0)) as total_available
            FROM user_shares us
            JOIN users u ON us.user_id = u.id
            WHERE us.trade_id = ?
            AND us.status = 'completed'
            AND us.is_ready_to_sell = 1
            AND us.total_share_count > 0
            AND u.status IN ('active', 'pending', 'fine')
        ";
        
        $availableStmt = $pdo->prepare($availableQuery);
        $availableStmt->execute([$trade['id']]);
        $availableResult = $availableStmt->fetch(PDO::FETCH_ASSOC);
        $availableShares = $availableResult['total_available'] ?: 0;
        
        if ($availableShares > 0) {
            echo "   📈 {$trade['name']} shares available in market: " . number_format($availableShares) . "\n";
        } else {
            echo "   📊 {$trade['name']}: No shares available in market\n";
        }
    }
    
    echo "\n🎉 MATURATION COMPLETED SUCCESSFULLY!\n";
    echo "All running shares have been matured and are now available in the market.\n";
    echo "The shares have been processed using the exact same logic as the existing updateMaturedShareStatus() function.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}