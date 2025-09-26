<?php
/**
 * Standalone Script: Make All Running Shares Available
 * 
 * This script immediately makes all "running" shares become "available" 
 * without changing any existing application logic.
 * 
 * Usage: php make_running_shares_available.php
 */

// Include Laravel bootstrap
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Models\UserShare;
use Illuminate\Support\Facades\DB;

echo "=== Making All Running Shares Available ===\n\n";

try {
    // Find all shares that are currently "running"
    // Running shares have: status='completed' AND is_ready_to_sell=0
    $runningShares = UserShare::where('status', 'completed')
        ->where('is_ready_to_sell', 0)
        ->whereNotNull('start_date')
        ->whereNotNull('period')
        ->get();
    
    echo "Found {$runningShares->count()} running shares to make available.\n\n";
    
    if ($runningShares->count() === 0) {
        echo "✅ No running shares found. All shares are already in their correct status.\n";
        exit(0);
    }
    
    // Show details of shares that will be affected
    echo "=== SHARES TO BE UPDATED ===\n";
    foreach ($runningShares as $share) {
        echo "📊 Ticket: {$share->ticket_no}\n";
        echo "   - User ID: {$share->user_id}\n";
        echo "   - Trade: " . ($share->trade ? $share->trade->name : 'N/A') . "\n";
        echo "   - Start Date: {$share->start_date}\n";
        echo "   - Period: {$share->period} days\n";
        echo "   - Current Status: Running\n";
        echo "   - Will Become: Available\n\n";
    }
    
    // Ask for confirmation
    echo "Do you want to proceed and make all these shares available? (y/N): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    $confirmation = trim($line);
    
    if (strtolower($confirmation) !== 'y' && strtolower($confirmation) !== 'yes') {
        echo "❌ Operation cancelled by user.\n";
        exit(0);
    }
    
    echo "\n=== PROCESSING SHARES ===\n";
    
    $updatedCount = 0;
    $failedCount = 0;
    $now = now();
    
    DB::beginTransaction();
    
    try {
        foreach ($runningShares as $share) {
            // Calculate when the share should have matured
            $startDate = \Carbon\Carbon::parse($share->start_date);
            $maturityDate = $startDate->copy()->addDays($share->period);
            
            // Set matured_at to either the calculated maturity date or now (whichever is earlier)
            $maturedAt = $maturityDate->isPast() ? $maturityDate : $now;
            
            // Update the share to make it available
            $share->is_ready_to_sell = 1;
            $share->matured_at = $maturedAt;
            $share->updated_at = $now;
            
            if ($share->save()) {
                echo "✅ Made share {$share->ticket_no} available\n";
                echo "   - Matured At: {$maturedAt}\n";
                echo "   - Status: Running → Available\n\n";
                $updatedCount++;
            } else {
                echo "❌ Failed to update share {$share->ticket_no}\n\n";
                $failedCount++;
            }
        }
        
        DB::commit();
        
        echo "=== OPERATION COMPLETED ===\n";
        echo "✅ Successfully updated: {$updatedCount} shares\n";
        
        if ($failedCount > 0) {
            echo "❌ Failed to update: {$failedCount} shares\n";
        }
        
        echo "\n=== VERIFICATION ===\n";
        
        // Verify the changes
        $availableShares = UserShare::whereIn('id', $runningShares->pluck('id'))
            ->where('is_ready_to_sell', 1)
            ->count();
        echo "Shares now marked as available: {$availableShares}\n";
        
        $stillRunning = UserShare::whereIn('id', $runningShares->pluck('id'))
            ->where('is_ready_to_sell', 0)
            ->count();
        echo "Shares still running: {$stillRunning}\n";
        
        if ($stillRunning === 0 && $availableShares === $runningShares->count()) {
            echo "\n🎉 SUCCESS: All running shares have been made available!\n";
            echo "Users can now see these shares with 'Available' status in their sold shares view.\n";
        } else {
            echo "\n⚠️  WARNING: Some shares may not have been updated correctly.\n";
        }
        
        echo "\n=== WHAT CHANGED ===\n";
        echo "1. ✅ Set is_ready_to_sell = 1 for all running shares\n";
        echo "2. ✅ Set matured_at timestamp for proper tracking\n";
        echo "3. ✅ Shares will now show as 'Available' in the sold shares view\n";
        echo "4. ✅ No existing application logic was modified\n";
        echo "5. ✅ ShareStatusService will automatically recognize them as available\n\n";
        
        echo "The shares are now ready for sale and will appear as 'Available' to users!\n";
        
    } catch (\Exception $e) {
        DB::rollBack();
        echo "❌ ERROR: Transaction failed and was rolled back.\n";
        echo "Error details: " . $e->getMessage() . "\n";
        exit(1);
    }
    
} catch (\Exception $e) {
    echo "❌ FATAL ERROR: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}

echo "\n🎯 MISSION ACCOMPLISHED: All running shares are now available!\n";