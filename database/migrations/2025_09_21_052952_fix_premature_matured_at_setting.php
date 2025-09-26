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
     * Fix shares that have matured_at set prematurely (before they actually mature).
     * The matured_at field should ONLY be set when:
     * - selling_started_at + period days <= current_time
     * 
     * For shares that haven't actually matured yet, set matured_at to NULL.
     */
    public function up(): void
    {
        // Find all shares that have matured_at set
        $sharesWithMaturedAt = DB::table('user_shares')
            ->whereNotNull('matured_at')
            ->whereNotNull('selling_started_at')
            ->get();
            
        echo "Found {$sharesWithMaturedAt->count()} shares with matured_at set.\n";
        
        $fixedCount = 0;
        $alreadyCorrectCount = 0;
        
        foreach ($sharesWithMaturedAt as $share) {
            // Calculate when this share should actually mature
            $actualMaturityTime = strtotime($share->selling_started_at . ' + ' . $share->period . ' days');
            $currentTime = time();
            $hasActuallyMatured = $currentTime >= $actualMaturityTime;
            
            if (!$hasActuallyMatured) {
                // This share has matured_at set but hasn't actually matured yet - FIX IT
                DB::table('user_shares')
                    ->where('id', $share->id)
                    ->update([
                        'matured_at' => null,
                        'updated_at' => now()
                    ]);
                    
                $hoursUntilMaturity = ceil(($actualMaturityTime - $currentTime) / 3600);
                echo "❌ FIXED: Share {$share->id} ({$share->ticket_no}) - matured_at cleared (still {$hoursUntilMaturity} hours until actual maturity)\n";
                $fixedCount++;
            } else {
                // This share has legitimately matured - leave it as is
                echo "✅ OK: Share {$share->id} ({$share->ticket_no}) - legitimately matured\n";
                $alreadyCorrectCount++;
            }
        }
        
        echo "\n=== SUMMARY ===\n";
        echo "Fixed (premature matured_at cleared): {$fixedCount}\n";
        echo "Already correct (legitimately matured): {$alreadyCorrectCount}\n";
        echo "\nMatured_at should only be set when a share has ACTUALLY completed its maturation period!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration corrects data, so reversing it would be complex and not recommended
        // The previous state was incorrect, so we don't want to revert to it
        echo "This migration fixes incorrect data - no rollback needed.\n";
    }
};
