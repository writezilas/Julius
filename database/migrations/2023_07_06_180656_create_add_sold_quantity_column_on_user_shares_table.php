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
            $table->integer('hold_quantity')->default(0)->after('total_share_count');
            $table->integer('sold_quantity')->default(0)->after('total_share_count');
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
            $table->integer('hold_quantity')->default(0)->after('total_share_count');
            $table->integer('sold_quantity')->default(0)->after('total_share_count');
        });
    }
};
