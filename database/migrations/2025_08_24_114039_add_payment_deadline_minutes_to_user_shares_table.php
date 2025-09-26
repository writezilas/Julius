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
            $table->integer('payment_deadline_minutes')->default(60)->after('paused_duration_seconds')
                ->comment('Payment deadline in minutes stored when share is created (unaffected by admin config changes)');
        });
        
        // Update existing records with current admin setting
        try {
            $currentDeadline = \DB::table('general_settings')
                ->where('key', 'bought_time')
                ->value('value') ?? 60;
            
            \DB::table('user_shares')
                ->whereNull('payment_deadline_minutes')
                ->orWhere('payment_deadline_minutes', 0)
                ->update(['payment_deadline_minutes' => $currentDeadline]);
        } catch (\Exception $e) {
            // If general settings table has different structure or issues, use default
            \DB::table('user_shares')
                ->whereNull('payment_deadline_minutes')
                ->orWhere('payment_deadline_minutes', 0)
                ->update(['payment_deadline_minutes' => 60]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_shares', function (Blueprint $table) {
            $table->dropColumn('payment_deadline_minutes');
        });
    }
};
