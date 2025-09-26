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
        // First, extend the enum to include both old and new values
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending', 'block', 'suspend', 'fine', 'active', 'suspended', 'blocked') DEFAULT 'active'");
        
        // Now migrate existing user statuses to new standardized values
        DB::statement("UPDATE users SET status = 'active' WHERE status IN ('pending', 'fine')");
        
        // Note: 'block' maps to 'blocked' and 'suspend' maps to 'suspended'
        DB::statement("UPDATE users SET status = 'blocked' WHERE status = 'block'");
        DB::statement("UPDATE users SET status = 'suspended' WHERE status = 'suspend'");
        
        // Finally, update the enum to use only the new values
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active', 'suspended', 'blocked') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to old enum values
        DB::statement("UPDATE users SET status = 'pending' WHERE status = 'active'");
        DB::statement("UPDATE users SET status = 'block' WHERE status = 'blocked'");
        DB::statement("UPDATE users SET status = 'suspend' WHERE status = 'suspended'");
        
        // Restore the old enum
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending', 'block', 'suspend', 'fine') DEFAULT 'pending'");
    }
};
