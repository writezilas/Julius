<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Services\ShareStatusService;
use App\Services\EnhancedSharePairingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixSystemInconsistencies extends Command
{
    protected $signature = 'system:fix-inconsistencies {--dry-run : Show what would be fixed without making changes}';
    protected $description = 'Fix all identified system inconsistencies in pairing logic, payment deadlines, and share statuses';

    private $shareStatusService;
    private $pairingService;
    private $issues = [];
    private $fixes = [];

    public function __construct()
    {
        parent::__construct();
        $this->shareStatusService = new ShareStatusService();
        $this->pairingService = new EnhancedSharePairingService();
    }

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('🔍 Starting System Inconsistency Fix...');
        $this->newLine();

        if ($dryRun) {
            $this->warn('🧪 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        try {
            if (!$dryRun) {
                DB::beginTransaction();
            }

            // Run all fixes
            $this->fixNegativeQuantities($dryRun);
            $this->fixOrphanedPairings($dryRun);
            $this->fixInconsistentStatuses($dryRun);
            $this->fixSoldShareStatuses($dryRun);
            $this->fixQuantityConservation($dryRun);
            $this->validatePairingConsistency($dryRun);

            if (!$dryRun) {
                DB::commit();
            }

            $this->displaySummary();

            return 0;
        } catch (\Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            
            $this->error('❌ Error during fix process: ' . $e->getMessage());
            Log::error('FixSystemInconsistencies failed: ' . $e->getMessage());
            
            return 1;
        }
    }

    private function fixNegativeQuantities(bool $dryRun): void
    {
        $this->info('🔧 Fixing negative quantities...');
        
        $negativeShares = UserShare::where(function($query) {
            $query->where('total_share_count', '<', 0)
                  ->orWhere('hold_quantity', '<', 0)
                  ->orWhere('sold_quantity', '<', 0);
        })->get();

        if ($negativeShares->isEmpty()) {
            $this->line('✅ No negative quantities found');
            return;
        }

        foreach ($negativeShares as $share) {
            $shareIssues = [];
            
            if ($share->total_share_count < 0) {
                $shareIssues[] = "total_share_count: {$share->total_share_count}";
                if (!$dryRun) {
                    $share->total_share_count = 0;
                }
            }
            
            if ($share->hold_quantity < 0) {
                $shareIssues[] = "hold_quantity: {$share->hold_quantity}";
                if (!$dryRun) {
                    $share->hold_quantity = 0;
                }
            }
            
            if ($share->sold_quantity < 0) {
                $shareIssues[] = "sold_quantity: {$share->sold_quantity}";
                if (!$dryRun) {
                    $share->sold_quantity = 0;
                }
            }

            if (!empty($shareIssues)) {
                $this->issues[] = "Share {$share->ticket_no}: Negative " . implode(', ', $shareIssues);
                
                if (!$dryRun) {
                    $share->save();
                    $this->fixes[] = "Fixed negative quantities for share {$share->ticket_no}";
                }
            }
        }

        $this->line("Found {$negativeShares->count()} shares with negative quantities");
    }

    private function fixOrphanedPairings(bool $dryRun): void
    {
        $this->info('🔧 Fixing orphaned pairings...');

        // Find pairings where buyer share doesn't exist
        $orphanedBuyerPairings = UserSharePair::whereNotIn('user_share_id', 
            UserShare::pluck('id')
        )->get();

        // Find pairings where seller share doesn't exist
        $orphanedSellerPairings = UserSharePair::whereNotIn('paired_user_share_id', 
            UserShare::pluck('id')
        )->get();

        $totalOrphaned = $orphanedBuyerPairings->count() + $orphanedSellerPairings->count();

        if ($totalOrphaned === 0) {
            $this->line('✅ No orphaned pairings found');
            return;
        }

        foreach ($orphanedBuyerPairings as $pairing) {
            $this->issues[] = "Orphaned pairing (missing buyer share): Pairing ID {$pairing->id}";
            if (!$dryRun) {
                $pairing->delete();
                $this->fixes[] = "Deleted orphaned buyer pairing {$pairing->id}";
            }
        }

        foreach ($orphanedSellerPairings as $pairing) {
            $this->issues[] = "Orphaned pairing (missing seller share): Pairing ID {$pairing->id}";
            if (!$dryRun) {
                $pairing->delete();
                $this->fixes[] = "Deleted orphaned seller pairing {$pairing->id}";
            }
        }

        $this->line("Found {$totalOrphaned} orphaned pairings");
    }

    private function fixInconsistentStatuses(bool $dryRun): void
    {
        $this->info('🔧 Fixing inconsistent share statuses...');

        // Fix buyer shares marked as paired without pairings
        $inconsistentBuyers = UserShare::where('status', 'paired')
            ->where('get_from', 'purchase')
            ->whereDoesntHave('pairedShares', function($query) {
                $query->where('is_paid', '!=', 2);
            })
            ->get();

        // Fix seller shares marked as paired without hold quantities
        $inconsistentSellers = UserShare::where('status', 'paired')
            ->where(function($query) {
                $query->where('get_from', '!=', 'purchase')
                      ->orWhereNull('get_from');
            })
            ->where('hold_quantity', '<=', 0)
            ->get();

        $totalInconsistent = $inconsistentBuyers->count() + $inconsistentSellers->count();

        if ($totalInconsistent === 0) {
            $this->line('✅ No status inconsistencies found');
            return;
        }

        foreach ($inconsistentBuyers as $share) {
            $this->issues[] = "Buyer share {$share->ticket_no} marked as paired without active pairings";
            if (!$dryRun) {
                $share->status = 'pending';
                $share->save();
                $this->fixes[] = "Reset buyer share {$share->ticket_no} status to pending";
            }
        }

        foreach ($inconsistentSellers as $share) {
            $this->issues[] = "Seller share {$share->ticket_no} marked as paired without hold quantity";
            if (!$dryRun) {
                $share->status = 'completed';
                $share->save();
                $this->fixes[] = "Reset seller share {$share->ticket_no} status to completed";
            }
        }

        $this->line("Found {$totalInconsistent} shares with inconsistent statuses");
    }

    private function fixSoldShareStatuses(bool $dryRun): void
    {
        $this->info('🔧 Fixing sold share statuses...');

        // Find shares that should be marked as sold
        $shouldBeSold = UserShare::where('total_share_count', 0)
            ->where('hold_quantity', 0)
            ->where('sold_quantity', '>', 0)
            ->whereIn('status', ['completed', 'paired'])
            ->get();

        if ($shouldBeSold->isEmpty()) {
            $this->line('✅ No shares need sold status update');
            return;
        }

        foreach ($shouldBeSold as $share) {
            $this->issues[] = "Share {$share->ticket_no} should be marked as sold (sold: {$share->sold_quantity}, remaining: 0)";
            if (!$dryRun) {
                $share->status = 'sold';
                $share->is_sold = 1;
                $share->save();
                $this->fixes[] = "Updated share {$share->ticket_no} status to sold";
            }
        }

        $this->line("Found {$shouldBeSold->count()} shares that should be marked as sold");
    }

    private function fixQuantityConservation(bool $dryRun): void
    {
        $this->info('🔧 Checking quantity conservation...');

        $inconsistentShares = UserShare::whereRaw('
            ABS((total_share_count + hold_quantity + sold_quantity) - (share_will_get + COALESCE(profit_share, 0))) > 
            GREATEST(1, (share_will_get + COALESCE(profit_share, 0)) * 0.1)
        ')->get();

        if ($inconsistentShares->isEmpty()) {
            $this->line('✅ No quantity conservation issues found');
            return;
        }

        foreach ($inconsistentShares as $share) {
            $expected = $share->share_will_get + ($share->profit_share ?? 0);
            $actual = $share->total_share_count + $share->hold_quantity + $share->sold_quantity;
            
            $this->issues[] = "Share {$share->ticket_no} quantity mismatch - Expected: {$expected}, Actual: {$actual}";
            
            // Log for investigation but don't auto-fix severe discrepancies
            if (!$dryRun) {
                Log::warning('Quantity conservation issue detected', [
                    'share_id' => $share->id,
                    'ticket_no' => $share->ticket_no,
                    'expected' => $expected,
                    'actual' => $actual,
                    'difference' => $actual - $expected
                ]);
            }
        }

        $this->line("Found {$inconsistentShares->count()} shares with quantity conservation issues (logged for review)");
    }

    private function validatePairingConsistency(bool $dryRun): void
    {
        $this->info('🔧 Validating pairing consistency...');

        if ($dryRun) {
            $stats = $this->pairingService->getPairingStatistics();
            
            if (!empty($stats['inconsistent_states'])) {
                foreach ($stats['inconsistent_states'] as $type => $shares) {
                    foreach ($shares as $ticket => $id) {
                        $this->issues[] = "Inconsistent pairing state: {$type} - Share {$ticket} (ID: {$id})";
                    }
                }
            }
        } else {
            $fixed = $this->pairingService->fixInconsistentStates();
            
            if (!empty($fixed['buyers_fixed'])) {
                foreach ($fixed['buyers_fixed'] as $ticket) {
                    $this->fixes[] = "Fixed buyer pairing inconsistency for share {$ticket}";
                }
            }
            
            if (!empty($fixed['sellers_fixed'])) {
                foreach ($fixed['sellers_fixed'] as $ticket) {
                    $this->fixes[] = "Fixed seller pairing inconsistency for share {$ticket}";
                }
            }
        }

        $this->line('✅ Pairing consistency validation completed');
    }

    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('📊 SUMMARY');
        $this->line(str_repeat('=', 50));

        $this->line("🚨 Issues Found: " . count($this->issues));
        if (!empty($this->issues)) {
            foreach (array_slice($this->issues, 0, 10) as $issue) {
                $this->line("  • {$issue}");
            }
            if (count($this->issues) > 10) {
                $this->line("  ... and " . (count($this->issues) - 10) . " more issues");
            }
        }

        $this->newLine();
        $this->line("✅ Fixes Applied: " . count($this->fixes));
        if (!empty($this->fixes)) {
            foreach (array_slice($this->fixes, 0, 10) as $fix) {
                $this->line("  • {$fix}");
            }
            if (count($this->fixes) > 10) {
                $this->line("  ... and " . (count($this->fixes) - 10) . " more fixes");
            }
        }

        $this->newLine();
        
        if ($this->option('dry-run')) {
            $this->warn('🧪 This was a DRY RUN - no changes were made');
            $this->line('Run without --dry-run to apply fixes');
        } else {
            if (count($this->fixes) > 0) {
                $this->info('✅ All fixes have been applied successfully!');
            } else {
                $this->info('✅ No fixes were needed - system is consistent!');
            }
        }

        Log::info('FixSystemInconsistencies completed', [
            'issues_found' => count($this->issues),
            'fixes_applied' => count($this->fixes),
            'dry_run' => $this->option('dry-run')
        ]);
    }
}
