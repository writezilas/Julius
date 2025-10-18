<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use App\Models\TradePeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MatureAllRunningShares extends Command
{
    protected $signature = 'shares:mature-all-running {--dry-run : Show what would be matured without making changes}';
    protected $description = 'One-time command to mature all currently running shares and make them available to market';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('🔍 Maturing All Running Shares...');
        $this->newLine();

        if ($dryRun) {
            $this->warn('🧪 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        try {
            if (!$dryRun) {
                DB::beginTransaction();
            }

            // Find all shares that are currently running (completed status but not ready to sell)
            $runningShares = UserShare::with('tradePeriod', 'trade')
                ->where('status', 'completed')
                ->where('is_ready_to_sell', 0)
                ->whereNotNull('start_date')
                ->whereNotNull('period')
                ->get();

            if ($runningShares->isEmpty()) {
                $this->info('✅ No running shares found to mature');
                return 0;
            }

            $this->info("Found {$runningShares->count()} running shares to mature:");
            $this->newLine();

            // Get all active trade periods for profit calculation
            $tradePeriods = TradePeriod::where('status', 1)->get();
            
            $maturedCount = 0;
            $totalProfitAdded = 0;

            foreach ($runningShares as $share) {
                // Find the matching trade period
                $tradePeriod = $tradePeriods->where('days', $share->period)->first();
                
                if (!$tradePeriod) {
                    $this->warn("⚠️  Skipping share {$share->ticket_no} - No trade period found for {$share->period} days");
                    continue;
                }

                // Calculate profit based on original share amount
                $profitPercentage = $tradePeriod->percentage;
                $profit = ($share->share_will_get * $profitPercentage / 100);
                
                // Calculate total shares after profit
                $totalAfterProfit = $share->total_share_count + $profit;
                
                // Display share information
                $tradeName = $share->trade ? $share->trade->name : 'Unknown';
                $this->line("📈 {$share->ticket_no} (User: {$share->user_id})");
                $this->line("   • Trade: {$tradeName}");
                $this->line("   • Period: {$share->period} days");
                $this->line("   • Started: {$share->start_date}");
                $this->line("   • Original Shares: {$share->share_will_get}");
                $this->line("   • Current Total Count: {$share->total_share_count}");
                $this->line("   • Profit Rate: {$profitPercentage}%");
                $this->line("   • Profit to Add: {$profit}");
                $this->line("   • Total After Maturation: {$totalAfterProfit}");
                
                if (!$dryRun) {
                    // Mature the share
                    $share->is_ready_to_sell = 1;
                    $share->matured_at = now();
                    $share->profit_share = $profit;
                    // CRITICAL FIX: Update total_share_count to include profit
                    $share->total_share_count = $share->total_share_count + $profit;
                    
                    // Save the changes
                    $share->save();
                    
                    Log::info("Share matured by admin command", [
                        'share_id' => $share->id,
                        'ticket_no' => $share->ticket_no,
                        'user_id' => $share->user_id,
                        'profit_added' => $profit,
                        'original_shares' => $share->share_will_get,
                        'command' => 'shares:mature-all-running'
                    ]);
                }
                
                $maturedCount++;
                $totalProfitAdded += $profit;
                $this->line("   ✅ " . ($dryRun ? "Would be matured" : "Matured successfully"));
                $this->newLine();
            }

            if (!$dryRun) {
                DB::commit();
            }

            // Display summary
            $this->info('📊 SUMMARY');
            $this->line(str_repeat('=', 50));
            $this->line("🎯 Shares processed: {$maturedCount}");
            $this->line("💰 Total profit added: " . number_format($totalProfitAdded, 2));
            
            if ($dryRun) {
                $this->newLine();
                $this->warn('🧪 This was a DRY RUN - no changes were made');
                $this->line('Run without --dry-run to apply changes');
            } else {
                $this->newLine();
                $this->info('✅ All running shares have been matured successfully!');
                $this->line('These shares are now available for purchase in the market.');
            }

            return 0;

        } catch (\Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            
            $this->error('❌ Error during maturation process: ' . $e->getMessage());
            Log::error('MatureAllRunningShares command failed: ' . $e->getMessage());
            
            return 1;
        }
    }
}
