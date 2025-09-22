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
     * Fix the chk_ready_to_sell_logic constraint to include 'sold' as a valid status
     * for is_ready_to_sell = 1. This addresses the constraint violation that occurs
     * when confirming payments and transitioning seller shares to 'sold' status.
     */
    public function up(): void
    {
        try {
            // First, drop the existing constraint if it exists
            DB::statement('ALTER TABLE user_shares DROP CONSTRAINT IF EXISTS chk_ready_to_sell_logic');
            
            // Add the updated constraint that includes 'sold' status
            DB::statement("ALTER TABLE user_shares ADD CONSTRAINT chk_ready_to_sell_logic 
                CHECK ((is_ready_to_sell = 0) OR (is_ready_to_sell = 1 AND status IN ('completed', 'failed', 'sold')))");
            
            echo "✅ Updated chk_ready_to_sell_logic constraint to include 'sold' status\n";
            
        } catch (Exception $e) {
            echo "⚠️ Warning: Could not update constraint: " . $e->getMessage() . "\n";
            echo "This may be normal if the constraint doesn't exist or database doesn't support this syntax.\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            // Drop the updated constraint
            DB::statement('ALTER TABLE user_shares DROP CONSTRAINT IF EXISTS chk_ready_to_sell_logic');
            
            // Restore the original constraint (without 'sold')
            DB::statement("ALTER TABLE user_shares ADD CONSTRAINT chk_ready_to_sell_logic 
                CHECK ((is_ready_to_sell = 0) OR (is_ready_to_sell = 1 AND status IN ('completed', 'failed')))");
                
            echo "✅ Reverted chk_ready_to_sell_logic constraint to original version\n";
            
        } catch (Exception $e) {
            echo "⚠️ Warning: Could not revert constraint: " . $e->getMessage() . "\n";
        }
    }
};