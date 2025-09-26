<?php

/**
 * Test Script: Automatic Referral Bonus Floating
 * 
 * This script tests the automatic referral bonus system to ensure:
 * 1. Referral bonuses are automatically created when referred user has shares ready to sell
 * 2. Referral bonuses are automatically floated to market (no manual intervention)
 * 3. Floated bonuses are available for buyers through the existing pairing system
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\UserShare;
use App\Models\Trade;
use App\Http\Controllers\CronController;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Automatic Referral Bonus Testing ===\n\n";

try {
    // Step 1: Find or create test users
    echo "Step 1: Setting up test users...\n";
    
    $danny = User::where('username', 'danny')->first();
    $maddyPower = User::where('username', 'MaddyPower')->first();
    
    if (!$danny) {
        echo "❌ User 'danny' not found. Please create this test user first.\n";
        exit(1);
    }
    
    if (!$maddyPower) {
        echo "❌ User 'MaddyPower' not found. Please create this test user first.\n";
        exit(1);
    }
    
    echo "✅ Found Danny (ID: {$danny->id}) and MaddyPower (ID: {$maddyPower->id})\n";
    
    // Step 2: Verify referral relationship
    echo "\nStep 2: Verifying referral relationship...\n";
    if ($maddyPower->refferal_code !== $danny->username) {
        echo "❌ MaddyPower is not referred by Danny. Expected: {$danny->username}, Got: {$maddyPower->refferal_code}\n";
        exit(1);
    }
    echo "✅ MaddyPower is referred by Danny\n";
    
    // Step 3: Check if MaddyPower has shares ready to sell
    echo "\nStep 3: Checking MaddyPower's shares...\n";
    $maddyShares = UserShare::where('user_id', $maddyPower->id)
        ->where('is_ready_to_sell', 1)
        ->where('status', 'completed')
        ->get();
    
    if ($maddyShares->count() === 0) {
        echo "❌ MaddyPower has no shares ready to sell. Creating test share...\n";
        
        // Create a test share for MaddyPower
        $trade = Trade::first();
        if (!$trade) {
            echo "❌ No trades found. Please create a trade first.\n";
            exit(1);
        }
        
        $testShare = UserShare::create([
            'trade_id' => $trade->id,
            'user_id' => $maddyPower->id,
            'ticket_no' => 'TEST-' . time(),
            'amount' => 1000,
            'period' => 1,
            'share_will_get' => 100,
            'total_share_count' => 100,
            'start_date' => now()->subDays(2)->format('Y/m/d H:i:s'), // Make it mature
            'status' => 'completed',
            'is_ready_to_sell' => 1,
            'get_from' => 'purchase'
        ]);
        
        echo "✅ Created test share for MaddyPower (ID: {$testShare->id})\n";
        $maddyShares = collect([$testShare]);
    } else {
        echo "✅ MaddyPower has {$maddyShares->count()} shares ready to sell\n";
    }
    
    // Step 4: Check existing referral bonuses for Danny
    echo "\nStep 4: Checking existing referral bonuses for Danny...\n";
    $existingBonuses = UserShare::where('user_id', $danny->id)
        ->where('get_from', 'refferal-bonus')
        ->get();
    
    echo "ℹ️  Danny has {$existingBonuses->count()} existing referral bonus(es)\n";
    
    // Step 5: Run the cron job to trigger automatic referral bonus creation
    echo "\nStep 5: Running cron job to trigger automatic bonus creation...\n";
    
    $cronController = new CronController();
    $result = $cronController->checkUnPaidReffMatureUser();
    
    echo "✅ Cron job executed (Result: {$result})\n";
    
    // Step 6: Check if new referral bonus was created and automatically floated
    echo "\nStep 6: Checking for new referral bonuses...\n";
    
    $newBonuses = UserShare::where('user_id', $danny->id)
        ->where('get_from', 'refferal-bonus')
        ->get();
    
    echo "ℹ️  Danny now has {$newBonuses->count()} referral bonus(es)\n";
    
    if ($newBonuses->count() > $existingBonuses->count()) {
        echo "✅ New referral bonus created!\n";
        
        $latestBonus = $newBonuses->sortByDesc('created_at')->first();
        
        // Step 7: Verify automatic floating
        echo "\nStep 7: Verifying automatic floating...\n";
        
        echo "Bonus Details:\n";
        echo "- ID: {$latestBonus->id}\n";
        echo "- Ticket: {$latestBonus->ticket_no}\n";
        echo "- Amount: KSH {$latestBonus->share_will_get}\n";
        echo "- Status: {$latestBonus->status}\n";
        echo "- Ready to Sell: " . ($latestBonus->is_ready_to_sell ? 'Yes' : 'No') . "\n";
        echo "- Total Share Count: {$latestBonus->total_share_count}\n";
        echo "- Matured At: " . ($latestBonus->matured_at ? $latestBonus->matured_at : 'No') . "\n";
        echo "- Selling Started: " . ($latestBonus->selling_started_at ? $latestBonus->selling_started_at : 'No') . "\n";
        
        // Verify automatic maturation and readiness conditions
        $isAutomaticallyMatured = (
            $latestBonus->status === 'completed' &&
            $latestBonus->is_ready_to_sell == 1 &&
            $latestBonus->total_share_count > 0 &&
            $latestBonus->matured_at !== null &&
            $latestBonus->selling_started_at !== null
        );
        
        if ($isAutomaticallyMatured) {
            echo "\n✅ AUTOMATIC MATURATION SUCCESSFUL!\n";
            echo "   - Bonus is matured and ready for buyers to purchase\n";
            echo "   - No manual intervention was required\n";
            echo "   - Bonus follows standard lifecycle (started_at -> selling_started_at -> matured_at)\n";
        } else {
            echo "\n❌ AUTOMATIC MATURATION FAILED!\n";
            echo "   - Manual intervention may be required\n";
        }
        
        // Step 8: Verify availability for buyers
        echo "\nStep 8: Verifying availability for buyers...\n";
        
        // Check if the bonus would be available in the buyer's share selection
        $availableForBuyers = UserShare::where('trade_id', $latestBonus->trade_id)
            ->where('status', 'completed')
            ->where('is_ready_to_sell', 1)
            ->where('total_share_count', '>', 0)
            ->where('user_id', '!=', $danny->id) // Exclude Danny himself
            ->where('id', $latestBonus->id)
            ->exists();
        
        if ($availableForBuyers) {
            echo "✅ Bonus is available for buyers in the market\n";
        } else {
            echo "❌ Bonus is NOT available for buyers\n";
        }
        
    } elseif ($newBonuses->count() === $existingBonuses->count()) {
        if ($newBonuses->count() > 0) {
            echo "ℹ️  Referral bonus already exists (no new bonus needed)\n";
            
            $existingBonus = $newBonuses->first();
            echo "\nExisting Bonus Status:\n";
            echo "- Matured: " . ($existingBonus->matured_at ? 'Yes' : 'No') . "\n";
            echo "- Available for Trading: " . ($existingBonus->is_ready_to_sell ? 'Yes' : 'No') . "\n";
        } else {
            echo "❌ No referral bonus found. Check if MaddyPower meets the criteria.\n";
        }
    }
    
    echo "\n=== Test Completed Successfully ===\n";
    
} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}