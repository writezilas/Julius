<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Add performance optimization indexes
     *
     * @return void
     */
    public function up()
    {
        // Helper function to check if index exists
        $indexExists = function($table, $indexName) {
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
            return count($indexes) > 0;
        };

        // Add indexes for frequently queried columns in user_shares table
        if (!$indexExists('user_shares', 'idx_perf_status_user')) {
            DB::statement('ALTER TABLE user_shares ADD INDEX idx_perf_status_user (status, user_id)');
        }
        
        if (!$indexExists('user_shares', 'idx_perf_maturation')) {
            DB::statement('ALTER TABLE user_shares ADD INDEX idx_perf_maturation (status, is_ready_to_sell)');
        }
        
        if (!$indexExists('user_shares', 'idx_perf_selling_shares')) {
            DB::statement('ALTER TABLE user_shares ADD INDEX idx_perf_selling_shares (is_ready_to_sell, created_at)');
        }
        
        if (!$indexExists('user_shares', 'idx_perf_timer_mgmt')) {
            DB::statement('ALTER TABLE user_shares ADD INDEX idx_perf_timer_mgmt (selling_timer_paused, status)');
        }

        // Add indexes for user_share_pairs table
        if (!$indexExists('user_share_pairs', 'idx_perf_payment_queries')) {
            DB::statement('ALTER TABLE user_share_pairs ADD INDEX idx_perf_payment_queries (is_paid, created_at)');
        }
        
        if (!$indexExists('user_share_pairs', 'idx_perf_pairing_status')) {
            DB::statement('ALTER TABLE user_share_pairs ADD INDEX idx_perf_pairing_status (user_share_id, is_paid)');
        }

        // Add indexes for user_share_payments table
        if (!$indexExists('user_share_payments', 'idx_perf_receiver_status')) {
            DB::statement('ALTER TABLE user_share_payments ADD INDEX idx_perf_receiver_status (status, receiver_id)');
        }
        
        if (!$indexExists('user_share_payments', 'idx_perf_sender_payments')) {
            DB::statement('ALTER TABLE user_share_payments ADD INDEX idx_perf_sender_payments (sender_id, status)');
        }

        // Add indexes for users table
        if (!$indexExists('users', 'idx_perf_user_status_date')) {
            DB::statement('ALTER TABLE users ADD INDEX idx_perf_user_status_date (status, created_at)');
        }
        
        if (!$indexExists('users', 'idx_perf_referral_code')) {
            DB::statement('ALTER TABLE users ADD INDEX idx_perf_referral_code (refferal_code)');
        }

        // Add indexes for logs table for better performance
        if (!$indexExists('logs', 'idx_perf_user_activity')) {
            DB::statement('ALTER TABLE logs ADD INDEX idx_perf_user_activity (user_id, created_at)');
        }
        
        if (!$indexExists('logs', 'idx_perf_log_type')) {
            DB::statement('ALTER TABLE logs ADD INDEX idx_perf_log_type (type(50))');
        }

        // Add composite indexes for notifications
        if (!$indexExists('notifications', 'idx_perf_unread_notifications')) {
            DB::statement('ALTER TABLE notifications ADD INDEX idx_perf_unread_notifications (notifiable_type, notifiable_id, read_at)');
        }

        echo "Database performance indexes added successfully!\n";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the added performance indexes safely
        $dropIndex = function($table, $indexName) {
            try {
                DB::statement("DROP INDEX {$indexName} ON {$table}");
            } catch (\Exception $e) {
                // Index might not exist, continue
            }
        };

        // Drop user_shares indexes
        $dropIndex('user_shares', 'idx_perf_status_user');
        $dropIndex('user_shares', 'idx_perf_maturation');
        $dropIndex('user_shares', 'idx_perf_selling_shares');
        $dropIndex('user_shares', 'idx_perf_timer_mgmt');

        // Drop user_share_pairs indexes
        $dropIndex('user_share_pairs', 'idx_perf_payment_queries');
        $dropIndex('user_share_pairs', 'idx_perf_pairing_status');

        // Drop user_share_payments indexes
        $dropIndex('user_share_payments', 'idx_perf_receiver_status');
        $dropIndex('user_share_payments', 'idx_perf_sender_payments');

        // Drop users indexes
        $dropIndex('users', 'idx_perf_user_status_date');
        $dropIndex('users', 'idx_perf_referral_code');

        // Drop logs indexes
        $dropIndex('logs', 'idx_perf_user_activity');
        $dropIndex('logs', 'idx_perf_log_type');

        // Drop notifications indexes
        $dropIndex('notifications', 'idx_perf_unread_notifications');

        echo "Database performance indexes removed successfully!\n";
    }
};
