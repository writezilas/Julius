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
        Schema::table('users', function (Blueprint $table) {
            // Add column to store referral bonus amount at the time of registration
            $table->integer('referral_bonus_at_registration')->nullable()->after('ref_amount')
                  ->comment('Stores the referral bonus amount that was active when this user registered');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('referral_bonus_at_registration');
        });
    }
};
