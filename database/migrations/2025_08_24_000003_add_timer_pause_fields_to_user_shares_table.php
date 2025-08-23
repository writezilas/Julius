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
            $table->boolean('timer_paused')->default(false)->after('matured_at');
            $table->timestamp('timer_paused_at')->nullable()->after('timer_paused');
            $table->integer('paused_duration_seconds')->default(0)->after('timer_paused_at');
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
            $table->dropColumn(['timer_paused', 'timer_paused_at', 'paused_duration_seconds']);
        });
    }
};
