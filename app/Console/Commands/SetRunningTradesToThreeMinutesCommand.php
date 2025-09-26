<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SetRunningTradesToThreeMinutesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trades:set-running-to-3min 
                            {--dry-run : Show what would be updated without making changes}
                            {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set all currently running trades to mature in exactly 3 minutes (does not affect future trades)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🕐 SET RUNNING TRADES TO 3 MINUTES MATURITY');
        $this->info('===============================================');
        $this->info('This command will modify ALL currently running trades to mature in exactly 3 minutes.');
        $this->info('Future trades will not be affected - only existing running trades will be modified.');
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');

        if ($isDryRun) {
            $this->warn('🧪 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Find all currently running trades
        $runningTrades = UserShare::where('status', 'completed')
            ->where('is_ready_to_sell', 0)
            ->whereNotNull('start_date')
            ->whereNotNull('period')
            ->with('user', 'trade')
            ->get();

        if ($runningTrades->isEmpty()) {
            $this->info('✅ No running trades found that need modification.');
            $this->info('All trades are either already matured or not in running status.');
            return 0;
        }

        $this->info("📋 Found {$runningTrades->count()} running trade(s) to modify:");
        $this->showRunningTrades($runningTrades);

        // Calculate totals
        $totalAmount = $runningTrades->sum('amount');
        $totalShares = $runningTrades->sum('share_will_get');

        $this->info("\n💰 FINANCIAL IMPACT:");
        $this->info("   Total Investment Amount: KSH " . number_format($totalAmount, 2));
        $this->info("   Total Share Count: " . number_format($totalShares));

        if ($isDryRun) {
            $this->warn("\n🧪 DRY RUN MODE - No changes will be made");
            $this->info("Above trades would be modified if run without --dry-run flag");
            $this->showWhatWouldChange($runningTrades);
            return 0;
        }

        // Confirmation
        if (!$isForce) {
            $this->warn("\n⚠️  WARNING: This action will modify ALL running trades to mature in 3 minutes!");
            $this->warn("   This cannot be undone and will immediately affect trade maturity times.");
            $this->info("   The trades will become available for sale in the market in 3 minutes.");
            
            if (!$this->confirm("\n❓ Do you want to modify ALL {$runningTrades->count()} running trade(s) to 3-minute maturity?")) {
                $this->info('❌ Operation cancelled by user.');
                return 0;
            }
        }

        // Execute the modification process
        return $this->executeModification($runningTrades);
    }

    /**
     * Show running trades that will be modified
     */
    private function showRunningTrades($trades)
    {
        $headers = ['Ticket No', 'User', 'Trade', 'Amount', 'Period', 'Started', 'Current Status'];
        $rows = [];

        foreach ($trades as $trade) {
            $startTime = Carbon::parse($trade->start_date);
            $periodDays = $trade->period;
            $originalEnd = $startTime->copy()->addDays($periodDays);
            $timeRemaining = $originalEnd->diffForHumans(Carbon::now(), true);
            
            $rows[] = [
                $trade->ticket_no,
                ($trade->user->username ?? 'Unknown') . " (ID: {$trade->user_id})",
                $trade->trade->name ?? 'Unknown',
                'KSH ' . number_format($trade->amount, 2),
                "{$trade->period} days",
                $startTime->format('Y-m-d H:i:s'),
                "Maturing in {$timeRemaining}"
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Show what changes would be made in dry-run mode
     */
    private function showWhatWouldChange($trades)
    {
        $this->info("\n🔍 PROPOSED CHANGES:");
        $this->info(str_repeat('=', 60));

        $targetTime = Carbon::now()->addMinutes(3);
        
        foreach ($trades as $trade) {
            $startTime = Carbon::parse($trade->start_date);
            $periodDays = $trade->period;
            $originalEnd = $startTime->copy()->addDays($periodDays);
            
            // Calculate new start date that would result in 3-minute maturity
            $newStartDate = $targetTime->copy()->subDays($periodDays);
            
            $this->line("📈 {$trade->ticket_no}:");
            $this->line("   Original Start: {$startTime->format('Y-m-d H:i:s')}");
            $this->line("   Original End:   {$originalEnd->format('Y-m-d H:i:s')}");
            $this->line("   New Start:      {$newStartDate->format('Y-m-d H:i:s')}");
            $this->line("   New End:        {$targetTime->format('Y-m-d H:i:s')} (in 3 minutes)");
            $this->line("   Period:         {$periodDays} days (unchanged)");
            $this->newLine();
        }
    }

    /**
     * Execute the modification process
     */
    private function executeModification($runningTrades)
    {
        $this->info("\n⏳ Processing modifications...");

        $targetTime = Carbon::now()->addMinutes(3);
        $successCount = 0;
        $errorCount = 0;

        // Use transaction to ensure data consistency
        DB::beginTransaction();

        try {
            foreach ($runningTrades as $trade) {
                try {
                    $originalStartDate = Carbon::parse($trade->start_date);
                    $originalPeriodDays = $trade->period;
                    $originalEndTime = $originalStartDate->copy()->addDays($originalPeriodDays);
                    
                    // Calculate new start date that results in maturity in 3 minutes
                    $newStartDate = $targetTime->copy()->subDays($originalPeriodDays);
                    
                    // Update the trade - fix both start_date and selling_started_at
                    $trade->update([
                        'start_date' => $newStartDate->toDateTimeString(),
                        'selling_started_at' => $newStartDate->toDateTimeString(), // Fix for proper maturation
                        // Keep all other timer fields as they are
                        // Don't modify timer_paused, paused_duration_seconds, etc.
                        // This preserves any existing pause states
                    ]);

                    // Log the change
                    Log::info("Trade maturity modified to 3 minutes", [
                        'ticket_no' => $trade->ticket_no,
                        'user_id' => $trade->user_id,
                        'original_start_date' => $originalStartDate->toDateTimeString(),
                        'new_start_date' => $newStartDate->toDateTimeString(),
                        'original_end_time' => $originalEndTime->toDateTimeString(),
                        'new_end_time' => $targetTime->toDateTimeString(),
                        'period_days' => $originalPeriodDays,
                        'command' => 'trades:set-running-to-3min'
                    ]);

                    $successCount++;

                    if ($successCount % 5 == 0) {
                        $this->info("   Processed {$successCount} trades...");
                    }

                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error("Failed to modify trade {$trade->ticket_no}: " . $e->getMessage());
                    $this->error("   Failed to modify trade {$trade->ticket_no}: " . $e->getMessage());
                }
            }

            DB::commit();

            // Show results
            $this->info("\n✅ MODIFICATION COMPLETED!");
            $this->info("   Successfully modified: {$successCount} trades");
            
            if ($errorCount > 0) {
                $this->error("   Failed to modify: {$errorCount} trades");
            }

            $this->info("   Target maturity time: {$targetTime->format('Y-m-d H:i:s')} (in 3 minutes)");

            // Verification
            $this->info("\n🔍 VERIFICATION:");
            $modifiedTrades = UserShare::where('status', 'completed')
                ->where('is_ready_to_sell', 0)
                ->whereNotNull('start_date')
                ->whereNotNull('period')
                ->get();
                
            $this->info("   Total trades still running: {$modifiedTrades->count()}");
            
            // Show countdown for verification
            $this->info("\n⏰ COUNTDOWN VERIFICATION:");
            foreach ($modifiedTrades->take(3) as $trade) {
                $startTime = Carbon::parse($trade->start_date);
                $endTime = $startTime->copy()->addDays($trade->period);
                
                // Account for paused time if any
                if ($trade->timer_paused) {
                    $pausedSeconds = $trade->paused_duration_seconds ?? 0;
                    if ($trade->timer_paused_at) {
                        $pausedSeconds += Carbon::parse($trade->timer_paused_at)->diffInSeconds(Carbon::now());
                    }
                    $endTime = $endTime->addSeconds($pausedSeconds);
                }
                
                $timeRemaining = $endTime->diffForHumans(Carbon::now(), true);
                $this->line("   {$trade->ticket_no}: Maturing in {$timeRemaining}");
            }

            $this->info("\n🎉 ALL RUNNING TRADES HAVE BEEN SET TO 3-MINUTE MATURITY!");
            $this->info("⏰ All modified trades will become available for sale in exactly 3 minutes.");
            $this->info("🔄 Future trades will continue to operate normally with their original maturation periods.");
            $this->info("💡 You can monitor the maturation in the admin panel or by running: php artisan trades:process-mature");

            return $successCount > 0 ? 0 : 1;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\n❌ Transaction failed: " . $e->getMessage());
            Log::error("SetRunningTradesToThreeMinutes process failed: " . $e->getMessage());
            return 1;
        }
    }
}