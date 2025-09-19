<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixShareQuantityIssue extends Command
{
    protected $signature = 'shares:fix-quantity-issue';
    protected $description = 'Fix share quantity inconsistencies where sold_quantity exceeds share_will_get';

    public function handle()
    {
        $this->info('Starting share quantity fix...');
        
        try {
            DB::beginTransaction();
            
            // Find shares where sold_quantity > share_will_get
            $inconsistentShares = UserShare::whereRaw('sold_quantity > share_will_get')->get();
            
            $this->info("Found {$inconsistentShares->count()} shares with sold_quantity > share_will_get");
            
            $fixedCount = 0;
            
            foreach ($inconsistentShares as $share) {
                $originalSoldQuantity = $share->sold_quantity;
                $shareWillGet = $share->share_will_get;
                $excessSold = $originalSoldQuantity - $shareWillGet;
                
                // The excess sold quantity should be profit, not actual sold shares
                // Correct the sold_quantity to match the original share_will_get
                $correctedSoldQuantity = $shareWillGet;
                
                // Update the share
                $share->sold_quantity = $correctedSoldQuantity;
                
                // Ensure profit_share is correctly set
                if ($share->profit_share != $excessSold) {
                    $share->profit_share = $excessSold;
                }
                
                $share->save();
                $fixedCount++;
                
                $this->line("Fixed Share ID {$share->id} ({$share->ticket_no}): Sold {$originalSoldQuantity} -> {$correctedSoldQuantity}, Profit: {$share->profit_share}");
                
                Log::info("Fixed share quantity - ID: {$share->id}, Ticket: {$share->ticket_no}, Original sold: {$originalSoldQuantity}, Corrected sold: {$correctedSoldQuantity}, Profit: {$share->profit_share}");
            }
            
            // Now check for shares that should have status 'sold' but don't
            $this->info("\nChecking for shares that should be marked as 'sold'...");
            
            $shouldBeSoldShares = UserShare::where('total_share_count', 0)
                ->where('hold_quantity', 0)
                ->where('sold_quantity', '>', 0)
                ->whereNotIn('status', ['sold'])
                ->get();
                
            $statusFixedCount = 0;
            foreach ($shouldBeSoldShares as $share) {
                $oldStatus = $share->status;
                $share->status = 'sold';
                $share->is_sold = 1;
                $share->save();
                $statusFixedCount++;
                
                $this->line("Updated status for Share ID {$share->id} ({$share->ticket_no}): {$oldStatus} -> sold");
                Log::info("Updated share status - ID: {$share->id}, Ticket: {$share->ticket_no}, Status: {$oldStatus} -> sold");
            }
            
            // Check for any shares with invalid quantities (negative values, etc.)
            $this->info("\nChecking for shares with invalid quantities...");
            
            $invalidShares = UserShare::where(function($query) {
                $query->where('sold_quantity', '<', 0)
                      ->orWhere('total_share_count', '<', 0)
                      ->orWhere('hold_quantity', '<', 0)
                      ->orWhere('share_will_get', '<=', 0);
            })->get();
            
            if ($invalidShares->count() > 0) {
                $this->warn("Found {$invalidShares->count()} shares with invalid quantities:");
                foreach ($invalidShares as $share) {
                    $this->warn("Share ID {$share->id} ({$share->ticket_no}): Will get: {$share->share_will_get}, Sold: {$share->sold_quantity}, Total: {$share->total_share_count}, Hold: {$share->hold_quantity}");
                }
            }
            
            DB::commit();
            
            $this->info("\n✅ Share quantity fix completed successfully!");
            $this->info("📊 Summary:");
            $this->info("   - Fixed quantity inconsistencies: {$fixedCount} shares");
            $this->info("   - Updated statuses: {$statusFixedCount} shares");
            $this->info("   - Invalid shares found: {$invalidShares->count()} shares");
            
            Log::info("Share quantity fix completed - Fixed: {$fixedCount}, Status updated: {$statusFixedCount}, Invalid: {$invalidShares->count()}");
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error fixing share quantities: ' . $e->getMessage());
            Log::error('Share quantity fix failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return 1;
        }
    }
}
