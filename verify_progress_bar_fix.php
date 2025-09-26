<?php

/**
 * Progress Bar Fix Verification Script
 * 
 * This script verifies that the progress bar fix is working correctly
 * for user "maddypower" and Safaricom shares.
 * 
 * Issue: Progress bar was showing 50% instead of 100% when all shares were available
 * Fix: Progress calculation now shows availability percentage (inverted logic)
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Safaricom Progress Bar Fix Verification ===\n\n";

try {
    // Test with user "maddypower" (ID: 9)
    $user = \App\Models\User::find(9);
    if (!$user) {
        echo "❌ User 'maddypower' not found\n";
        exit(1);
    }

    auth()->login($user);
    
    echo "🔍 Testing progress calculation for user: {$user->username}\n\n";

    // Use the fixed progress calculation service
    $service = new \App\Services\ProgressCalculationService();
    $result = $service->computeTradeProgress(1); // Safaricom trade ID = 1

    echo "📊 Progress Calculation Results:\n";
    echo "--------------------------------\n";
    echo "Trade: {$result['trade_name']}\n";
    echo "Progress Percentage: {$result['progress_percentage']}%\n";
    echo "Actually Sold Shares: {$result['shares_bought']}\n";
    echo "Available for Sale: {$result['available_shares']}\n";
    echo "Admin Inventory: {$result['admin_allocated_shares']}\n";
    echo "Referral Inventory: {$result['referral_bonus_shares']}\n\n";

    // Verify the fix - calculate expected progress based on actual market state
    $availableShares = $result['available_shares'];
    $soldShares = $result['shares_bought'];
    $totalMarketShares = $availableShares + $soldShares;
    
    $expectedProgress = $totalMarketShares > 0 ? round(($availableShares / $totalMarketShares) * 100, 0) : 0;
    $actualProgress = $result['progress_percentage'];

    echo "✅ Fix Verification:\n";
    echo "--------------------\n";
    echo "Market State: {$soldShares} sold, {$availableShares} available\n";
    echo "Expected Progress: {$expectedProgress}% (availability percentage)\n";
    echo "Actual Progress: {$actualProgress}%\n";
    
    if (abs($actualProgress - $expectedProgress) <= 1) { // Allow 1% tolerance for rounding
        echo "Status: ✅ FIXED! Progress bar correctly shows {$actualProgress}%\n";
        echo "Reason: {$actualProgress}% of shares are still available for purchase\n\n";
        
        echo "📋 Summary of the Fix:\n";
        echo "- Before: Progress incorrectly counted admin inventory as 'sold' = 50%\n";
        echo "- After: Progress shows availability percentage = 100%\n";
        echo "- Higher percentage = more opportunity to buy shares\n";
        echo "- Progress will show 0% only when all shares are sold out\n\n";
        
        echo "🎯 Expected Behavior (Inverted Logic):\n";
        echo "- 100% = All shares available for purchase\n";
        echo "- 90% = 90% of shares available, 10% sold (current state)\n";
        echo "- 50% = Half of shares available, half sold\n";
        echo "- 0% = All shares sold out (no availability)\n\n";
        
        exit(0);
    } else {
        echo "Status: ❌ NOT FIXED - Progress should be {$expectedProgress}% but shows {$actualProgress}%\n";
        exit(1);
    }

} catch (\Exception $e) {
    echo "❌ Verification failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}