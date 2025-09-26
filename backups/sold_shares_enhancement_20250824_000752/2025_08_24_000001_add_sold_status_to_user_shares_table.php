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
        // Add 'sold' status to the existing enum
        DB::statement("ALTER TABLE user_shares MODIFY COLUMN status ENUM('pending', 'pairing', 'paired', 'completed', 'failed', 'sold') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove 'sold' status from the enum (revert to original)
        DB::statement("ALTER TABLE user_shares MODIFY COLUMN status ENUM('pending', 'pairing', 'paired', 'completed', 'failed') DEFAULT 'pending'");
    }
};
