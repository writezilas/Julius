<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FixShareStatusConsistency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:share-status-consistency 
                           {--share-id= : Specific share ID to fix}
                           {--simulate : Simulate fixes without making changes}
                           {--find-issues : Find shares with status inconsistencies}
                           {--fix-all : Fix all found inconsistencies}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix share status inconsistencies where sellers show "waiting for payment" despite failed buyer payments';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Starting share status consistency check...');
        
        if ($this->option('share-id')) {
            return $this->fixSpecificShare($this->option('share-id'));
        }
        
        if ($this->option('find-issues')) {
            return $this->findStatusInconsistencies();
        }
        
        if ($this->option('fix-all')) {
            return $this->fixAllInconsistencies();
        }
        
        // Default: find and optionally fix issues
        return $this->findAndFixIssues();
    }
    
    /**
     * Fix a specific share
     */
    private function fixSpecificShare($shareId): int
    {
        $share = UserShare::find($shareId);
        if (!$share) {
            $this->error("❌ Share with ID {$shareId} not found");
            return self::FAILURE;
        }
        
        $this->info("🔍 Analyzing share: {$share->ticket_no} (ID: {$share->id})");
        
        $issues = $this->analyzeShare($share);
        
        if (empty($issues)) {
            $this->info("✅ No issues found with this share");
            return self::SUCCESS;
        }
        
        $this->displayShareAnalysis($share, $issues);
        
        if (!$this->option('simulate') && $this->confirm('Fix these issues?')) {
            return $this->fixShare($share, $issues);
        }
        
        return self::SUCCESS;
    }
    
    /**
     * Find all shares with status inconsistencies
     */
    private function findStatusInconsistencies(): int
    {
        $this->info('🔍 Scanning for shares with status inconsistencies...');
        
        // Find shares that are "paired" or have seller-side status issues
        $problematicShares = UserShare::where('status', 'paired')
            ->orWhere(function($query) {
                $query->where('is_ready_to_sell', 1)
                      ->whereHas('pairedWithThis', function($q) {
                          $q->where('is_paid', 2); // Has failed payments
                      });
            })
            ->with(['pairedWithThis', 'trade'])
            ->get();
        
        $issuesFound = [];
        
        foreach ($problematicShares as $share) {
            $issues = $this->analyzeShare($share);
            if (!empty($issues)) {
                $issuesFound[$share->id] = [
                    'share' => $share,
                    'issues' => $issues
                ];
            }
        }
        
        if (empty($issuesFound)) {
            $this->info("✅ No status inconsistencies found");
            return self::SUCCESS;
        }
        
        $this->warn("⚠️ Found " . count($issuesFound) . " shares with status inconsistencies:");
        
        foreach ($issuesFound as $shareId => $data) {
            $this->info("  - Share ID: {$shareId} ({$data['share']->ticket_no})");
            foreach ($data['issues'] as $issue) {
                $this->warn("    → {$issue}");
            }
        }
        
        return self::SUCCESS;
    }
    
    /**
     * Analyze a specific share for issues
     */
    private function analyzeShare(UserShare $share): array
    {
        $issues = [];
        
        // Get all pairings for this seller share
        $pairings = UserSharePair::where('paired_user_share_id', $share->id)->get();
        
        if ($pairings->isEmpty()) {
            return $issues; // No pairings, no issues to check
        }
        
        $paidCount = $pairings->where('is_paid', 1)->count();
        $unpaidCount = $pairings->where('is_paid', 0)->count(); 
        $failedCount = $pairings->where('is_paid', 2)->count();
        $totalCount = $pairings->count();
        
        $this->info("    Pairings analysis: Total={$totalCount}, Paid={$paidCount}, Unpaid={$unpaidCount}, Failed={$failedCount}");
        
        // Issue 1: Share shows "paired" but has failed payments that should be processed
        if ($share->status === 'paired' && $failedCount > 0) {
            if ($failedCount === $totalCount) {
                $issues[] = "All buyer payments failed but seller status is still 'paired' - should be 'completed' or returned to available";
            } elseif ($unpaidCount === 0 && $paidCount > 0) {
                $issues[] = "Mix of paid and failed payments but seller status should reflect completion of valid payments";
            }
        }
        
        // Issue 2: Share has failed payments but hold_quantity not returned
        if ($failedCount > 0) {
            $failedSharesSum = $pairings->where('is_paid', 2)->sum('share');
            if ($failedSharesSum > 0 && $share->hold_quantity >= $failedSharesSum) {
                $issues[] = "Failed payments exist but {$failedSharesSum} shares still in hold_quantity - should be returned to total_share_count";
            }
        }
        
        // Issue 3: Status doesn't match actual payment situation
        if ($share->is_ready_to_sell == 1) {
            $expectedStatus = $this->calculateCorrectStatus($paidCount, $unpaidCount, $failedCount, $share);
            $currentDisplayStatus = getSoldShareStatus($share);
            
            if ($currentDisplayStatus === 'Paired' && $unpaidCount === 0) {
                $issues[] = "Share displays 'Paired' status but has no unpaid buyers (only paid/failed) - should show correct status";
            }
        }
        
        return $issues;
    }
    
    /**
     * Calculate what the correct status should be
     */
    private function calculateCorrectStatus(int $paidCount, int $unpaidCount, int $failedCount, UserShare $share): string
    {
        if ($paidCount > 0 && $unpaidCount > 0) {
            return 'Partially Paid'; // Mixed paid and unpaid
        } elseif ($paidCount > 0 && $unpaidCount === 0) {
            // All remaining are paid (failed ones processed)
            if ($share->total_share_count == 0 && $share->hold_quantity == 0) {
                return 'Sold';
            } else {
                return 'Partially Sold';
            }
        } elseif ($unpaidCount > 0 && $paidCount === 0 && $failedCount === 0) {
            return 'Paired'; // Only unpaid buyers
        } elseif ($failedCount > 0 && $paidCount === 0 && $unpaidCount === 0) {
            return 'Available'; // All failed, back to available
        } else {
            return 'Available'; // Default
        }
    }
    
    /**
     * Display detailed analysis of a share
     */
    private function displayShareAnalysis(UserShare $share, array $issues): void
    {
        $this->info("\n📊 Share Analysis:");
        $this->table(['Property', 'Value'], [
            ['Share ID', $share->id],
            ['Ticket No', $share->ticket_no],
            ['Status', $share->status],
            ['Total Shares', $share->total_share_count],
            ['Hold Quantity', $share->hold_quantity],
            ['Sold Quantity', $share->sold_quantity],
            ['Ready to Sell', $share->is_ready_to_sell ? 'Yes' : 'No'],
        ]);
        
        $pairings = UserSharePair::where('paired_user_share_id', $share->id)->get();
        if ($pairings->isNotEmpty()) {
            $this->info("\n👥 Buyer Pairings:");
            $pairingData = [];
            foreach ($pairings as $pairing) {
                $statusText = match($pairing->is_paid) {
                    0 => 'Unpaid',
                    1 => 'Paid',
                    2 => 'Failed',
                    default => 'Unknown'
                };
                $pairingData[] = [
                    'Buyer Share ID' => $pairing->user_share_id,
                    'Shares' => $pairing->share,
                    'Payment Status' => $statusText,
                ];
            }
            $this->table(['Buyer Share ID', 'Shares', 'Payment Status'], $pairingData);
        }
        
        $this->warn("\n⚠️ Issues Found:");
        foreach ($issues as $issue) {
            $this->warn("  • {$issue}");
        }
    }
    
    /**
     * Fix issues with a specific share
     */
    private function fixShare(UserShare $share, array $issues): int
    {
        try {
            DB::beginTransaction();
            
            $this->info("🔧 Fixing share {$share->ticket_no}...");
            
            $pairings = UserSharePair::where('paired_user_share_id', $share->id)->get();
            $failedPairings = $pairings->where('is_paid', 2);
            $paidPairings = $pairings->where('is_paid', 1);
            $unpaidPairings = $pairings->where('is_paid', 0);
            
            // Fix 1: Return shares from failed payments to available pool
            $sharesReturned = 0;
            foreach ($failedPairings as $failedPairing) {
                if ($share->hold_quantity >= $failedPairing->share) {
                    $share->hold_quantity -= $failedPairing->share;
                    $share->total_share_count += $failedPairing->share;
                    $sharesReturned += $failedPairing->share;
                    
                    $this->info("  ✅ Returned {$failedPairing->share} shares from failed payment to available pool");
                }
            }
            
            // Fix 2: Update share status based on remaining valid pairings
            $newStatus = $this->calculateCorrectStatus(
                $paidPairings->count(), 
                $unpaidPairings->count(), 
                $failedPairings->count(), 
                $share
            );
            
            // Update the share status if needed
            if ($share->status !== $newStatus && $newStatus !== 'Available') {
                $oldStatus = $share->status;
                
                // Map display status to database status
                $dbStatus = match($newStatus) {
                    'Partially Paid', 'Partially Sold' => 'paired',
                    'Sold' => 'completed',
                    'Available' => 'completed', // Back to available for new buyers
                    default => $share->status
                };
                
                if ($dbStatus !== $share->status) {
                    $share->status = $dbStatus;
                    $this->info("  ✅ Updated status from '{$oldStatus}' to '{$dbStatus}' (displays as '{$newStatus}')");
                }
            }
            
            $share->save();
            
            DB::commit();
            
            $this->info("✅ Successfully fixed share {$share->ticket_no}");
            if ($sharesReturned > 0) {
                $this->info("  📤 Total shares returned to available pool: {$sharesReturned}");
            }
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error fixing share {$share->ticket_no}: " . $e->getMessage());
            Log::error('Share status fix error: ' . $e->getMessage(), [
                'share_id' => $share->id,
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }
    
    /**
     * Find and optionally fix all issues
     */
    private function findAndFixIssues(): int
    {
        $findResult = $this->findStatusInconsistencies();
        
        if ($findResult === self::SUCCESS && $this->confirm('Fix all found inconsistencies?')) {
            return $this->fixAllInconsistencies();
        }
        
        return $findResult;
    }
    
    /**
     * Fix all found inconsistencies
     */
    private function fixAllInconsistencies(): int
    {
        // Implementation would call findStatusInconsistencies and fix each found issue
        $this->info('🔧 Fixing all inconsistencies...');
        
        // This is a placeholder - the actual implementation would iterate through
        // the found issues and fix them one by one
        $this->warn('⚠️ Bulk fix not yet implemented - use --share-id to fix individual shares');
        
        return self::SUCCESS;
    }
}
