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
            $table->tinyInteger('is_ready_to_sell')->after('status')->default(0);
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
            $table->tinyInteger('is_ready_to_sell')->after('status')->default(0);
        });
    }
};
