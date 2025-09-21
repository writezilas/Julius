<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddStateConsistencyConstraints extends Migration
{
    public function up()
    {
        // Add constraints and indexes to user_shares table
        Schema::table('user_shares', function (Blueprint $table) {
            // Add status validation constraint (only if it doesn't exist)
            if (!$this->hasConstraint('user_shares', 'chk_user_share_status')) {
                DB::statement("ALTER TABLE user_shares ADD CONSTRAINT chk_user_share_status 
                    CHECK (status IN ('pending', 'paired', 'failed', 'completed', 'suspended', 'running', 'partially_paired', 'active'))");
            }
            
            // Add logical constraints (only if they don't exist)
            if (!$this->hasConstraint('user_shares', 'chk_quantities')) {
                DB::statement("ALTER TABLE user_shares ADD CONSTRAINT chk_quantities 
                    CHECK (total_share_count >= 0 AND hold_quantity >= 0 AND sold_quantity >= 0)");
            }
            
            if (!$this->hasConstraint('user_shares', 'chk_ready_to_sell_logic')) {
                DB::statement("ALTER TABLE user_shares ADD CONSTRAINT chk_ready_to_sell_logic 
                    CHECK ((is_ready_to_sell = 0) OR (is_ready_to_sell = 1 AND status IN ('completed', 'failed')))");
            }
            
            // Add composite indexes for performance (only if they don't exist)
            if (!$this->hasIndex('user_shares', 'idx_status_created')) {
                $table->index(['status', 'created_at'], 'idx_status_created');
            }
            if (!$this->hasIndex('user_shares', 'idx_status_ready_sell')) {
                $table->index(['status', 'is_ready_to_sell'], 'idx_status_ready_sell');
            }
            if (!$this->hasIndex('user_shares', 'idx_status_timer')) {
                $table->index(['status', 'timer_paused'], 'idx_status_timer');
            }
            if (!$this->hasIndex('user_shares', 'idx_user_status')) {
                $table->index(['user_id', 'status'], 'idx_user_status');
            }
            if (!$this->hasIndex('user_shares', 'idx_trade_status')) {
                $table->index(['trade_id', 'status'], 'idx_trade_status');
            }
        });

        // Add constraints to user_share_pairs table
        Schema::table('user_share_pairs', function (Blueprint $table) {
            // Add foreign key constraints if they don't exist
            if (!$this->hasForeignKey('user_share_pairs', 'user_share_pairs_user_share_id_foreign')) {
                $table->foreign('user_share_id')->references('id')->on('user_shares')->onDelete('cascade');
            }
            if (!$this->hasForeignKey('user_share_pairs', 'user_share_pairs_paired_user_share_id_foreign')) {
                $table->foreign('paired_user_share_id')->references('id')->on('user_shares')->onDelete('cascade');
            }
            if (!$this->hasForeignKey('user_share_pairs', 'user_share_pairs_user_id_foreign')) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
            
            // Add payment status constraint (only if it doesn't exist)
            if (!$this->hasConstraint('user_share_pairs', 'chk_payment_status')) {
                DB::statement("ALTER TABLE user_share_pairs ADD CONSTRAINT chk_payment_status 
                    CHECK (is_paid IN (0, 1, 2))"); // 0=unpaid, 1=paid, 2=failed
            }
            
            // Add share quantity constraint (only if it doesn't exist)
            if (!$this->hasConstraint('user_share_pairs', 'chk_share_quantity')) {
                DB::statement("ALTER TABLE user_share_pairs ADD CONSTRAINT chk_share_quantity 
                    CHECK (share > 0)");
            }
            
            // Add indexes for performance (only if they don't exist)
            if (!$this->hasIndex('user_share_pairs', 'idx_user_share_payment')) {
                $table->index(['user_share_id', 'is_paid'], 'idx_user_share_payment');
            }
            if (!$this->hasIndex('user_share_pairs', 'idx_paired_share_payment')) {
                $table->index(['paired_user_share_id', 'is_paid'], 'idx_paired_share_payment');
            }
        });

        // Add constraints to user_share_payments table
        Schema::table('user_share_payments', function (Blueprint $table) {
            // Add status validation constraint (only if it doesn't exist)
            if (!$this->hasConstraint('user_share_payments', 'chk_payment_status_enum')) {
                DB::statement("ALTER TABLE user_share_payments ADD CONSTRAINT chk_payment_status_enum 
                    CHECK (status IN ('pending', 'paid', 'conformed', 'failed'))");
            }
            
            // Add amount constraint (only if it doesn't exist)
            if (!$this->hasConstraint('user_share_payments', 'chk_payment_amount')) {
                DB::statement("ALTER TABLE user_share_payments ADD CONSTRAINT chk_payment_amount 
                    CHECK (amount > 0)");
            }
        });

        // Create state consistency validation function (PostgreSQL only)
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared('
                CREATE OR REPLACE FUNCTION validate_share_pairing_consistency()
                RETURNS TRIGGER AS $$
                DECLARE
                    buyer_status VARCHAR(50);
                    seller_status VARCHAR(50);
                BEGIN
                    -- Get buyer and seller share statuses
                    SELECT status INTO buyer_status FROM user_shares WHERE id = NEW.user_share_id;
                    SELECT status INTO seller_status FROM user_shares WHERE id = NEW.paired_user_share_id;
                    
                    -- Validate pairing consistency
                    IF buyer_status NOT IN (\'pending\', \'paired\') THEN
                        RAISE EXCEPTION \'Invalid buyer share status for pairing: %\', buyer_status;
                    END IF;
                    
                    IF seller_status NOT IN (\'completed\', \'paired\') THEN
                        RAISE EXCEPTION \'Invalid seller share status for pairing: %\', seller_status;
                    END IF;
                    
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
            ');
        }

        // Create trigger for pairing consistency (MySQL version) - SKIPPED for now
        /*
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('
                CREATE TRIGGER validate_pairing_before_insert
                BEFORE INSERT ON user_share_pairs
                FOR EACH ROW
                BEGIN
                    DECLARE buyer_status VARCHAR(50);
                    DECLARE seller_status VARCHAR(50);
                    
                    SELECT status INTO buyer_status FROM user_shares WHERE id = NEW.user_share_id;
                    SELECT status INTO seller_status FROM user_shares WHERE id = NEW.paired_user_share_id;
                    
                    IF buyer_status NOT IN (\'pending\', \'paired\') THEN
                        SIGNAL SQLSTATE \'45000\' SET MESSAGE_TEXT = CONCAT(\'Invalid buyer share status for pairing: \', buyer_status);
                    END IF;
                    
                    IF seller_status NOT IN (\'completed\', \'paired\') THEN
                        SIGNAL SQLSTATE \'45000\' SET MESSAGE_TEXT = CONCAT(\'Invalid seller share status for pairing: \', seller_status);
                    END IF;
                END
            ');
        }
        */
    }

    public function down()
    {
        // Drop triggers
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS validate_pairing_before_insert');
        } else {
            DB::unprepared('DROP TRIGGER IF EXISTS validate_pairing_consistency ON user_share_pairs');
            DB::unprepared('DROP FUNCTION IF EXISTS validate_share_pairing_consistency()');
        }

        // Drop constraints and indexes from user_shares
        Schema::table('user_shares', function (Blueprint $table) {
            $table->dropIndex('idx_status_created');
            $table->dropIndex('idx_status_ready_sell');
            $table->dropIndex('idx_status_timer');
            $table->dropIndex('idx_user_status');
            $table->dropIndex('idx_trade_status');
        });

        DB::statement('ALTER TABLE user_shares DROP CONSTRAINT IF EXISTS chk_user_share_status');
        DB::statement('ALTER TABLE user_shares DROP CONSTRAINT IF EXISTS chk_quantities');
        DB::statement('ALTER TABLE user_shares DROP CONSTRAINT IF EXISTS chk_ready_to_sell_logic');

        // Drop constraints and indexes from user_share_pairs
        Schema::table('user_share_pairs', function (Blueprint $table) {
            $table->dropIndex('idx_user_share_payment');
            $table->dropIndex('idx_paired_share_payment');
        });

        DB::statement('ALTER TABLE user_share_pairs DROP CONSTRAINT IF EXISTS chk_payment_status');
        DB::statement('ALTER TABLE user_share_pairs DROP CONSTRAINT IF EXISTS chk_share_quantity');

        // Drop constraints from user_share_payments
        DB::statement('ALTER TABLE user_share_payments DROP CONSTRAINT IF EXISTS chk_payment_status_enum');
        DB::statement('ALTER TABLE user_share_payments DROP CONSTRAINT IF EXISTS chk_payment_amount');
    }

    /**
     * Check if a foreign key exists
     */
    private function hasForeignKey($table, $key)
    {
        if (DB::getDriverName() === 'mysql') {
            $result = DB::select("
                SELECT COUNT(*) as count 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND TABLE_SCHEMA = ?
            ", [$table, $key, DB::getDatabaseName()]);
            
            return $result[0]->count > 0;
        }
        
        return false; // For other databases, assume we need to add them
    }
    
    /**
     * Check if an index exists
     */
    private function hasIndex($table, $indexName)
    {
        if (DB::getDriverName() === 'mysql') {
            $result = DB::select("
                SELECT COUNT(*) as count 
                FROM INFORMATION_SCHEMA.STATISTICS 
                WHERE TABLE_NAME = ? AND INDEX_NAME = ? AND TABLE_SCHEMA = ?
            ", [$table, $indexName, DB::getDatabaseName()]);
            
            return $result[0]->count > 0;
        }
        
        return false; // For other databases, assume we need to add them
    }
    
    /**
     * Check if a constraint exists
     */
    private function hasConstraint($table, $constraintName)
    {
        if (DB::getDriverName() === 'mysql') {
            $result = DB::select("
                SELECT COUNT(*) as count 
                FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
                WHERE TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND TABLE_SCHEMA = ?
            ", [$table, $constraintName, DB::getDatabaseName()]);
            
            return $result[0]->count > 0;
        }
        
        return false; // For other databases, assume we need to add them
    }
}
