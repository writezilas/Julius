<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MatureAllRunningTradesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trades:mature-all-running 
                            {--dry-run : Show what would be updated without making changes} 
                            {--force : Skip confirmation prompts}
                            {--include-all-statuses : Include all share statuses, not just completed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mature ALL currently running trades and make them immediately available in the market (does not affect future trades)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 MATURE ALL RUNNING TRADES');
        $this->info('==============================');
        $this->info('This command will mature ALL currently running shares and make them available for sale.');
        $this->info('Future trades will not be affected - only existing shares will be matured.');
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');
        $includeAllStatuses = $this->option('include-all-statuses');

        // Define which statuses to include
        $statusFilters = $includeAllStatuses 
            ? ['completed', 'running', 'paired', 'pending'] 
            : ['completed'];

        $this->info('📊 CURRENT SYSTEM STATUS:');
        $this->showSystemStatus();

        // Find shares that are currently running (not yet matured)
        $query = UserShare::whereIn('status', $statusFilters)
            ->where('is_ready_to_sell', 0) // Not ready to sell yet
            ->whereNull('matured_at'); // Not already matured

        $sharesToMature = $query->get();
        $sharesToMatureCount = $sharesToMature->count();

        if ($sharesToMatureCount === 0) {
            $this->warn('⚠️  No running shares found that need maturation.');
            $this->info('All shares are already matured and ready for sale in the market.');
            return 0;
        }

        $this->info("📋 Found {$sharesToMatureCount} running share(s) to mature:");
        $this->showSharesToMature($sharesToMature->take(10));

        if ($sharesToMatureCount > 10) {
            $this->info("... and " . ($sharesToMatureCount - 10) . " more shares");
        }

        // Calculate totals
        $totalAmount = $sharesToMature->sum('amount');
        $totalShares = $sharesToMature->sum('share_will_get');

        $this->info("\n💰 FINANCIAL IMPACT:");
        $this->info("   Total Investment Amount: " . formatPrice($totalAmount));
        $this->info("   Total Share Count: " . number_format($totalShares));

        if ($isDryRun) {
            $this->warn("\n🧪 DRY RUN MODE - No changes will be made");
            $this->info("Above shares would be matured if run without --dry-run flag");
            return 0;
        }

        // Confirmation
        if (!$isForce) {
            $this->warn("\n⚠️  WARNING: This action will make ALL running shares immediately available for sale!");
            $this->warn("   This cannot be undone and will affect the market dynamics.");
            
            if (!$this->confirm("\n❓ Do you want to mature ALL {$sharesToMatureCount} running share(s)?")) {
                $this->info('❌ Operation cancelled by user.');
                return 0;
            }
        }

        // Execute the maturation process
        return $this->executeMaturation($sharesToMature);
    }

    /**
     * Show current system status
     */
    private function showSystemStatus()
    {
        $statusSummary = UserShare::select('status')
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(CASE WHEN is_ready_to_sell = 1 THEN 1 ELSE 0 END) as matured_count')
            ->selectRaw('SUM(CASE WHEN is_ready_to_sell = 0 THEN 1 ELSE 0 END) as running_count')
            ->selectRaw('SUM(amount) as total_amount')
            ->groupBy('status')
            ->get();

        $headers = ['Status', 'Total', 'Matured', 'Running', 'Total Amount'];
        $rows = [];

        foreach ($statusSummary as $status) {
            $rows[] = [
                $status->status,
                number_format($status->total_count),
                number_format($status->matured_count),
                number_format($status->running_count),
                formatPrice($status->total_amount ?? 0)
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Show shares that will be matured
     */
    private function showSharesToMature($shares)
    {
        $headers = ['ID', 'Ticket No', 'Status', 'Amount', 'Share Count', 'Start Date'];
        $rows = [];

        foreach ($shares as $share) {
            $rows[] = [
                $share->id,
                $share->ticket_no,
                $share->status,
                formatPrice($share->amount),
                number_format($share->share_will_get ?? 0),
                $share->start_date ? Carbon::parse($share->start_date)->format('Y-m-d H:i') : 'Not Started'
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Execute the maturation process
     */
    private function executeMaturation($sharesToMature)
    {
        $this->info("\n⏳ Processing maturation...");

        $now = Carbon::now();
        $successCount = 0;
        $errorCount = 0;

        // Use transaction to ensure data consistency
        DB::beginTransaction();

        try {
            foreach ($sharesToMature as $share) {
                try {
                    // Calculate profit if needed
                    $profit = $this->calculateProfitOfShare($share);

                    // Update the share
                    $share->update([
                        'is_ready_to_sell' => 1,
                        'matured_at' => $now,
                        'profit_share' => $profit
                    ]);

                    // Create profit history record
                    if ($profit > 0) {
                        \App\Models\UserProfitHistory::create([
                            'user_share_id' => $share->id,
                            'shares' => $profit,
                            'created_at' => $now,
                            'updated_at' => $now
                        ]);
                    }

                    $successCount++;

                    if ($successCount % 10 == 0) {
                        $this->info("   Processed {$successCount} shares...");
                    }

                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error("Failed to mature share {$share->id}: " . $e->getMessage());
                    $this->error("   Failed to mature share {$share->ticket_no}: " . $e->getMessage());
                }
            }

            DB::commit();

            // Show results
            $this->info("\n✅ MATURATION COMPLETED!");
            $this->info("   Successfully matured: {$successCount} shares");
            
            if ($errorCount > 0) {
                $this->error("   Failed to mature: {$errorCount} shares");
            }

            $this->info("   Maturation timestamp: {$now->format('Y-m-d H:i:s')}");

            // Verification
            $this->info("\n🔍 VERIFICATION:");
            $readyToSellCount = UserShare::where('is_ready_to_sell', 1)
                ->whereNotNull('matured_at')
                ->count();
                
            $this->info("   Total shares now ready for sale: {$readyToSellCount}");

            // Show final status
            $this->newLine();
            $this->showSystemStatus();

            $this->info("\n🎉 ALL RUNNING TRADES HAVE BEEN MATURED!");
            $this->info("💰 All matured shares are now immediately available for sale in the market.");
            $this->info("🔄 Future trades will continue to operate normally with their original maturation periods.");

            return $successCount > 0 ? 0 : 1;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\n❌ Transaction failed: " . $e->getMessage());
            Log::error("Maturation process failed: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Calculate profit for a share
     */
    private function calculateProfitOfShare($share)
    {
        try {
            $period = \App\Models\TradePeriod::where('days', $share->period)->first();
            
            if (!$period) {
                return 0;
            }

            return ($period->percentage / 100) * $share->total_share_count;
        } catch (\Exception $e) {
            Log::warning("Failed to calculate profit for share {$share->id}: " . $e->getMessage());
            return 0;
        }
    }
}
