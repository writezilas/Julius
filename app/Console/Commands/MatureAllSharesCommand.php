<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use Carbon\Carbon;

class MatureAllSharesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shares:mature-all {--dry-run : Show what would be done without making changes} {--force : Force maturation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely mature all eligible shares without affecting future trade logic';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Comprehensive Share Maturation Process');
        $this->info('==========================================');

        $isDryRun = $this->option('dry-run');
        $isForced = $this->option('force');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        // Define all possible statuses that should be checked for maturation
        $eligibleStatuses = ['completed', 'running', 'active'];
        
        $totalProcessed = 0;
        $overallSummary = [];

        foreach ($eligibleStatuses as $status) {
            $this->info("\n📊 Checking shares with status: '{$status}'");
            
            // Query for shares that need maturation
            $query = UserShare::where('status', $status)
                ->where('is_ready_to_sell', 0) // Only shares not ready to sell yet
                ->whereNull('matured_at'); // Only shares not already matured

            $sharesToMatureCount = $query->count();

            if ($sharesToMatureCount === 0) {
                $this->info("   ✓ No '{$status}' shares need maturation");
                continue;
            }

            $this->info("   📈 Found {$sharesToMatureCount} '{$status}' share(s) to mature");

            // Get sample shares to show
            $sampleShares = $query->limit(3)->get(['id', 'ticket_no', 'amount', 'total_share_count']);
            
            if ($sampleShares->count() > 0) {
                $this->info("   📋 Sample shares:");
                foreach ($sampleShares as $share) {
                    $this->line("      - {$share->ticket_no} (ID: {$share->id}) - Amount: " . number_format($share->amount, 2) . " - Shares: " . number_format($share->total_share_count));
                }
                if ($sharesToMatureCount > 3) {
                    $this->line("      ... and " . ($sharesToMatureCount - 3) . " more");
                }
            }

            // Skip actual processing in dry-run mode
            if ($isDryRun) {
                $overallSummary[$status] = $sharesToMatureCount;
                continue;
            }

            // Ask for confirmation (unless forced)
            if (!$isForced) {
                if (!$this->confirm("\n❓ Mature these {$sharesToMatureCount} '{$status}' share(s)?", true)) {
                    $this->info("   ❌ Skipped '{$status}' shares by user choice");
                    continue;
                }
            }

            // Perform the maturation
            $this->info("   ⏳ Processing '{$status}' share maturation...");
            
            $now = Carbon::now();
            $updatedCount = $query->update([
                'is_ready_to_sell' => 1,
                'matured_at' => $now,
                'updated_at' => $now
            ]);

            $this->info("   ✅ Successfully matured {$updatedCount} '{$status}' share(s)!");
            $totalProcessed += $updatedCount;
            $overallSummary[$status] = $updatedCount;
        }

        // Show final summary
        $this->info("\n" . str_repeat('=', 50));
        $this->info('📊 FINAL SUMMARY');
        $this->info(str_repeat('=', 50));

        if ($isDryRun) {
            $this->info('🔍 DRY RUN RESULTS (no changes made):');
            $totalWouldBeProcessed = 0;
            foreach ($overallSummary as $status => $count) {
                if ($count > 0) {
                    $this->info("   📈 {$status}: {$count} shares would be matured");
                    $totalWouldBeProcessed += $count;
                }
            }
            if ($totalWouldBeProcessed === 0) {
                $this->info('   ✓ No shares need maturation - all eligible shares are already matured');
            } else {
                $this->info("   📊 Total that would be processed: {$totalWouldBeProcessed} shares");
                $this->info("   💡 Run without --dry-run to execute the changes");
            }
        } else {
            if ($totalProcessed === 0) {
                $this->info('✓ No shares needed maturation - all eligible shares were already matured');
            } else {
                $this->info("✅ Successfully processed {$totalProcessed} shares total!");
                $this->info('🕐 Maturation timestamp: ' . Carbon::now()->format('Y-m-d H:i:s'));
                
                foreach ($overallSummary as $status => $count) {
                    if ($count > 0) {
                        $this->info("   📈 {$status}: {$count} shares matured");
                    }
                }

                // Verification
                $this->info("\n🔍 Post-maturation verification:");
                $totalMatured = UserShare::whereIn('status', $eligibleStatuses)
                    ->where('is_ready_to_sell', 1)
                    ->whereNotNull('matured_at')
                    ->count();
                    
                $this->info("   📊 Total matured shares now available for sale: {$totalMatured}");
            }
        }

        $this->info("\n🎉 Share maturation process completed!");
        if (!$isDryRun && $totalProcessed > 0) {
            $this->info("💰 All matured shares are now available for sale in the market.");
            $this->info("🔧 Future trade logic remains unchanged - this only updated existing shares.");
        }

        return 0;
    }
}
