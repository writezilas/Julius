<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use App\Models\UserSharePair;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupFailedPairingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shares:cleanup-failed-pairings {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup failed buyer-seller pairings and return shares to seller available pool';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('🔍 Analyzing failed pairings...');
        
        // Get all failed pairings
        $failedPairings = UserSharePair::where('is_paid', 2)
            ->with(['buyerShare.user', 'sellerShare.user'])
            ->get();
            
        if ($failedPairings->isEmpty()) {
            $this->info('✅ No failed pairings found to cleanup.');
            return 0;
        }
        
        $this->info("📊 Found {$failedPairings->count()} failed pairings to process:");
        $this->newLine();
        
        $totalSharesReturned = 0;
        $processedSellers = [];
        
        // Display summary table
        $headers = ['Pair ID', 'Seller', 'Buyer', 'Shares', 'Action'];
        $rows = [];
        
        foreach ($failedPairings as $pairing) {
            $sellerShare = $pairing->sellerShare;
            $buyerShare = $pairing->buyerShare;
            
            $rows[] = [
                $pairing->id,
                $sellerShare->user->username . ' (' . $sellerShare->ticket_no . ')',
                $buyerShare->user->username . ' (' . $buyerShare->ticket_no . ')',
                number_format($pairing->share),
                'Return & Remove'
            ];
            
            $totalSharesReturned += $pairing->share;
            $processedSellers[$sellerShare->id] = $sellerShare;
        }
        
        $this->table($headers, $rows);
        $this->newLine();
        $this->info("💰 Total shares to return: " . number_format($totalSharesReturned));
        $this->info("👥 Sellers affected: " . count($processedSellers));
        
        if ($isDryRun) {
            $this->warn('🚫 DRY RUN MODE - No changes will be made');
            $this->info('Run without --dry-run to execute the cleanup');
            return 0;
        }
        
        if (!$this->confirm('Do you want to proceed with the cleanup?')) {
            $this->info('❌ Cleanup cancelled');
            return 1;
        }
        
        $this->info('🔧 Processing cleanup...');
        
        DB::beginTransaction();
        
        try {
            $cleanedPairings = 0;
            $sharesReturned = 0;
            $sellersUpdated = [];
            
            foreach ($failedPairings as $pairing) {
                $sellerShare = $pairing->sellerShare;
                $buyerShare = $pairing->buyerShare;
                
                $this->line("Processing: {$sellerShare->ticket_no} ← {$buyerShare->ticket_no} ({$pairing->share} shares)");
                
                // 1. Return shares to seller's available pool
                $sellerShare->total_share_count += $pairing->share;
                $sellerShare->hold_quantity = max(0, $sellerShare->hold_quantity - $pairing->share);
                $sellerShare->save();
                
                $sharesReturned += $pairing->share;
                $sellersUpdated[$sellerShare->id] = $sellerShare;
                
                // 2. Remove the failed pairing
                $pairing->delete();
                $cleanedPairings++;
                
                $this->info("  ✅ Returned {$pairing->share} shares to {$sellerShare->ticket_no}");
            }
            
            // 3. Update seller statuses to make them available for new matching
            foreach ($sellersUpdated as $sellerShare) {
                // Check if seller has any remaining unpaid pairings
                $unpaidPairings = UserSharePair::where('paired_user_share_id', $sellerShare->id)
                    ->where('is_paid', 0)
                    ->count();
                    
                if ($unpaidPairings > 0) {
                    // Still has unpaid buyers, keep as paired
                    if ($sellerShare->status !== 'paired') {
                        $sellerShare->status = 'paired';
                        $sellerShare->save();
                        $this->info("  📝 Updated {$sellerShare->ticket_no} status to 'paired' (has unpaid buyers)");
                    }
                } else {
                    // No unpaid buyers, set to completed (available for new matching)
                    if ($sellerShare->status !== 'completed') {
                        $sellerShare->status = 'completed';
                        $sellerShare->save();
                        $this->info("  📝 Updated {$sellerShare->ticket_no} status to 'completed' (available for new matching)");
                    }
                }
            }
            
            DB::commit();
            
            $this->newLine();
            $this->info('🎉 Cleanup completed successfully!');
            $this->info("📊 Summary:");
            $this->info("  • Failed pairings removed: {$cleanedPairings}");
            $this->info("  • Total shares returned: " . number_format($sharesReturned));
            $this->info("  • Sellers updated: " . count($sellersUpdated));
            
            // Log the cleanup
            Log::info('Failed pairings cleanup completed', [
                'cleaned_pairings' => $cleanedPairings,
                'shares_returned' => $sharesReturned,
                'sellers_updated' => count($sellersUpdated),
                'processed_by' => 'console_command'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('❌ Cleanup failed: ' . $e->getMessage());
            Log::error('Failed pairings cleanup error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
}
