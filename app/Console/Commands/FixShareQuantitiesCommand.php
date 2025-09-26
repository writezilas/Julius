<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use App\Models\UserSharePair;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixShareQuantitiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shares:fix-quantities {--dry-run : Show what would be done without making changes} {--share-id= : Fix specific share by ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix share quantity inconsistencies caused by failed buyer share handling';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $shareId = $this->option('share-id');
        
        $this->info('🔍 Analyzing share quantity inconsistencies...');
        
        // Query seller shares that might have inconsistencies
        $query = UserShare::where('is_ready_to_sell', 1)
            ->where('status', '!=', 'sold');
            
        if ($shareId) {
            $query->where('id', $shareId);
        }
            
        $sellerShares = $query->get();
        
        if ($sellerShares->isEmpty()) {
            $this->info('✅ No seller shares found to process.');
            return 0;
        }
        
        $this->info("📊 Found {$sellerShares->count()} seller shares to analyze:");
        $this->newLine();
        
        $inconsistentShares = [];
        $headers = ['Share ID', 'Ticket', 'Owner', 'Current Available', 'Expected Available', 'Difference', 'Action'];
        $rows = [];
        
        foreach ($sellerShares as $sellerShare) {
            // Calculate valid pairings (exclude failed buyer shares)
            $allPairings = UserSharePair::where('paired_user_share_id', $sellerShare->id)->get();
            $validPairedAmount = 0;
            
            foreach ($allPairings as $pairing) {
                $buyerShare = UserShare::find($pairing->user_share_id);
                if ($buyerShare && $buyerShare->status !== 'failed') {
                    $validPairedAmount += $pairing->share;
                }
            }
            
            $totalShares = $sellerShare->share_will_get + ($sellerShare->profit_share ?? 0);
            $expectedAvailable = $totalShares - $validPairedAmount;
            $expectedHold = $validPairedAmount;
            
            $currentAvailable = $sellerShare->total_share_count;
            $difference = $currentAvailable - $expectedAvailable;
            
            $action = 'No change needed';
            if (abs($difference) > 0) {
                $inconsistentShares[] = [
                    'share' => $sellerShare,
                    'expected_available' => $expectedAvailable,
                    'expected_hold' => $expectedHold,
                    'valid_paired_amount' => $validPairedAmount,
                    'difference' => $difference
                ];
                $action = $difference > 0 ? 'Reduce available' : 'Increase available';
            }
            
            $rows[] = [
                $sellerShare->id,
                $sellerShare->ticket_no,
                $sellerShare->user->name ?? 'N/A',
                number_format($currentAvailable),
                number_format($expectedAvailable),
                $difference >= 0 ? '+' . number_format($difference) : number_format($difference),
                $action
            ];
        }
        
        $this->table($headers, $rows);
        $this->newLine();
        
        if (empty($inconsistentShares)) {
            $this->info('✅ No inconsistencies found! All share quantities are correct.');
            return 0;
        }
        
        $this->warn("⚠️  Found {count($inconsistentShares)} shares with quantity inconsistencies.");
        
        if ($isDryRun) {
            $this->warn('🚫 DRY RUN MODE - No changes will be made');
            $this->info('Run without --dry-run to apply fixes');
            return 0;
        }
        
        if (!$this->confirm('Do you want to fix these inconsistencies?')) {
            $this->info('❌ Fix cancelled');
            return 1;
        }
        
        $this->info('🔧 Applying fixes...');
        
        DB::beginTransaction();
        
        try {
            $fixedCount = 0;
            
            foreach ($inconsistentShares as $data) {
                $sellerShare = $data['share'];
                $expectedAvailable = $data['expected_available'];
                $expectedHold = $data['expected_hold'];
                
                $this->line("Fixing {$sellerShare->ticket_no}:");
                $this->line("  Available: {$sellerShare->total_share_count} → {$expectedAvailable}");
                $this->line("  Hold: {$sellerShare->hold_quantity} → {$expectedHold}");
                $this->line("  Sold: {$sellerShare->sold_quantity} → 0");
                
                // Update the quantities
                $sellerShare->total_share_count = $expectedAvailable;
                $sellerShare->hold_quantity = $expectedHold;
                $sellerShare->sold_quantity = 0; // Reset sold quantity as no shares are actually sold yet
                $sellerShare->save();
                
                $fixedCount++;
                
                // Log the fix
                Log::info('Share quantities fixed', [
                    'share_id' => $sellerShare->id,
                    'ticket_no' => $sellerShare->ticket_no,
                    'expected_available' => $expectedAvailable,
                    'expected_hold' => $expectedHold,
                    'valid_paired_amount' => $data['valid_paired_amount'],
                    'processed_by' => 'fix_quantities_command'
                ]);
            }
            
            DB::commit();
            
            $this->newLine();
            $this->info('🎉 Fixes applied successfully!');
            $this->info("📊 Summary:");
            $this->info("  • Shares fixed: {$fixedCount}");
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('❌ Fix failed: ' . $e->getMessage());
            Log::error('Share quantities fix error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
}