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
            // Store original status before suspension
            $table->string('status_before_suspension')->nullable()->after('status');
            
            // Add index for suspended shares
            $table->index(['status', 'user_id']);
        });
    }

    /**\n     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_shares', function (Blueprint $table) {
            $table->dropIndex(['status', 'user_id']);
            $table->dropColumn('status_before_suspension');
        });
    }
};
