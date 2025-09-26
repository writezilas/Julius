<?php

/**
 * Test the new trade liquidity selection function
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Trade;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Trade Liquidity Selection ===\n\n";

try {
    // Test the new function
    echo "Testing findTradeWithMostLiquidity()...\n";
    
    $selectedTrade = findTradeWithMostLiquidity();
    
    if ($selectedTrade) {
        echo "✅ Selected trade: {$selectedTrade->name} (ID: {$selectedTrade->id})\n";
        $availableShares = checkAvailableSharePerTrade($selectedTrade->id);
        echo "✅ Available shares in selected trade: {$availableShares}\n";
    } else {
        echo "❌ No trade selected (no liquidity found)\n";
    }
    
    // Show comparison with all trades
    echo "\n📊 Comparison with all active trades:\n";
    $trades = Trade::where('status', '1')->get();
    
    foreach ($trades as $trade) {
        $available = checkAvailableSharePerTrade($trade->id);
        $isSelected = $selectedTrade && $selectedTrade->id === $trade->id;
        $marker = $isSelected ? " ← SELECTED" : "";
        
        echo "- {$trade->name} (ID: {$trade->id}): {$available} shares{$marker}\n";
    }
    
    echo "\n🎯 Impact: New referral bonuses will be created in the trade with the most available shares.\n";
    echo "This means referral bonuses can be paid from any wallet with liquidity, not just Safaricom.\n";
    
    echo "\n=== Test Complete ===\n";
    
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    exit(1);
}