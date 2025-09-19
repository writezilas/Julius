<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixMaturationAutomationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shares:fix-maturation-automation 
                            {--dry-run : Show what would be fixed without making changes}
                            {--force : Skip confirmation prompts}
                            {--mature-ready : Also mature shares that are ready but not yet processed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix maturation automation issues by aligning timer fields and processing overdue maturations';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔧 MATURATION AUTOMATION FIXER');
        $this->info('==============================');
        $this->info('This command fixes issues where shares should be matured but automation failed.');
        $this->info('It aligns timer fields and processes overdue maturations.');
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');
        $matureReady = $this->option('mature-ready');

        if ($isDryRun) {
            $this->warn('🧪 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Find shares with misaligned timer fields
        $misalignedShares = $this->findMisalignedTimerShares();
        
        // Find shares that should be mature but aren't
        $overdueShares = $this->findOverdueShares();

        $this->info("📊 ANALYSIS RESULTS:");
        $this->info("   Shares with misaligned timers: {$misalignedShares->count()}");
        $this->info("   Shares overdue for maturation: {$overdueShares->count()}");

        if ($misalignedShares->isEmpty() && $overdueShares->isEmpty()) {
            $this->info('✅ No maturation issues found! All shares are properly aligned.');
            return 0;
        }

        // Show details
        if ($misalignedShares->isNotEmpty()) {
            $this->info("\n🔍 MISALIGNED TIMER SHARES:");
            $this->showMisalignedShares($misalignedShares->take(5));
        }

        if ($overdueShares->isNotEmpty()) {
            $this->info("\n⏰ OVERDUE FOR MATURATION:");
            $this->showOverdueShares($overdueShares->take(5));
        }

        if ($isDryRun) {
            $this->warn("\n🧪 DRY RUN MODE - Above issues would be fixed if run without --dry-run");
            return 0;
        }

        // Confirmation
        if (!$isForce) {
            $totalIssues = $misalignedShares->count() + $overdueShares->count();
            $this->warn("\n⚠️  WARNING: This will fix {$totalIssues} maturation issue(s)!");
            
            if (!$this->confirm("\n❓ Do you want to proceed with the fixes?")) {
                $this->info('❌ Operation cancelled by user.');
                return 0;
            }
        }

        // Execute fixes
        return $this->executeFixes($misalignedShares, $overdueShares, $matureReady);
    }

    /**
     * Find shares with misaligned timer fields
     */
    private function findMisalignedTimerShares()
    {
        return UserShare::where('status', 'completed')
            ->where('get_from', 'purchase')
            ->whereNotNull('start_date')
            ->whereNotNull('selling_started_at')
            ->where('is_ready_to_sell', 0)
            ->get()
            ->filter(function ($share) {
                $startDate = Carbon::parse($share->start_date)->format('Y-m-d H:i:s');
                $sellingStarted = Carbon::parse($share->selling_started_at)->format('Y-m-d H:i:s');
                return $startDate !== $sellingStarted;
            });
    }

    /**
     * Find shares that are overdue for maturation
     */
    private function findOverdueShares()
    {
        return UserShare::where('status', 'completed')
            ->where('is_ready_to_sell', 0)
            ->whereNotNull('start_date')
            ->whereNotNull('period')
            ->get()
            ->filter(function ($share) {
                // Use selling_started_at for purchased shares, start_date for others
                $timerStart = $share->get_from === 'purchase' && $share->selling_started_at 
                    ? Carbon::parse($share->selling_started_at)
                    : Carbon::parse($share->start_date);
                    
                $endTime = $timerStart->copy()->addDays($share->period);
                
                // Account for paused time
                if ($share->get_from === 'purchase') {
                    $pausedSeconds = $share->selling_paused_duration_seconds ?? 0;
                    if ($share->selling_timer_paused && $share->selling_timer_paused_at) {
                        $pausedSeconds += Carbon::parse($share->selling_timer_paused_at)->diffInSeconds(Carbon::now());
                    }
                    $endTime = $endTime->addSeconds($pausedSeconds);
                } else {
                    $pausedSeconds = $share->paused_duration_seconds ?? 0;
                    if ($share->timer_paused && $share->timer_paused_at) {
                        $pausedSeconds += Carbon::parse($share->timer_paused_at)->diffInSeconds(Carbon::now());
                    }
                    $endTime = $endTime->addSeconds($pausedSeconds);
                }
                
                return $endTime->isPast();
            });
    }

    /**
     * Show misaligned shares
     */
    private function showMisalignedShares($shares)
    {
        $headers = ['Ticket', 'start_date', 'selling_started_at', 'Difference'];
        $rows = [];

        foreach ($shares as $share) {
            $startDate = Carbon::parse($share->start_date);
            $sellingStarted = Carbon::parse($share->selling_started_at);
            $diff = $startDate->diffForHumans($sellingStarted, true);
            
            $rows[] = [
                $share->ticket_no,
                $startDate->format('Y-m-d H:i:s'),
                $sellingStarted->format('Y-m-d H:i:s'),
                $diff
            ];
        }

        $this->table($headers, $rows);
        
        if ($shares->count() < $this->findMisalignedTimerShares()->count()) {
            $remaining = $this->findMisalignedTimerShares()->count() - $shares->count();
            $this->line("... and {$remaining} more misaligned shares");
        }
    }

    /**
     * Show overdue shares
     */
    private function showOverdueShares($shares)
    {
        $headers = ['Ticket', 'Timer Start', 'Should Have Matured', 'Overdue By'];
        $rows = [];

        foreach ($shares as $share) {
            $timerStart = $share->get_from === 'purchase' && $share->selling_started_at 
                ? Carbon::parse($share->selling_started_at)
                : Carbon::parse($share->start_date);
                
            $endTime = $timerStart->copy()->addDays($share->period);
            $overdue = $endTime->diffForHumans(Carbon::now(), true);
            
            $rows[] = [
                $share->ticket_no,
                $timerStart->format('Y-m-d H:i:s'),
                $endTime->format('Y-m-d H:i:s'),
                $overdue . ' ago'
            ];
        }

        $this->table($headers, $rows);
        
        if ($shares->count() < $this->findOverdueShares()->count()) {
            $remaining = $this->findOverdueShares()->count() - $shares->count();
            $this->line("... and {$remaining} more overdue shares");
        }
    }

    /**
     * Execute the fixes
     */
    private function executeFixes($misalignedShares, $overdueShares, $matureReady)
    {
        $this->info("\n⏳ Executing fixes...");
        
        $alignedCount = 0;
        $maturedCount = 0;
        $errorCount = 0;

        DB::beginTransaction();

        try {
            // Fix misaligned timer fields
            foreach ($misalignedShares as $share) {
                try {
                    $share->update([
                        'selling_started_at' => $share->start_date
                    ]);
                    
                    Log::info("Fixed misaligned timer for share {$share->ticket_no}", [
                        'share_id' => $share->id,
                        'start_date' => $share->start_date,
                        'old_selling_started_at' => $share->selling_started_at,
                        'command' => 'shares:fix-maturation-automation'
                    ]);
                    
                    $alignedCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error("Failed to fix misaligned timer for share {$share->ticket_no}: " . $e->getMessage());
                }
            }

            // Process overdue maturations if requested
            if ($matureReady) {
                foreach ($overdueShares as $share) {
                    try {
                        $profit = $this->calculateProfitOfShare($share);
                        
                        $share->update([
                            'is_ready_to_sell' => 1,
                            'matured_at' => Carbon::now()->format('Y/m/d H:i:s'),
                            'profit_share' => $profit,
                            'total_share_count' => $share->total_share_count + $profit,
                            // Reset timer states
                            'timer_paused' => 0,
                            'timer_paused_at' => null,
                            'selling_timer_paused' => 0,
                            'selling_timer_paused_at' => null
                        ]);

                        // Create profit history
                        if ($profit > 0) {
                            \App\Models\UserProfitHistory::create([
                                'user_share_id' => $share->id,
                                'shares' => $profit,
                            ]);
                        }

                        Log::info("Matured overdue share {$share->ticket_no}", [
                            'share_id' => $share->id,
                            'user_id' => $share->user_id,
                            'profit_added' => $profit,
                            'command' => 'shares:fix-maturation-automation'
                        ]);
                        
                        $maturedCount++;
                    } catch (\Exception $e) {
                        $errorCount++;
                        Log::error("Failed to mature overdue share {$share->ticket_no}: " . $e->getMessage());
                    }
                }
            }

            DB::commit();

            // Show results
            $this->info("\n✅ FIXES COMPLETED!");
            $this->info("   Timer fields aligned: {$alignedCount}");
            if ($matureReady) {
                $this->info("   Overdue shares matured: {$maturedCount}");
            } else {
                $this->warn("   Overdue shares found but not matured (use --mature-ready to process them)");
            }
            
            if ($errorCount > 0) {
                $this->error("   Errors encountered: {$errorCount}");
            }

            $this->info("\n🎯 RECOMMENDATION:");
            $this->info("   Run 'php artisan sharematured:cron' to process any newly aligned shares.");
            
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\n❌ Transaction failed: " . $e->getMessage());
            Log::error("FixMaturationAutomation process failed: " . $e->getMessage());
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