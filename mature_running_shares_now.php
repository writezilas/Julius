<?php
/**
 * Mature All Running Shares Script
 * 
 * This script matures all shares that are currently running (status=completed, is_ready_to_sell=0)
 * by using the existing maturation logic without changing any core functionality.
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Models\UserProfitHistory;
use App\Models\Trade;
use App\Models\TradePeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "🚀 MATURE ALL RUNNING SHARES\n";
echo str_repeat("=", 70) . "\n\n";

try {
    DB::beginTransaction();
    
    // Find all currently running shares
    $runningShares = UserShare::where('status', 'completed')
        ->where('is_ready_to_sell', 0)
        ->whereNotNull('start_date')
        ->whereNotNull('period')
        ->with(['user', 'trade'])
        ->get();
        
    echo "📊 ANALYSIS:\n";
    echo "   Found " . $runningShares->count() . " running share(s) to mature\n\n";
    
    if ($runningShares->count() === 0) {
        echo "✅ No running shares found to mature.\n";
        DB::rollBack();
        exit(0);
    }
    
    $maturedCount = 0;
    $errorCount = 0;
    $totalProfitAdded = 0;
    
    echo "🔄 PROCESSING SHARES:\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach ($runningShares as $share) {
        try {
            echo "Processing: {$share->ticket_no} (ID: {$share->id})\n";
            echo "  User: {$share->user->name} ({$share->user->username})\n";
            echo "  Trade: {$share->trade->name}\n";
            echo "  Start Date: {$share->start_date}\n";
            echo "  Period: {$share->period} days\n";
            echo "  Current Shares: " . number_format($share->total_share_count) . "\n";
            
            // Calculate profit using the same logic as existing system
            $trade = Trade::where('id', $share->trade_id)->first();
            $period = TradePeriod::where('days', $share->period)->first();
            
            if (!$period || !$trade) {
                echo "  ❌ ERROR: Could not find trade period or trade data\n";
                $errorCount++;
                continue;
            }
            
            $profit = ($period->percentage / 100) * $share->total_share_count;
            echo "  Calculated Profit: " . number_format($profit) . " shares (at {$period->percentage}%)\n";
            
            // Apply maturation using the exact same logic as the existing system
            $originalShares = $share->total_share_count;
            
            $share->is_ready_to_sell = 1;
            $share->matured_at = now()->format("Y/m/d H:i:s");
            $share->profit_share = $profit;
            $share->total_share_count = $share->total_share_count + $profit;
            
            // Clear timer fields (existing logic)
            $share->timer_paused = 0;
            $share->timer_paused_at = null;
            
            $share->save();
            
            // Create profit history record (existing logic)
            $profitHistoryData = [
                'user_share_id' => $share->id,
                'shares' => $profit,
            ];
            
            UserProfitHistory::create($profitHistoryData);
            
            echo "  ✅ MATURED SUCCESSFULLY\n";
            echo "  📈 Original: " . number_format($originalShares) . " shares\n";
            echo "  📈 Profit: " . number_format($profit) . " shares\n";
            echo "  📈 New Total: " . number_format($share->total_share_count) . " shares\n";
            
            // Log the maturation (existing logic)
            Log::info('Share manually matured via bulk maturation script: ' . $share->id, [
                'ticket_no' => $share->ticket_no,
                'user_id' => $share->user_id,
                'original_shares' => $originalShares,
                'profit_added' => $profit,
                'final_shares' => $share->total_share_count,
                'matured_at' => $share->matured_at,
                'script_execution' => true
            ]);
            
            $maturedCount++;
            $totalProfitAdded += $profit;
            
        } catch (Exception $e) {
            echo "  ❌ ERROR: " . $e->getMessage() . "\n";
            Log::error('Error maturing share in bulk maturation script', [
                'share_id' => $share->id,
                'ticket_no' => $share->ticket_no,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $errorCount++;
        }
        
        echo "\n";
    }
    
    echo str_repeat("-", 50) . "\n";
    echo "📋 EXECUTION SUMMARY:\n";
    echo "   Total shares found: " . $runningShares->count() . "\n";
    echo "   Successfully matured: {$maturedCount}\n";
    echo "   Errors encountered: {$errorCount}\n";
    echo "   Total profit added: " . number_format($totalProfitAdded) . " shares\n\n";
    
    if ($errorCount > 0) {
        echo "⚠️  WARNING: Some shares encountered errors during maturation.\n";
        echo "   Check the application logs for detailed error information.\n\n";
        
        // Ask for confirmation to proceed with partial success
        echo "Do you want to commit the successful maturations? (y/N): ";
        if (php_sapi_name() === 'cli') {
            $handle = fopen("php://stdin", "r");
            $confirmation = strtolower(trim(fgets($handle)));
            fclose($handle);
        } else {
            $confirmation = 'y'; // Auto-confirm for web execution
        }
        
        if ($confirmation !== 'y' && $confirmation !== 'yes') {
            DB::rollBack();
            echo "❌ CANCELLED: No changes were made to the database.\n";
            exit(1);
        }
    }
    
    DB::commit();
    
    echo "🎉 SUCCESS: All running shares have been matured!\n\n";
    echo "💡 WHAT HAPPENED:\n";
    echo "   - {$maturedCount} shares marked as is_ready_to_sell = 1\n";
    echo "   - Maturation timestamps set to current time\n";
    echo "   - " . number_format($totalProfitAdded) . " profit shares calculated and added\n";
    echo "   - {$maturedCount} profit history records created\n";
    echo "   - Timer states cleared for all processed shares\n";
    echo "   - All operations logged for audit trail\n\n";
    
    echo "🔍 VERIFICATION:\n";
    // Quick verification that shares are now matured
    $nowMaturedShares = UserShare::where('is_ready_to_sell', 1)
        ->whereIn('id', $runningShares->pluck('id'))
        ->count();
    echo "   Verification: {$nowMaturedShares} shares are now marked as ready to sell\n\n";
    
    echo "🎯 IMPACT:\n";
    echo "   - Shares are now available for selling in the market\n";
    echo "   - Users can see their matured shares with profits\n";
    echo "   - No code changes were made to existing logic\n";
    echo "   - All maturation followed existing business rules\n\n";
    
    echo "✅ BULK MATURATION COMPLETED SUCCESSFULLY\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    Log::error('Critical error in bulk maturation script', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    echo "\n❌ TRANSACTION ROLLED BACK - No changes were made.\n";
    exit(1);
}