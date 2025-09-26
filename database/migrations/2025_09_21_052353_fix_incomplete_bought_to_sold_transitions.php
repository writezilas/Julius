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
     * Fix incomplete bought→sold share transitions:
     * 1. Find shares that are 'completed' but missing selling_started_at and matured_at
     * 2. Set selling_started_at to the time when the bought share was completed
     * 3. Calculate matured_at based on selling_started_at + period (not start_date + period)
     * 4. Set proper sold shares state (is_ready_to_sell = 1, is_sold = 0)
     */
    public function up(): void
    {
        // Find shares that completed buying but never transitioned to selling properly
        $incompleteTransitions = DB::table('user_shares')
            ->where('status', 'completed')
            ->whereNull('selling_started_at')
            ->whereNull('matured_at')
            ->get();
            
        echo "Found {$incompleteTransitions->count()} shares with incomplete bought→sold transitions.\n";
            
        foreach ($incompleteTransitions as $share) {
            // When should the selling have started?
            // Use the share's updated_at as the selling start time (when it was marked as completed)
            // Or use current time if updated_at is not reliable
            $sellingStartedAt = $share->updated_at ?: now();
            
            // Calculate when the sold share should mature based on selling_started_at + period
            $maturedAt = date('Y-m-d H:i:s', strtotime($sellingStartedAt . ' + ' . $share->period . ' days'));
            
            // Update the share with proper sold shares transition
            DB::table('user_shares')
                ->where('id', $share->id)
                ->update([
                    'selling_started_at' => $sellingStartedAt,
                    'matured_at' => $maturedAt,
                    'is_ready_to_sell' => 1,  // Mark as ready for selling
                    'is_sold' => 0,           // Not sold yet (will show as "running")
                    'updated_at' => now()
                ]);
                
            echo "Fixed share {$share->id} ({$share->ticket_no}): selling_started_at={$sellingStartedAt}, matured_at={$maturedAt}\n";
        }
        
        if ($incompleteTransitions->count() > 0) {
            echo "\nSUCCESS: Fixed {$incompleteTransitions->count()} incomplete transitions!\n";
            echo "All shares now properly transition from bought→sold with correct timing.\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert shares that were fixed by this migration
        // Find shares that have selling_started_at but status is still 'completed'
        $sharesToRevert = DB::table('user_shares')
            ->where('status', 'completed')
            ->whereNotNull('selling_started_at')
            ->whereNotNull('matured_at')
            ->where('is_ready_to_sell', 1)
            ->where('is_sold', 0)
            ->get();
            
        foreach ($sharesToRevert as $share) {
            DB::table('user_shares')
                ->where('id', $share->id)
                ->update([
                    'selling_started_at' => null,
                    'matured_at' => null,
                    'is_ready_to_sell' => 0,
                    'updated_at' => now()
                ]);
        }
    }
};