<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use App\Services\EnhancedTimerManagementService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixSellingTimerStartTimeCommand extends Command
{
    protected $signature = 'timers:fix-selling-start-time 
                            {--ticket= : Fix specific ticket number}
                            {--dry-run : Show what would be fixed without making changes}
                            {--force : Skip confirmation prompts}';

    protected $description = 'Fix selling timers that started before payment confirmation - they should start when seller confirms payment receipt';

    public function handle()
    {
        $this->info('🕐 FIX SELLING TIMER START TIMES - Use selling_started_at correctly');
        $this->info(str_repeat('=', 80));
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');
        $specificTicket = $this->option('ticket');

        if ($isDryRun) {
            $this->warn('🧪 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        try {
            // Find shares where selling timer might have started too early
            $query = UserShare::where('get_from', 'purchase')
                ->where('status', 'completed')
                ->whereNotNull('selling_started_at')
                ->with(['user', 'trade']);

            if ($specificTicket) {
                $query->where('ticket_no', $specificTicket);
            }

            $affectedShares = $query->get();

            if ($affectedShares->isEmpty()) {
                $this->info('✅ No shares found with selling timer issues.');
                return 0;
            }

            $this->info("📊 Found {$affectedShares->count()} share(s) with selling timers to analyze:");
            $this->newLine();

            $sharesNeedingFix = [];
            $enhancedTimerService = new EnhancedTimerManagementService();

            foreach ($affectedShares as $share) {
                $analysis = $this->analyzeSellingTimerStart($share);
                
                if ($analysis['needs_fix']) {
                    $sharesNeedingFix[] = $analysis;
                    
                    $this->line("❌ {$share->ticket_no} - Selling timer started incorrectly");
                    $this->line("   User: {$share->user->username} ({$share->user->name})");
                    $this->line("   Investment: KSH " . number_format($share->amount, 2));
                    $this->line("   Period: {$share->period} days");
                    $this->line("   Payment Confirmed (start_date): {$share->start_date}");
                    $this->line("   Current Selling Started: {$share->selling_started_at}");
                    $this->line("   Should Start From: {$analysis['correct_start_time']}");
                    $this->line("   Issue: {$analysis['issue_description']}");
                    $this->newLine();
                } else {
                    $this->line("✅ {$share->ticket_no} - Selling timer start time is correct");
                }
            }

            if (empty($sharesNeedingFix)) {
                $this->info('🎉 All selling timers have correct start times!');
                return 0;
            }

            $this->warn("📊 SUMMARY:");
            $this->warn("   Shares needing timer adjustment: " . count($sharesNeedingFix));
            $this->newLine();

            if ($isDryRun) {
                $this->info('🧪 This was a DRY RUN - no changes were made');
                $this->line('Run without --dry-run to apply fixes');
                return 0;
            }

            if (!$isForce) {
                if (!$this->confirm("Do you want to fix these selling timer start times?")) {
                    $this->info('❌ Operation cancelled by user.');
                    return 0;
                }
            }

            // Apply fixes
            return $this->applyFixes($sharesNeedingFix, $enhancedTimerService);

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error('FixSellingTimerStartTimeCommand failed: ' . $e->getMessage());
            return 1;
        }
    }

    private function analyzeSellingTimerStart($share)
    {
        $analysis = [
            'share' => $share,
            'needs_fix' => false,
            'correct_start_time' => null,
            'issue_description' => 'OK'
        ];

        // The correct start time should be when payment was confirmed (start_date)
        // not when selling timer was started (selling_started_at)
        
        if (!$share->start_date) {
            $analysis['issue_description'] = 'Missing start_date (payment confirmation time)';
            return $analysis;
        }

        $correctStartTime = Carbon::parse($share->start_date);
        $analysis['correct_start_time'] = $correctStartTime->format('Y-m-d H:i:s');
        
        if (!$share->selling_started_at) {
            $analysis['needs_fix'] = true;
            $analysis['issue_description'] = 'Missing selling_started_at - timer not initialized';
            return $analysis;
        }

        $currentSellingStart = Carbon::parse($share->selling_started_at);

        // Check if selling timer started before payment confirmation
        if ($currentSellingStart->lt($correctStartTime)) {
            $analysis['needs_fix'] = true;
            $analysis['issue_description'] = 'Selling timer started before payment confirmation';
        }
        // Check if selling timer started significantly after payment confirmation (more than 1 hour)
        elseif ($currentSellingStart->diffInHours($correctStartTime) > 1) {
            $analysis['needs_fix'] = true;
            $analysis['issue_description'] = 'Selling timer started too long after payment confirmation';
        }

        return $analysis;
    }

    private function applyFixes($sharesNeedingFix, $enhancedTimerService)
    {
        $this->info('🚀 Applying selling timer fixes...');
        $this->newLine();

        $fixedCount = 0;
        $errorCount = 0;

        DB::beginTransaction();

        try {
            foreach ($sharesNeedingFix as $analysis) {
                $share = $analysis['share'];
                
                $this->line("🔧 Fixing {$share->ticket_no}...");

                try {
                    $correctStartTime = Carbon::parse($analysis['correct_start_time']);
                    
                    // Update selling timer start time to payment confirmation time
                    $share->update([
                        'selling_started_at' => $correctStartTime,
                        'selling_timer_paused' => 0,
                        'selling_timer_paused_at' => null,
                        'selling_paused_duration_seconds' => 0
                    ]);

                    Log::info("Fixed selling timer start time for share {$share->ticket_no}", [
                        'share_id' => $share->id,
                        'user_id' => $share->user_id,
                        'old_selling_started_at' => $share->getOriginal('selling_started_at'),
                        'new_selling_started_at' => $correctStartTime->format('Y-m-d H:i:s'),
                        'payment_confirmed_at' => $share->start_date,
                        'timer_reference' => 'start_date_used_as_selling_started_at'
                    ]);

                    $this->line("   ✅ Fixed successfully");
                    $this->line("   📅 Updated selling start time to: {$correctStartTime->format('Y-m-d H:i:s')}");
                    $this->line("   💡 Selling timer now starts from payment confirmation time");
                    
                    $fixedCount++;

                } catch (\Exception $e) {
                    $this->line("   ❌ Error: " . $e->getMessage());
                    Log::error("Failed to fix selling timer for share {$share->ticket_no}: " . $e->getMessage());
                    $errorCount++;
                }

                $this->newLine();
            }

            DB::commit();

            $this->info('📊 SELLING TIMER FIX COMPLETED:');
            $this->info("   ✅ Successfully fixed: {$fixedCount} shares");
            
            if ($errorCount > 0) {
                $this->error("   ❌ Errors: {$errorCount} shares");
            }

            $this->newLine();
            $this->info('🎉 All selling timer start times have been corrected!');
            $this->info('💡 Selling timers now start from payment confirmation time, not payment submission time');
            $this->info('⏰ Investment periods will be calculated from when seller confirms payment receipt');

            return $fixedCount > 0 ? 0 : 1;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Transaction failed: ' . $e->getMessage());
            Log::error('FixSellingTimerStartTimeCommand transaction failed: ' . $e->getMessage());
            return 1;
        }
    }
}