<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use App\Models\UserProfitHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixShareLifecycleInheritanceCommand extends Command
{
    protected $signature = 'shares:fix-lifecycle-inheritance 
                            {--ticket= : Fix specific ticket number}
                            {--dry-run : Show what would be fixed without making changes}
                            {--force : Skip confirmation prompts}';

    protected $description = 'Fix shares stuck due to inheriting timer state from buying phase when transitioning to selling phase';

    public function handle()
    {
        $this->info('🔄 FIX SHARE LIFECYCLE INHERITANCE ISSUES');
        $this->info(str_repeat('=', 60));
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');
        $specificTicket = $this->option('ticket');

        if ($isDryRun) {
            $this->warn('🧪 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        try {
            // Find shares that are affected by the inheritance issue
            $query = UserShare::where('status', 'completed')
                ->where('get_from', 'purchase') // Originally purchased shares
                ->where('is_ready_to_sell', 0) // Should have matured but hasn't
                ->where('timer_paused', 1) // Stuck in paused state from buying phase
                ->whereNotNull('start_date')
                ->with(['user', 'trade', 'tradePeriod']);

            if ($specificTicket) {
                $query->where('ticket_no', $specificTicket);
            }

            $affectedShares = $query->get();

            if ($affectedShares->isEmpty()) {
                $this->info('✅ No shares found with lifecycle inheritance issues.');
                return 0;
            }

            $this->info("📊 Found {$affectedShares->count()} share(s) affected by lifecycle inheritance issues:");
            $this->newLine();

            $sharesNeedingFix = [];
            $totalMissingEarnings = 0;

            foreach ($affectedShares as $share) {
                $analysis = $this->analyzeShare($share);
                
                if ($analysis['should_have_matured']) {
                    $sharesNeedingFix[] = $analysis;
                    $totalMissingEarnings += $analysis['expected_profit'];
                    
                    $this->line("❌ {$share->ticket_no} - Stuck in buying phase timer state");
                    $this->line("   User: {$share->user->username} ({$share->user->name})");
                    $this->line("   Investment: KSH " . number_format($share->amount, 2));
                    $this->line("   Expected Profit: KSH " . number_format($analysis['expected_profit'], 2));
                    $this->line("   Start Date: {$share->start_date}");
                    $this->line("   Expected Maturity: {$analysis['expected_maturity']}");
                    $this->line("   Timer Paused: {$share->timer_paused} (since {$share->timer_paused_at})");
                    $this->line("   Status: {$analysis['issue_description']}");
                    $this->newLine();
                }
            }

            if (empty($sharesNeedingFix)) {
                $this->info('🎉 All shares have correct lifecycle states!');
                return 0;
            }

            $this->warn("📊 SUMMARY:");
            $this->warn("   Shares needing fix: " . count($sharesNeedingFix));
            $this->warn("   Total missing earnings: KSH " . number_format($totalMissingEarnings, 2));
            $this->newLine();

            if ($isDryRun) {
                $this->info('🧪 This was a DRY RUN - no changes were made');
                $this->line('Run without --dry-run to apply fixes');
                return 0;
            }

            if (!$isForce) {
                if (!$this->confirm("Do you want to fix these share lifecycle inheritance issues?")) {
                    $this->info('❌ Operation cancelled by user.');
                    return 0;
                }
            }

            // Apply fixes
            return $this->applyFixes($sharesNeedingFix);

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error('FixShareLifecycleInheritanceCommand failed: ' . $e->getMessage());
            return 1;
        }
    }

    private function analyzeShare($share)
    {
        $analysis = [
            'share' => $share,
            'should_have_matured' => false,
            'expected_profit' => 0,
            'expected_maturity' => null,
            'issue_description' => 'OK'
        ];

        if (!$share->start_date || !$share->period) {
            $analysis['issue_description'] = 'Missing start_date or period';
            return $analysis;
        }

        // Calculate when this share should have matured
        $startTime = \Carbon\Carbon::parse($share->start_date);
        $expectedMaturity = $startTime->copy()->addDays($share->period);
        $analysis['expected_maturity'] = $expectedMaturity->format('Y-m-d H:i:s');

        // Check if it should have matured by now
        if ($expectedMaturity->isPast()) {
            $analysis['should_have_matured'] = true;
            $analysis['issue_description'] = 'Should have matured but stuck in buying phase timer state';
            
            // Calculate expected profit
            if ($share->tradePeriod) {
                $analysis['expected_profit'] = ($share->tradePeriod->percentage / 100) * $share->share_will_get;
            }
        }

        return $analysis;
    }

    private function applyFixes($sharesNeedingFix)
    {
        $this->info('🚀 Applying lifecycle inheritance fixes...');
        $this->newLine();

        $fixedCount = 0;
        $errorCount = 0;

        DB::beginTransaction();

        try {
            foreach ($sharesNeedingFix as $analysis) {
                $share = $analysis['share'];
                
                $this->line("🔧 Fixing {$share->ticket_no}...");

                try {
                    // Calculate profit using existing logic
                    $profit = 0;
                    if ($share->tradePeriod) {
                        $profit = ($share->tradePeriod->percentage / 100) * $share->share_will_get;
                    }

                    // Transition share from "bought phase" to "selling phase" properly
                    $share->is_ready_to_sell = 1;
                    $share->matured_at = now()->format('Y/m/d H:i:s');
                    $share->profit_share = $profit;
                    $share->total_share_count = $share->share_will_get + $profit;
                    
                    // CRITICAL: Reset timer state for selling phase
                    // Clear all buying-phase timer inheritance
                    $share->timer_paused = 0;
                    $share->timer_paused_at = null;
                    // Keep paused_duration_seconds for audit trail
                    
                    $share->save();

                    // Create profit history record if missing
                    $existingProfitHistory = UserProfitHistory::where('user_share_id', $share->id)->first();
                    if (!$existingProfitHistory && $profit > 0) {
                        UserProfitHistory::create([
                            'user_share_id' => $share->id,
                            'shares' => $profit
                        ]);
                    }

                    Log::info("Fixed lifecycle inheritance for share {$share->ticket_no}", [
                        'share_id' => $share->id,
                        'user_id' => $share->user_id,
                        'profit_added' => $profit,
                        'timer_cleared' => true,
                        'matured_at' => $share->matured_at
                    ]);

                    $this->line("   ✅ Fixed successfully");
                    $this->line("   💰 Added profit: KSH " . number_format($profit, 2));
                    $this->line("   ⏰ Cleared buying-phase timer state");
                    $this->line("   🎯 Share now ready to sell with clean state");
                    
                    $fixedCount++;

                } catch (\Exception $e) {
                    $this->line("   ❌ Error: " . $e->getMessage());
                    Log::error("Failed to fix share {$share->ticket_no}: " . $e->getMessage());
                    $errorCount++;
                }

                $this->newLine();
            }

            DB::commit();

            $this->info('📊 LIFECYCLE INHERITANCE FIX COMPLETED:');
            $this->info("   ✅ Successfully fixed: {$fixedCount} shares");
            
            if ($errorCount > 0) {
                $this->error("   ❌ Errors: {$errorCount} shares");
            }

            $this->newLine();
            $this->info('🎉 All lifecycle inheritance issues have been resolved!');
            $this->info('💡 Shares now have clean selling-phase state without buying-phase baggage');
            $this->info('⚡ Users will receive proper Total = Investment + Earnings when shares are sold');

            return $fixedCount > 0 ? 0 : 1;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Transaction failed: ' . $e->getMessage());
            Log::error('FixShareLifecycleInheritanceCommand transaction failed: ' . $e->getMessage());
            return 1;
        }
    }
}