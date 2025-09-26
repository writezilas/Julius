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
        // Drop the incorrect foreign key constraint
        Schema::table('user_share_payments', function (Blueprint $table) {
            $table->dropForeign('user_share_payments_user_share_pair_id_foreign');
        });
        
        // Add the correct foreign key constraint
        Schema::table('user_share_payments', function (Blueprint $table) {
            $table->foreign('user_share_pair_id')
                  ->references('id')
                  ->on('user_share_pairs')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the correct foreign key constraint
        Schema::table('user_share_payments', function (Blueprint $table) {
            $table->dropForeign(['user_share_pair_id']);
        });
        
        // Add back the incorrect foreign key constraint (to match original state)
        Schema::table('user_share_payments', function (Blueprint $table) {
            $table->foreign('user_share_pair_id')
                  ->references('id')
                  ->on('user_shares')
                  ->onDelete('cascade');
        });
    }
};
