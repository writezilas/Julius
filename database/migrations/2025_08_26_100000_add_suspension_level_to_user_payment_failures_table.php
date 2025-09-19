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
        Schema::table('user_payment_failures', function (Blueprint $table) {
            // Track which suspension level the user is at (1st, 2nd, 3rd time hitting 3 failures)
            $table->integer('suspension_level')->default(0)->after('consecutive_failures');
            
            // Track the exact suspension duration in hours for this suspension
            $table->integer('suspension_duration_hours')->nullable()->after('suspended_at');
            
            // Add index for suspension level queries
            $table->index(['user_id', 'suspension_level']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_payment_failures', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'suspension_level']);
            $table->dropColumn(['suspension_level', 'suspension_duration_hours']);
        });
    }
};
