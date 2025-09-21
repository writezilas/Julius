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
     * Fix shares that have is_ready_to_sell = 1 prematurely.
     * The is_ready_to_sell flag should ONLY be 1 when:
     * - selling_started_at + period days <= current_time
     * 
     * For shares that haven't actually matured yet, set is_ready_to_sell = 0
     * so the timer will display properly.
     */
    public function up(): void
    {
        // Find all shares that have selling_started_at but haven't actually matured yet
        $prematureShares = DB::table('user_shares')
            ->where('status', 'completed')
            ->where('is_ready_to_sell', 1)
            ->whereNotNull('selling_started_at')
            ->whereNull('matured_at')
            ->get();
            
        echo "Found {$prematureShares->count()} shares with premature is_ready_to_sell flag.\n";
        
        $fixedCount = 0;
        $alreadyCorrectCount = 0;
        
        foreach ($prematureShares as $share) {
            // Calculate when this share should actually mature
            $actualMaturityTime = strtotime($share->selling_started_at . ' + ' . $share->period . ' days');
            $currentTime = time();
            $hasActuallyMatured = $currentTime >= $actualMaturityTime;
            
            if (!$hasActuallyMatured) {
                // This share has is_ready_to_sell = 1 but hasn't actually matured yet - FIX IT
                DB::table('user_shares')
                    ->where('id', $share->id)
                    ->update([
                        'is_ready_to_sell' => 0,
                        'updated_at' => now()
                    ]);
                    
                $hoursUntilMaturity = ceil(($actualMaturityTime - $currentTime) / 3600);
                echo "❌ FIXED: Share {$share->id} ({$share->ticket_no}) - is_ready_to_sell cleared (still {$hoursUntilMaturity} hours until actual maturity)\n";
                $fixedCount++;
            } else {
                // This share has legitimately matured - leave it as is
                echo "✅ OK: Share {$share->id} ({$share->ticket_no}) - legitimately ready to sell\n";
                $alreadyCorrectCount++;
            }
        }
        
        echo "\n=== SUMMARY ===\n";
        echo "Fixed (premature is_ready_to_sell cleared): {$fixedCount}\n";
        echo "Already correct (legitimately ready): {$alreadyCorrectCount}\n";
        echo "\nNow the timer should display properly for shares that haven't matured!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration corrects incorrect data, so reversing would restore the incorrect state
        echo "This migration fixes incorrect data - no rollback needed.\n";
    }
};
