<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixUserSharesStatusConstraints extends Migration
{
    public function up()
    {
        // Drop the conflicting constraints
        DB::statement('ALTER TABLE user_shares DROP CONSTRAINT IF EXISTS chk_user_share_status');
        DB::statement('ALTER TABLE user_shares DROP CONSTRAINT IF EXISTS chk_valid_status');
        
        // Add a single, comprehensive status constraint that includes all needed statuses
        DB::statement("ALTER TABLE user_shares ADD CONSTRAINT chk_user_share_status_fixed 
            CHECK (status IN ('pending', 'pairing', 'paired', 'partially_paired', 'completed', 'failed', 'sold', 'suspended', 'running', 'active'))");
    }

    public function down()
    {
        // Drop the fixed constraint
        DB::statement('ALTER TABLE user_shares DROP CONSTRAINT IF EXISTS chk_user_share_status_fixed');
        
        // Restore the original constraints (though they were conflicting)
        DB::statement("ALTER TABLE user_shares ADD CONSTRAINT chk_user_share_status 
            CHECK (status IN ('pending','paired','failed','completed','suspended','running','partially_paired','active'))");
            
        DB::statement("ALTER TABLE user_shares ADD CONSTRAINT chk_valid_status 
            CHECK (status IN ('pending','pairing','paired','partially_paired','completed','failed','sold','suspended','running','active'))");
    }
}
