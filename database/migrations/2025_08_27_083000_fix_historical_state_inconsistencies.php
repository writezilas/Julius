<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixHistoricalStateInconsistencies extends Migration
{
    public function up()
    {
        Log::info('Starting historical state inconsistency fixes...');
        
        // Fix 1: Buyer shares marked as 'paired' but with no active pairings
        $this->fixBuyerSharesWithoutPairings();
        
        // Fix 2: Seller shares marked as 'paired' but with no hold quantities
        $this->fixSellerSharesWithoutHolds();
        
        // Fix 3: Shares with negative quantities
        $this->fixNegativeQuantities();
        
        // Fix 4: Orphaned pairings (references to non-existent shares)
        $this->removeOrphanedPairings();
        
        // Fix 5: Inconsistent payment states
        $this->fixInconsistentPaymentStates();
        
        // Fix 6: Legacy status values
        $this->fixLegacyStatusValues();
        
        Log::info('Historical state inconsistency fixes completed');
    }

    public function down()
    {
        // This migration only fixes data, no rollback needed
        Log::info('Rollback for historical fixes - no action needed');
    }

    /**
     * Fix buyer shares marked as 'paired' but with no active pairings
     */
    private function fixBuyerSharesWithoutPairings()
    {
        $query = "
            UPDATE user_shares 
            SET status = 'pending',
                updated_at = NOW()
            WHERE status = 'paired' 
            AND get_from = 'purchase'
            AND id NOT IN (
                SELECT DISTINCT user_share_id 
                FROM user_share_pairs 
                WHERE is_paid != 2
            )
        ";
        
        $affected = DB::update($query);
        
        if ($affected > 0) {
            Log::info("Fixed {$affected} buyer shares marked as paired without active pairings");
        }
        
        return $affected;
    }

    /**
     * Fix seller shares marked as 'paired' but with no hold quantities
     */
    private function fixSellerSharesWithoutHolds()
    {
        $query = "
            UPDATE user_shares 
            SET status = 'completed',
                updated_at = NOW()
            WHERE status = 'paired' 
            AND (get_from != 'purchase' OR get_from IS NULL)
            AND (hold_quantity <= 0 OR hold_quantity IS NULL)
        ";
        
        $affected = DB::update($query);
        
        if ($affected > 0) {
            Log::info("Fixed {$affected} seller shares marked as paired without hold quantities");
        }
        
        return $affected;
    }

    /**
     * Fix shares with negative quantities
     */
    private function fixNegativeQuantities()
    {
        $fixes = 0;
        
        // Fix negative total_share_count
        $affected = DB::update("
            UPDATE user_shares 
            SET total_share_count = 0,
                updated_at = NOW()
            WHERE total_share_count < 0
        ");
        $fixes += $affected;
        
        if ($affected > 0) {
            Log::warning("Fixed {$affected} shares with negative total_share_count");
        }
        
        // Fix negative hold_quantity
        $affected = DB::update("
            UPDATE user_shares 
            SET hold_quantity = 0,
                updated_at = NOW()
            WHERE hold_quantity < 0
        ");
        $fixes += $affected;
        
        if ($affected > 0) {
            Log::warning("Fixed {$affected} shares with negative hold_quantity");
        }
        
        // Fix negative sold_quantity
        $affected = DB::update("
            UPDATE user_shares 
            SET sold_quantity = 0,
                updated_at = NOW()
            WHERE sold_quantity < 0
        ");
        $fixes += $affected;
        
        if ($affected > 0) {
            Log::warning("Fixed {$affected} shares with negative sold_quantity");
        }
        
        return $fixes;
    }

    /**
     * Remove orphaned pairings (references to non-existent shares)
     */
    private function removeOrphanedPairings()
    {
        // Remove pairings where buyer share doesn't exist
        $affected1 = DB::delete("
            DELETE FROM user_share_pairs 
            WHERE user_share_id NOT IN (SELECT id FROM user_shares)
        ");
        
        // Remove pairings where seller share doesn't exist
        $affected2 = DB::delete("
            DELETE FROM user_share_pairs 
            WHERE paired_user_share_id NOT IN (SELECT id FROM user_shares)
        ");
        
        $total = $affected1 + $affected2;
        
        if ($total > 0) {
            Log::warning("Removed {$total} orphaned pairings ({$affected1} missing buyer shares, {$affected2} missing seller shares)");
        }
        
        return $total;
    }

    /**
     * Fix inconsistent payment states
     */
    private function fixInconsistentPaymentStates()
    {
        $fixes = 0;
        
        // Fix pairings marked as paid (is_paid=1) but have no payment records
        $pairingsWithoutPayments = DB::select("
            SELECT id FROM user_share_pairs 
            WHERE is_paid = 1 
            AND id NOT IN (
                SELECT DISTINCT user_share_pair_id 
                FROM user_share_payments 
                WHERE status = 'confirmed'
            )
        ");
        
        if (!empty($pairingsWithoutPayments)) {
            $pairingIds = array_column($pairingsWithoutPayments, 'id');
            $affected = DB::update("
                UPDATE user_share_pairs 
                SET is_paid = 0,
                    updated_at = NOW()
                WHERE id IN (" . implode(',', $pairingIds) . ")
            ");
            
            $fixes += $affected;
            Log::warning("Fixed {$affected} pairings marked as paid without payment records");
        }
        
        // Fix payments marked as confirmed but pairings not marked as paid
        $paymentsWithoutPaidPairings = DB::select("
            SELECT p.id, p.user_share_pair_id 
            FROM user_share_payments p
            JOIN user_share_pairs usp ON p.user_share_pair_id = usp.id
            WHERE p.status = 'confirmed' 
            AND usp.is_paid != 1
        ");
        
        if (!empty($paymentsWithoutPaidPairings)) {
            $pairingIds = array_unique(array_column($paymentsWithoutPaidPairings, 'user_share_pair_id'));
            $affected = DB::update("
                UPDATE user_share_pairs 
                SET is_paid = 1,
                    updated_at = NOW()
                WHERE id IN (" . implode(',', $pairingIds) . ")
            ");
            
            $fixes += $affected;
            Log::info("Fixed {$affected} pairings with confirmed payments but not marked as paid");
        }
        
        return $fixes;
    }

    /**
     * Fix legacy status values
     */
    private function fixLegacyStatusValues()
    {
        $statusMappings = [
            'running' => 'paired',
            'partially_paired' => 'paired',
            'active' => 'completed'
        ];
        
        $fixes = 0;
        
        foreach ($statusMappings as $oldStatus => $newStatus) {
            $affected = DB::update("
                UPDATE user_shares 
                SET status = ?,
                    updated_at = NOW()
                WHERE status = ?
            ", [$newStatus, $oldStatus]);
            
            if ($affected > 0) {
                $fixes += $affected;
                Log::info("Updated {$affected} shares from '{$oldStatus}' to '{$newStatus}' status");
            }
        }
        
        return $fixes;
    }

    /**
     * Generate detailed inconsistency report
     */
    private function generateInconsistencyReport()
    {
        $report = [
            'buyer_shares_paired_without_pairings' => 0,
            'seller_shares_paired_without_holds' => 0,
            'shares_with_negative_quantities' => 0,
            'orphaned_pairings' => 0,
            'payment_state_mismatches' => 0,
            'legacy_status_values' => 0
        ];
        
        // Count buyer shares paired without pairings
        $report['buyer_shares_paired_without_pairings'] = DB::scalar("
            SELECT COUNT(*) FROM user_shares 
            WHERE status = 'paired' 
            AND get_from = 'purchase'
            AND id NOT IN (
                SELECT DISTINCT user_share_id 
                FROM user_share_pairs 
                WHERE is_paid != 2
            )
        ");
        
        // Count seller shares paired without holds
        $report['seller_shares_paired_without_holds'] = DB::scalar("
            SELECT COUNT(*) FROM user_shares 
            WHERE status = 'paired' 
            AND (get_from != 'purchase' OR get_from IS NULL)
            AND (hold_quantity <= 0 OR hold_quantity IS NULL)
        ");
        
        // Count shares with negative quantities
        $report['shares_with_negative_quantities'] = DB::scalar("
            SELECT COUNT(*) FROM user_shares 
            WHERE total_share_count < 0 
            OR hold_quantity < 0 
            OR sold_quantity < 0
        ");
        
        // Count orphaned pairings
        $report['orphaned_pairings'] = DB::scalar("
            SELECT COUNT(*) FROM user_share_pairs 
            WHERE user_share_id NOT IN (SELECT id FROM user_shares)
            OR paired_user_share_id NOT IN (SELECT id FROM user_shares)
        ");
        
        // Count payment state mismatches
        $report['payment_state_mismatches'] = DB::scalar("
            SELECT COUNT(*) FROM user_share_pairs 
            WHERE is_paid = 1 
            AND id NOT IN (
                SELECT DISTINCT user_share_pair_id 
                FROM user_share_payments 
                WHERE status = 'confirmed'
            )
        ");
        
        // Count legacy status values
        $report['legacy_status_values'] = DB::scalar("
            SELECT COUNT(*) FROM user_shares 
            WHERE status IN ('running', 'partially_paired', 'active')
        ");
        
        Log::info('Inconsistency Report:', $report);
        
        return $report;
    }
}
