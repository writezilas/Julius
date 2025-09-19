<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            // Add separate timer fields for selling phase (investment maturity)
            $table->datetime('selling_started_at')->nullable()->after('matured_at')
                ->comment('When the investment period started (separate from buying phase)');
            
            $table->boolean('selling_timer_paused')->default(false)->after('selling_started_at')
                ->comment('Investment maturity timer pause state (separate from payment timer)');
            
            $table->timestamp('selling_timer_paused_at')->nullable()->after('selling_timer_paused')
                ->comment('When investment maturity timer was paused');
            
            $table->integer('selling_paused_duration_seconds')->default(0)->after('selling_timer_paused_at')
                ->comment('Total seconds investment timer has been paused');
        });

        // Add comments to existing timer fields to clarify their purpose
        Schema::table('user_shares', function (Blueprint $table) {
            // Rename existing timer fields to be more specific about their purpose
            $table->boolean('payment_timer_paused')->default(false)->after('selling_paused_duration_seconds')
                ->comment('Payment deadline timer pause state (for bought shares only)');
            
            $table->timestamp('payment_timer_paused_at')->nullable()->after('payment_timer_paused')
                ->comment('When payment deadline timer was paused');
            
            $table->integer('payment_paused_duration_seconds')->default(0)->after('payment_timer_paused_at')
                ->comment('Total seconds payment timer has been paused');
        });

        // Copy existing timer data to the new payment-specific fields
        DB::statement('UPDATE user_shares SET 
            payment_timer_paused = timer_paused,
            payment_timer_paused_at = timer_paused_at,
            payment_paused_duration_seconds = paused_duration_seconds
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_shares', function (Blueprint $table) {
            $table->dropColumn([
                'selling_started_at',
                'selling_timer_paused', 
                'selling_timer_paused_at',
                'selling_paused_duration_seconds',
                'payment_timer_paused',
                'payment_timer_paused_at', 
                'payment_paused_duration_seconds'
            ]);
        });
    }
};