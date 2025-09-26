<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing user statuses from old to new values
        
        // pending -> active
        DB::table('users')
            ->where('status', 'pending')
            ->update(['status' => 'active']);
            
        // fine -> active  
        DB::table('users')
            ->where('status', 'fine')
            ->update(['status' => 'active']);
            
        // suspend -> suspended
        DB::table('users')
            ->where('status', 'suspend')
            ->update(['status' => 'suspended']);
            
        // block -> blocked
        DB::table('users')
            ->where('status', 'block')
            ->update(['status' => 'blocked']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to old status values
        
        DB::table('users')
            ->where('status', 'active')
            ->update(['status' => 'pending']); // Use 'pending' as the primary old active status
            
        DB::table('users')
            ->where('status', 'suspended')
            ->update(['status' => 'suspend']);
            
        DB::table('users')
            ->where('status', 'blocked')
            ->update(['status' => 'block']);
    }
};
