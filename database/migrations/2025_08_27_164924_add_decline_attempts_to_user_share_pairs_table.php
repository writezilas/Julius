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
        Schema::table('user_share_pairs', function (Blueprint $table) {
            $table->tinyInteger('decline_attempts')->default(0)->after('is_paid')
                  ->comment('Number of times payment has been declined for this pair');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_share_pairs', function (Blueprint $table) {
            $table->dropColumn('decline_attempts');
        });
    }
};
