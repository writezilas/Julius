<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use App\Services\PaymentFailureService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessExpiredTrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trades:process-expired {--ticket= : Specific trade ticket to process} {--dry-run : Show what would be processed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expired trades that should have been failed but are still in paired status (only if no payment was submitted)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔧 Processing Expired Trades...');
        $this->newLine();

        $ticketNo = $this->option('ticket');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🚨 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        try {
            DB::beginTransaction();

            // Find expired trades
            $query = UserShare::whereStatus('paired');
            
            if ($ticketNo) {
                $query->where('ticket_no', $ticketNo);
            }
            
            $expiredShares = $query->with(['payments'])->get()->filter(function ($share) {
                // CRITICAL FIX: Don't process shares where payment was submitted
                if ($share->timer_paused) {
                    return false; // Skip shares with paused timers (payment submitted)
                }
                
                // Additional check: if there are payment records, don't mark as failed
                if ($share->payments()->exists()) {
                    return false; // Skip shares with payment records
                }
                
                // Check if payment deadline has passed (only for shares without payments)
                $deadlineMinutes = $share->payment_deadline_minutes ?? 60;
                $timeoutTime = Carbon::parse($share->created_at)->addMinutes($deadlineMinutes);
                return $timeoutTime < Carbon::now();
            });

            // Count shares that were skipped due to payment submission
            $skippedShares = $query->with(['payments'])->get()->filter(function ($share) {
                return $share->timer_paused || $share->payments()->exists();
            });
            
            if ($skippedShares->count() > 0) {
                $this->info("💰 Found {$skippedShares->count()} share(s) with submitted payments - these will NOT be marked as failed");
            }
            
            if ($expiredShares->isEmpty()) {
                $this->info('✅ No expired trades found (excluding shares with submitted payments).');
                return 0;
            }

            $this->info("Found {$expiredShares->count()} expired trade(s):");
            $this->newLine();

            $paymentFailureService = new PaymentFailureService();
            $processedCount = 0;

            foreach ($expiredShares as $share) {
                $deadlineMinutes = $share->payment_deadline_minutes ?? 60;
                $timeoutTime = Carbon::parse($share->created_at)->addMinutes($deadlineMinutes);
                $overdue = Carbon::now()->diffInMinutes($timeoutTime);
                
                $this->info("🔍 Processing Trade: {$share->ticket_no}");
                $this->line("  📅 Created: {$share->created_at}");
                $this->line("  ⏱️ Deadline: {$deadlineMinutes} minutes");
                $this->line("  ⌛ Expired: {$timeoutTime} ({$overdue} minutes overdue)");
                $this->line("  👤 User ID: {$share->user_id}");
                $this->line("  💰 Amount: KSH {$share->amount}");
                $this->line("  📊 Shares: {$share->share_will_get}");

                if (!$dryRun) {
                    // Process the failed share directly
                    $reason = "Payment timeout - share failed after {$deadlineMinutes} minutes (manual processing)";
                    
                    $share->status = 'failed';
                    $share->save();

                    // Handle payment failure for the user
                    try {
                        $result = $paymentFailureService->handlePaymentFailure(
                            $share->user_id, 
                            $reason
                        );
                        
                        if ($result['suspended']) {
                            $this->warn("  ⚠️ User {$share->user->username} suspended due to payment failure");
                        }
                    } catch (\Exception $e) {
                        $this->error("  ❌ Error handling payment failure: " . $e->getMessage());
                    }

                    // Return shares to paired users
                    foreach ($share->pairedShares as $pairedShare) {
                        $userShare = UserShare::findOrFail($pairedShare->paired_user_share_id);
                        $userShare->hold_quantity -= $pairedShare->share;
                        $userShare->total_share_count += $pairedShare->share;
                        $userShare->save();
                        
                        $this->line("  📤 Returned {$pairedShare->share} shares to user {$userShare->user_id}");
                    }
                    
                    $this->info("  ✅ Trade {$share->ticket_no} marked as failed and shares returned");
                    $processedCount++;
                } else {
                    $this->line("  💭 Would process this expired trade");
                }
                
                $this->newLine();
            }

            if (!$dryRun) {
                DB::commit();
                $this->info("✅ Successfully processed {$processedCount} expired trade(s)!");
            } else {
                DB::rollBack();
                $this->warn("💡 Run without --dry-run to process these {$expiredShares->count()} expired trades");
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error processing expired trades: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}
