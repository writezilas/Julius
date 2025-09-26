<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, ensure we have no orphaned records
        DB::statement('DELETE ash FROM allocate_share_histories ash
                      LEFT JOIN user_shares us ON ash.user_share_id = us.id
                      WHERE us.id IS NULL');
        
        Schema::table('allocate_share_histories', function (Blueprint $table) {
            // Drop the existing foreign key if it exists
            try {
                $table->dropForeign(['user_share_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
            
            // Add the proper foreign key constraint with CASCADE delete
            $table->foreign('user_share_id')
                  ->references('id')
                  ->on('user_shares')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('allocate_share_histories', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['user_share_id']);
        });
    }
};
