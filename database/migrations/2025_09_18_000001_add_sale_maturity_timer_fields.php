<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to separate sale maturity timer from payment deadline timer
 * 
 * This addresses the confusion between two different timer types:
 * 1. Sale Maturity Timer - for shares to mature and become available for sale
 * 2. Payment Deadline Timer - for buyers to complete payments within time limit
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_shares', function (Blueprint $table) {
            // Add new dedicated fields for Sale Maturity Timer
            $table->boolean('maturity_timer_paused')->default(false)->after('paused_duration_seconds')
                ->comment('Whether the sale maturity timer is paused (admin intervention only)');
            
            $table->timestamp('maturity_timer_paused_at')->nullable()->after('maturity_timer_paused')
                ->comment('When the sale maturity timer was paused');
            
            $table->integer('maturity_paused_duration_seconds')->default(0)->after('maturity_timer_paused_at')
                ->comment('Total seconds the maturity timer has been paused');
            
            $table->string('maturity_pause_reason')->nullable()->after('maturity_paused_duration_seconds')
                ->comment('Reason why maturity timer was paused');
            
            // Add index for efficient queries on maturity timer status
            $table->index(['status', 'is_ready_to_sell', 'maturity_timer_paused'], 'idx_maturity_timer_status');
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
            // Drop the index first
            $table->dropIndex('idx_maturity_timer_status');
            
            // Drop the new maturity timer fields
            $table->dropColumn([
                'maturity_timer_paused',
                'maturity_timer_paused_at', 
                'maturity_paused_duration_seconds',
                'maturity_pause_reason'
            ]);
        });
    }
};