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
     * Fix admin-allocated shares that are missing selling_started_at field.
     * For admin-allocated shares, selling_started_at should be set to start_date
     * because they start their selling/maturity phase immediately when allocated.
     */
    public function up(): void
    {
        // Find admin-allocated shares that are missing selling_started_at
        $adminSharesWithMissingSelling = DB::table('user_shares')
            ->where('get_from', 'allocated-by-admin')
            ->where('status', 'completed')
            ->whereNull('selling_started_at')
            ->whereNotNull('start_date')
            ->get();
            
        echo "Found {$adminSharesWithMissingSelling->count()} admin-allocated shares missing selling_started_at.\n";
        
        $fixedCount = 0;
        
        foreach ($adminSharesWithMissingSelling as $share) {
            // For admin-allocated shares, selling_started_at should equal start_date
            // because they don't go through a buying phase - they start in selling phase
            DB::table('user_shares')
                ->where('id', $share->id)
                ->update([
                    'selling_started_at' => $share->start_date,
                    'updated_at' => now()
                ]);
                
            echo "✅ FIXED: Share {$share->id} ({$share->ticket_no}) - selling_started_at set to {$share->start_date}\n";
            $fixedCount++;
        }
        
        echo "\n=== SUMMARY ===\n";
        echo "Fixed admin-allocated shares: {$fixedCount}\n";
        echo "Admin-allocated shares now have proper selling_started_at for timer functionality!\n";
        
        // Verify the fix
        if ($fixedCount > 0) {
            echo "\n=== VERIFICATION ===\n";
            $verifyShares = DB::table('user_shares')
                ->where('get_from', 'allocated-by-admin')
                ->where('status', 'completed')
                ->whereNotNull('selling_started_at')
                ->whereNull('matured_at')
                ->where('is_ready_to_sell', 0)
                ->get();
                
            foreach ($verifyShares as $share) {
                $maturityTime = strtotime($share->selling_started_at . ' + ' . $share->period . ' days');
                $hoursUntilMaturity = ceil(($maturityTime - time()) / 3600);
                
                echo "Share {$share->ticket_no}: Timer should run with {$hoursUntilMaturity} hours until maturity\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert admin-allocated shares by clearing selling_started_at
        $adminShares = DB::table('user_shares')
            ->where('get_from', 'allocated-by-admin')
            ->where('status', 'completed')
            ->whereNotNull('selling_started_at')
            ->get();
            
        foreach ($adminShares as $share) {
            DB::table('user_shares')
                ->where('id', $share->id)
                ->update([
                    'selling_started_at' => null,
                    'updated_at' => now()
                ]);
        }
        
        echo "Reverted selling_started_at for {$adminShares->count()} admin-allocated shares.\n";
    }
};
