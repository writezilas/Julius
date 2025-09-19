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
     * @return void
     */
    public function up()
    {
        // First, fix existing inconsistent data
        $this->fixExistingData();
        
        // Add composite index to prevent duplicate pairs
        Schema::table('user_share_pairs', function (Blueprint $table) {
            $table->unique(['user_share_id', 'paired_user_share_id'], 'unique_share_pair');
            $table->index(['is_paid', 'created_at'], 'idx_payment_status_created');
        });
        
        // Add indexes for better performance on status queries
        Schema::table('user_shares', function (Blueprint $table) {
            $table->index(['status', 'start_date'], 'idx_status_start_date');
            $table->index(['status', 'is_ready_to_sell'], 'idx_status_ready_sell');
        });
        
        // For MySQL 8.0+, add CHECK constraints (will be ignored in older versions)
        DB::statement("ALTER TABLE user_shares ADD CONSTRAINT chk_paired_has_start_date CHECK (
            status != 'paired' OR start_date IS NOT NULL
        )");
        
        DB::statement("ALTER TABLE user_shares ADD CONSTRAINT chk_valid_status CHECK (
            status IN ('pending', 'pairing', 'paired', 'completed', 'failed', 'sold')
        )");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop constraints first
        DB::statement("ALTER TABLE user_shares DROP CONSTRAINT IF EXISTS chk_paired_has_start_date");
        DB::statement("ALTER TABLE user_shares DROP CONSTRAINT IF EXISTS chk_valid_status");
        
        Schema::table('user_share_pairs', function (Blueprint $table) {
            $table->dropUnique('unique_share_pair');
            $table->dropIndex('idx_payment_status_created');
        });
        
        Schema::table('user_shares', function (Blueprint $table) {
            $table->dropIndex('idx_status_start_date');
            $table->dropIndex('idx_status_ready_sell');
        });
    }
    
    /**
     * Fix existing inconsistent data before applying constraints
     */
    private function fixExistingData()
    {
        // Fix shares that are paired but have no start_date
        DB::statement("
            UPDATE user_shares 
            SET start_date = COALESCE(start_date, created_at)
            WHERE status = 'paired' AND start_date IS NULL
        ");
        
        // Fix inconsistent pair statuses - sync seller shares to paired if buyer is paired
        DB::statement("
            UPDATE user_shares seller_share
            INNER JOIN user_share_pairs usp ON seller_share.id = usp.paired_user_share_id
            INNER JOIN user_shares buyer_share ON usp.user_share_id = buyer_share.id
            SET seller_share.status = 'paired',
                seller_share.start_date = COALESCE(seller_share.start_date, buyer_share.start_date, buyer_share.created_at)
            WHERE buyer_share.status = 'paired' 
            AND seller_share.status != 'paired'
            AND usp.is_paid = 0
        ");
        
        // Fix buyer shares that should be completed (all pairs are paid)
        DB::statement("
            UPDATE user_shares buyer_share
            SET status = 'completed'
            WHERE status = 'paired'
            AND NOT EXISTS (
                SELECT 1 FROM user_share_pairs usp 
                WHERE usp.user_share_id = buyer_share.id 
                AND usp.is_paid = 0
            )
            AND EXISTS (
                SELECT 1 FROM user_share_pairs usp 
                WHERE usp.user_share_id = buyer_share.id
            )
        ");
        
        Log::info('Fixed existing share pairing inconsistencies during migration');
    }
};
