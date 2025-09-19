<?php
/**
 * Mature Running Shares Script
 * 
 * This script manually matures all currently running shares to make them available in the market
 * without changing any logic - it uses the existing maturation process
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Models\UserProfitHistory;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

echo "🔄 MATURE RUNNING SHARES SCRIPT\n";
echo str_repeat("=", 50) . "\n\n";

// Find all currently running shares
$runningShares = UserShare::where('status', 'completed')
    ->where('is_ready_to_sell', 0)
    ->whereNotNull('start_date')
    ->with(['user', 'trade'])
    ->get();

echo "📊 Found {$runningShares->count()} running shares to mature:\n\n";

if ($runningShares->isEmpty()) {
    echo "✅ No running shares found. All shares are already matured.\n";
    exit(0);
}

// Display shares to be matured
echo "🔍 SHARES TO MATURE:\n";
echo str_repeat("-", 80) . "\n";
printf("%-5s %-15s %-20s %-15s %-20s\n", 'ID', 'User', 'Trade', 'Shares', 'Start Date');
echo str_repeat("-", 80) . "\n";

foreach ($runningShares as $share) {
    printf("%-5s %-15s %-20s %-15s %-20s\n", 
        $share->id,
        substr($share->user->name ?? 'Unknown', 0, 14),
        substr($share->trade->name ?? 'Unknown', 0, 19),
        number_format($share->total_share_count),
        $share->start_date ? substr($share->start_date, 0, 19) : 'No date'
    );
}

echo "\n" . str_repeat("-", 80) . "\n";

// Confirmation
echo "\n⚠️  WARNING: This will mature ALL running shares immediately!\n";
echo "📈 Matured shares will become available in the market with profit added.\n";
echo "🔒 This action cannot be undone.\n\n";

echo "Do you want to proceed? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) !== 'yes') {
    echo "\n❌ Operation cancelled by user.\n";
    exit(0);
}

echo "\n🚀 Starting maturation process...\n\n";

$successCount = 0;
$errorCount = 0;
$totalProfitAdded = 0;

foreach ($runningShares as $share) {
    try {
        echo "Processing Share ID {$share->id} ({$share->user->name}) ... ";
        
        // Calculate profit using existing logic
        $profit = calculateProfitOfShare($share);
        
        // Apply maturation using existing logic
        $originalShares = $share->total_share_count;
        
        $share->is_ready_to_sell = 1;
        $share->matured_at = now()->format('Y/m/d H:i:s');
        $share->profit_share = $profit;
        $share->total_share_count = $share->total_share_count + $profit;
        $share->save();
        
        // Create profit history record using existing logic
        $profitHistoryData = [
            'user_share_id' => $share->id,
            'shares' => $profit,
        ];
        
        UserProfitHistory::create($profitHistoryData);
        
        // Log the action
        Log::info("Manual maturation: Share {$share->id} matured", [
            'user_id' => $share->user_id,
            'original_shares' => $originalShares,
            'profit_added' => $profit,
            'final_shares' => $share->total_share_count,
            'matured_by' => 'mature_running_shares_script'
        ]);
        
        echo "✅ SUCCESS\n";
        echo "   Original: " . number_format($originalShares) . " shares\n";
        echo "   Profit: +" . number_format($profit) . " shares\n";
        echo "   Final: " . number_format($share->total_share_count) . " shares\n\n";
        
        $successCount++;
        $totalProfitAdded += $profit;
        
    } catch (Exception $e) {
        echo "❌ ERROR: {$e->getMessage()}\n\n";
        
        Log::error("Manual maturation failed for share {$share->id}", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        $errorCount++;
    }
}

echo str_repeat("=", 50) . "\n";
echo "📋 MATURATION COMPLETED\n\n";

echo "📊 SUMMARY:\n";
echo "   ✅ Successfully matured: {$successCount} shares\n";
echo "   ❌ Errors: {$errorCount} shares\n";
echo "   📈 Total profit added: " . number_format($totalProfitAdded) . " shares\n";

if ($successCount > 0) {
    echo "\n🎉 SUCCESS! All running shares have been matured and are now available in the market.\n";
    echo "💰 Users can now sell their shares with profit included.\n";
    
    // Check updated market availability for Safaricom
    echo "\n📈 MARKET UPDATE:\n";
    try {
        $safaricomAvailable = checkAvailableSharePerTrade(1);
        echo "   Safaricom shares now available: " . number_format($safaricomAvailable) . "\n";
        
        // Check progress update
        $progressService = new App\Services\ProgressCalculationService();
        $result = $progressService->computeTradeProgress(1);
        echo "   Safaricom progress: {$result['progress_percentage']}%\n";
        
    } catch (Exception $e) {
        echo "   Could not check market status: {$e->getMessage()}\n";
    }
}

if ($errorCount > 0) {
    echo "\n⚠️  Some shares could not be matured. Check the logs for details.\n";
}

echo "\n✅ Script completed successfully!\n";