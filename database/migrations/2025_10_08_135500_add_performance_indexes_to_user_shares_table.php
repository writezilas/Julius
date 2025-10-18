<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexesToUserSharesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_shares', function (Blueprint $table) {
            // Add composite index for the most common query pattern in checkAvailableSharePerTrade
            $table->index(['trade_id', 'status', 'is_ready_to_sell', 'total_share_count'], 'idx_trade_availability');
            
            // Add index for user_id filtering (excluding own shares)
            $table->index(['user_id', 'trade_id'], 'idx_user_trade');
            
            // Add index for status filtering
            $table->index(['status'], 'idx_status');
            
            // Add index for share counting operations
            $table->index(['total_share_count'], 'idx_share_count');
            
            // Add index for ready to sell filtering
            $table->index(['is_ready_to_sell'], 'idx_ready_to_sell');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_shares', function (Blueprint $table) {
            $table->dropIndex('idx_trade_availability');
            $table->dropIndex('idx_user_trade');
            $table->dropIndex('idx_status');
            $table->dropIndex('idx_share_count');
            $table->dropIndex('idx_ready_to_sell');
        });
    }
}