<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use App\Services\SaleMaturityTimerService;
use App\Services\PaymentDeadlineTimerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Command to fix timer separation issues
 * 
 * This command addresses the confusion between payment deadline timers
 * and sale maturity timers by properly separating their functionality
 * and fixing shares that are stuck due to the mixed timer logic.
 */
class FixTimerSeparationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:timer-separation {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix timer separation issues between payment deadlines and sale maturity timers';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔧 Timer Separation Fix');
        $this->info('======================');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('🧪 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $saleMaturityService = new SaleMaturityTimerService();
        $paymentDeadlineService = new PaymentDeadlineTimerService();

        try {
            DB::beginTransaction();

            // Step 1: Find shares stuck due to timer confusion
            $this->info('Step 1: Identifying stuck shares due to timer confusion');
            $stuckShares = $this->findStuckShares();
            
            $this->line("Found {$stuckShares->count()} shares with timer separation issues:");
            $this->newLine();

            $fixedCount = 0;
            $skippedCount = 0;

            foreach ($stuckShares as $share) {
                $this->line("📋 Processing {$share->ticket_no} (User: {$share->user_id})");
                $this->line("   Status: {$share->status}");
                $this->line("   Created: {$share->created_at}");
                $this->line("   Start Date: {$share->start_date}");
                $this->line("   Period: {$share->period} days");
                $this->line("   Old Timer Paused: " . ($share->timer_paused ? 'YES' : 'NO'));
                $this->line("   Is Ready to Sell: " . ($share->is_ready_to_sell ? 'YES' : 'NO'));

                // Check if this share should be matured
                if ($saleMaturityService->shouldMatureShare($share)) {
                    $this->line("   🎯 Action: Share should be matured (timer has completed)");
                    
                    if (!$dryRun) {
                        if ($saleMaturityService->matureShare($share)) {
                            $this->line("   ✅ Fixed: Share successfully matured");
                            $fixedCount++;
                        } else {
                            $this->line("   ❌ Error: Failed to mature share");
                            $skippedCount++;
                        }
                    } else {
                        $this->line("   💭 Would mature this share");
                        $fixedCount++;
                    }
                } else {
                    $this->line("   ℹ️  Skipped: Share doesn't need maturation");
                    $skippedCount++;
                }

                $this->newLine();
            }

            // Step 2: Summary
            $this->info('📊 SUMMARY');
            $this->line('==========');
            $this->line("Total shares processed: {$stuckShares->count()}");
            $this->line("Fixed/Would fix: {$fixedCount}");
            $this->line("Skipped: {$skippedCount}");
            $this->newLine();

            // Step 3: Verification
            if (!$dryRun && $fixedCount > 0) {
                DB::commit();
                $this->info('✅ All changes committed successfully');
                
                // Verify the fixes
                $this->info('🔍 Verifying fixes...');
                $this->verifyFixes($stuckShares);
            } else {
                DB::rollBack();
                if ($dryRun) {
                    $this->warn('🧪 DRY RUN: No changes were made');
                } else {
                    $this->info('ℹ️  No changes were needed');
                }
            }

            // Step 4: System recommendations
            $this->displayRecommendations();

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error during timer separation fix: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Find shares that are stuck due to timer confusion
     */
    private function findStuckShares()
    {
        return UserShare::where('status', 'completed')
            ->where('is_ready_to_sell', 0)
            ->where('timer_paused', 1)  // Old confused timer system
            ->whereNotNull('start_date')
            ->whereNotNull('period')
            ->get()
            ->filter(function ($share) {
                // Only include shares where the natural timer period has passed
                $startTime = Carbon::parse($share->start_date);
                $naturalEndTime = $startTime->copy()->addDays($share->period);
                return $naturalEndTime->isPast();
            });
    }

    /**
     * Verify that the fixes worked correctly
     */
    private function verifyFixes($originalShares)
    {
        $verified = 0;
        $issues = 0;

        foreach ($originalShares as $originalShare) {
            $share = UserShare::find($originalShare->id);
            
            if ($share->is_ready_to_sell == 1 && $share->matured_at) {
                $verified++;
                $this->line("✅ {$share->ticket_no}: Successfully matured at {$share->matured_at}");
            } else {
                $issues++;
                $this->line("⚠️  {$share->ticket_no}: Still not properly matured");
            }
        }

        $this->newLine();
        $this->line("Verification Results:");
        $this->line("- Successfully verified: {$verified}");
        $this->line("- Issues remaining: {$issues}");
        
        if ($issues > 0) {
            $this->warn("⚠️  Some shares may need manual intervention");
        }
    }

    /**
     * Display system recommendations
     */
    private function displayRecommendations()
    {
        $this->newLine();
        $this->info('📋 RECOMMENDATIONS');
        $this->line('==================');
        $this->newLine();
        
        $this->line('1. 🔄 Update UI Components:');
        $this->line('   - Update bought-shares.blade.php to use PaymentDeadlineTimerService');
        $this->line('   - Update sold-shares view to use SaleMaturityTimerService');
        $this->line('   - Ensure timer displays show correct context');
        $this->newLine();
        
        $this->line('2. ⏰ Implement Automatic Processing:');
        $this->line('   - Schedule regular maturity timer checks');
        $this->line('   - Schedule payment deadline processing');
        $this->line('   - Add to Laravel scheduler');
        $this->newLine();
        
        $this->line('3. 🧪 Testing:');
        $this->line('   - Test both timer types independently');
        $this->line('   - Verify UI shows correct information per context');
        $this->line('   - Test edge cases and error conditions');
        $this->newLine();
        
        $this->line('4. 📊 Monitoring:');
        $this->line('   - Monitor for similar timer confusion issues');
        $this->line('   - Set up alerts for stuck timers');
        $this->line('   - Regular audits of timer state consistency');
    }
}