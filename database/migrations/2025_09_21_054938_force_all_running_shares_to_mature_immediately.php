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
     * Force all currently running shares to mature immediately.
     * This is a data-only operation that does not change any application logic.
     * 
     * Running shares are identified as:
     * - status = 'completed'
     * - is_ready_to_sell = 0 (still in countdown)
     * - matured_at = NULL (haven't matured yet)
     * - selling_started_at is set (timer is running)
     */
    public function up(): void
    {
        $currentTime = now();
        
        // Find all currently running shares
        $runningShares = DB::table('user_shares')
            ->where('status', 'completed')
            ->where('is_ready_to_sell', 0)  // Not yet ready to sell (still running timer)
            ->whereNull('matured_at')       // Haven't matured yet
            ->whereNotNull('selling_started_at') // Have selling phase started
            ->get();
            
        echo "Found {$runningShares->count()} shares currently running with countdown timers.\n";
        
        if ($runningShares->count() === 0) {
            echo "No running shares found. All shares are already matured or not in countdown mode.\n";
            return;
        }
        
        echo "\n=== FORCING IMMEDIATE MATURATION ===\n";
        
        $maturedCount = 0;
        
        foreach ($runningShares as $share) {
            // Calculate what the natural maturity time would have been
            $naturalMaturityTime = date('Y-m-d H:i:s', strtotime($share->selling_started_at . ' + ' . $share->period . ' days'));
            $hoursRemaining = ceil((strtotime($naturalMaturityTime) - time()) / 3600);
            
            // Force immediate maturation by setting matured_at to current time
            // and is_ready_to_sell to 1
            DB::table('user_shares')
                ->where('id', $share->id)
                ->update([
                    'matured_at' => $currentTime,
                    'is_ready_to_sell' => 1,
                    'updated_at' => $currentTime
                ]);
                
            echo "✅ MATURED: Share {$share->id} ({$share->ticket_no}) - was {$hoursRemaining}h from natural maturity\n";
            $maturedCount++;
        }
        
        echo "\n=== SUMMARY ===\n";
        echo "✅ Successfully forced {$maturedCount} shares to mature immediately!\n";
        echo "📊 All shares are now ready to sell (is_ready_to_sell = 1)\n";
        echo "⏰ Timers will no longer display - shares show as 'Share Matured'\n";
        echo "🔧 No application logic was changed - this was a data-only operation\n";
    }

    /**
     * Reverse the migrations.
     * 
     * This would revert the forced maturation, but it's not recommended
     * as it would put shares back into countdown mode with incorrect timing.
     */
    public function down(): void
    {
        echo "WARNING: Rolling back forced maturation is not recommended!\n";
        echo "This would put shares back into countdown mode with potentially incorrect timing.\n";
        echo "If you really need to rollback, you should manually adjust the data.\n";
        
        // Commented out to prevent accidental rollback
        // $forcedMaturedShares = DB::table('user_shares')
        //     ->where('matured_at', $this->getCurrentTimestamp())
        //     ->where('is_ready_to_sell', 1)
        //     ->get();
        
        // foreach ($forcedMaturedShares as $share) {
        //     DB::table('user_shares')
        //         ->where('id', $share->id)
        //         ->update([
        //             'matured_at' => null,
        //             'is_ready_to_sell' => 0,
        //             'updated_at' => now()
        //         ]);
        // }
    }
};
