<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Make all currently "running" shares become "available" without changing existing logic.
     * 
     * LOGIC: Shares are "running" when status='completed' AND is_ready_to_sell=0
     * They become "available" when is_ready_to_sell=1
     * This migration simply flips that flag for all eligible shares.
     */
    public function up(): void
    {
        echo "=== Making All Running Shares Available ===\n";
        
        // Find all shares that are currently "running"
        $runningShares = DB::table('user_shares')
            ->where('status', 'completed')
            ->where('is_ready_to_sell', 0)
            ->whereNotNull('start_date')
            ->whereNotNull('period')
            ->get();
        
        echo "Found {$runningShares->count()} running shares to make available.\n\n";
        
        if ($runningShares->count() === 0) {
            echo "No running shares found. Migration completed.\n";
            return;
        }
        
        $updatedCount = 0;
        $now = now();
        
        foreach ($runningShares as $share) {
            // Calculate when the share should have matured
            $startDate = \Carbon\Carbon::parse($share->start_date);
            $maturityDate = $startDate->addDays($share->period);
            
            // Set matured_at to either the calculated maturity date or now (whichever is earlier)
            $maturedAt = $maturityDate->isPast() ? $maturityDate : $now;
            
            // Update the share to make it available
            $updated = DB::table('user_shares')
                ->where('id', $share->id)
                ->update([
                    'is_ready_to_sell' => 1,
                    'matured_at' => $maturedAt,
                    'updated_at' => $now
                ]);
            
            if ($updated) {
                echo "✅ Made share {$share->ticket_no} (ID: {$share->id}) available\n";
                echo "   - User ID: {$share->user_id}\n";
                echo "   - Start Date: {$share->start_date}\n";
                echo "   - Period: {$share->period} days\n";
                echo "   - Matured At: {$maturedAt}\n";
                echo "   - Status: Running → Available\n\n";
                $updatedCount++;
            } else {
                echo "⚠️  Failed to update share {$share->ticket_no} (ID: {$share->id})\n";
            }
        }
        
        echo "=== SUMMARY ===\n";
        echo "Total shares processed: {$runningShares->count()}\n";
        echo "Successfully updated: {$updatedCount}\n";
        echo "All running shares are now available for sale!\n";
        
        // Verify the changes
        if ($updatedCount > 0) {
            echo "\n=== VERIFICATION ===\n";
            $availableShares = DB::table('user_shares')
                ->whereIn('id', $runningShares->pluck('id'))
                ->where('is_ready_to_sell', 1)
                ->count();
            echo "Shares now marked as available: {$availableShares}\n";
            
            $stillRunning = DB::table('user_shares')
                ->whereIn('id', $runningShares->pluck('id'))
                ->where('is_ready_to_sell', 0)
                ->count();
            echo "Shares still running: {$stillRunning}\n";
            
            if ($stillRunning === 0) {
                echo "✅ SUCCESS: All shares successfully transitioned to available status!\n";
            } else {
                echo "⚠️  WARNING: {$stillRunning} shares are still in running status\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     * 
     * This would revert all shares back to running status, but we'll make this optional
     * since the user specifically wants them to be available.
     */
    public function down(): void
    {
        echo "=== Reverting: Making Available Shares Back to Running ===\n";
        echo "WARNING: This will revert shares back to running status.\n";
        echo "Are you sure you want to do this? (This migration was run to make shares available)\n";
        
        // Get shares that were modified by this migration (have matured_at set recently)
        $recentlyMatured = DB::table('user_shares')
            ->where('is_ready_to_sell', 1)
            ->where('status', 'completed')
            ->whereNotNull('matured_at')
            ->where('matured_at', '>=', now()->subHours(1)) // Shares matured in last hour
            ->get();
        
        echo "Found {$recentlyMatured->count()} shares to potentially revert.\n";
        
        if ($recentlyMatured->count() === 0) {
            echo "No recently matured shares found. Rollback completed.\n";
            return;
        }
        
        $revertedCount = 0;
        
        foreach ($recentlyMatured as $share) {
            // Only revert if the share hasn't actually reached its natural maturity date
            $startDate = \Carbon\Carbon::parse($share->start_date);
            $naturalMaturityDate = $startDate->addDays($share->period);
            
            // If the share hasn't naturally matured yet, revert it
            if ($naturalMaturityDate->isFuture()) {
                $reverted = DB::table('user_shares')
                    ->where('id', $share->id)
                    ->update([
                        'is_ready_to_sell' => 0,
                        'matured_at' => null,
                        'updated_at' => now()
                    ]);
                
                if ($reverted) {
                    echo "🔄 Reverted share {$share->ticket_no} (ID: {$share->id}) back to running\n";
                    $revertedCount++;
                }
            } else {
                echo "⏭️  Keeping share {$share->ticket_no} (ID: {$share->id}) as available (naturally matured)\n";
            }
        }
        
        echo "\n=== ROLLBACK SUMMARY ===\n";
        echo "Shares reverted to running: {$revertedCount}\n";
        echo "Shares kept as available (naturally matured): " . ($recentlyMatured->count() - $revertedCount) . "\n";
    }
};