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
     * Fix the sold shares maturation logic:
     * 1. Set selling_started_at for shares that have matured_at but no selling_started_at
     * 2. Recalculate matured_at based on selling_started_at + period (not start_date + period)
     * 3. Reset is_sold to 0 so these shares show as "running" again with correct maturation
     */
    public function up(): void
    {
        // Find shares that have matured_at but no selling_started_at
        // These are shares that transitioned to sold shares but weren't properly configured
        $sharesToFix = DB::table('user_shares')
            ->whereNotNull('matured_at')
            ->whereNull('selling_started_at')
            ->get();
            
        foreach ($sharesToFix as $share) {
            // Step 1: Set selling_started_at to the original matured_at time
            // This represents when the selling process actually started
            $sellingStartedAt = $share->matured_at;
            
            // Step 2: Calculate the correct matured_at based on selling_started_at + period
            $correctMaturedAt = date('Y-m-d H:i:s', strtotime($sellingStartedAt . ' + ' . $share->period . ' days'));
            
            // Step 3: Update the share with correct selling logic
            DB::table('user_shares')
                ->where('id', $share->id)
                ->update([
                    'selling_started_at' => $sellingStartedAt,
                    'matured_at' => $correctMaturedAt,
                    'is_sold' => 0, // Reset to show as "running" with correct timing
                    'is_ready_to_sell' => 1, // Mark as ready to sell
                    'updated_at' => now()
                ]);
                
            echo "Fixed share {$share->id} ({$share->ticket_no}): selling_started_at={$sellingStartedAt}, new_matured_at={$correctMaturedAt}\n";
        }
        
        echo "Fixed {$sharesToFix->count()} shares to use correct sold shares maturation logic.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the changes by clearing selling_started_at and recalculating matured_at from start_date
        $sharesToRevert = DB::table('user_shares')
            ->whereNotNull('selling_started_at')
            ->get();
            
        foreach ($sharesToRevert as $share) {
            $originalMaturedAt = date('Y-m-d H:i:s', strtotime(($share->start_date ?: $share->created_at) . ' + ' . $share->period . ' days'));
            
            DB::table('user_shares')
                ->where('id', $share->id)
                ->update([
                    'selling_started_at' => null,
                    'matured_at' => $originalMaturedAt,
                    'is_ready_to_sell' => 0,
                    'updated_at' => now()
                ]);
        }
    }
};
