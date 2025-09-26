<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use App\Services\PaymentFailureService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessExpiredPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:process-expired {--dry-run : Show what would be processed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expired payments and mark trades as failed (only if no payment was submitted)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('🚨 DRY RUN MODE - No changes will be made');
        }
        
        $this->info('🔍 Processing expired payments...');
        $this->newLine();
        
        try {
            // Get all paired shares that might have expired payment deadlines
            // BUT exclude shares where payment has been submitted (timer_paused = true)
            $expiredShares = UserShare::whereStatus('paired')
                ->where('balance', 0)
                ->where(function ($query) {
                    // Only include shares where timer is NOT paused (no payment submitted)
                    // Check both legacy and enhanced timer fields
                    $query->where('timer_paused', false)
                          ->orWhereNull('timer_paused');
                })
                ->where(function ($query) {
                    // Also check enhanced payment timer fields
                    $query->where('payment_timer_paused', false)
                          ->orWhereNull('payment_timer_paused');
                })
                ->with(['payments']) // Load payments to double-check
                ->get()
                ->filter(function ($share) {
                    $deadlineMinutes = $share->payment_deadline_minutes ?? 60;
                    $timeoutTime = Carbon::parse($share->created_at)->addMinutes($deadlineMinutes);
                    
                    // Additional check: if there are any payment records, don't mark as failed
                    if ($share->payments()->exists()) {
                        return false; // Don't include shares with payment records
                    }
                    
                    return $timeoutTime < Carbon::now();
                });

            // Also check for shares with payments that were skipped
            $sharesWithPayments = UserShare::whereStatus('paired')
                ->where('balance', 0)
                ->where(function ($query) {
                    // Check both legacy and enhanced timer fields
                    $query->where('timer_paused', true)
                          ->orWhere('payment_timer_paused', true);
                })
                ->whereHas('payments')
                ->count();
                
            if ($sharesWithPayments > 0) {
                $this->info("💰 Found {$sharesWithPayments} share(s) with submitted payments - these will NOT be marked as failed");
            }
            
            if ($expiredShares->isEmpty()) {
                $this->info('✅ No expired payments found (excluding shares with submitted payments).');
                return 0;
            }

            $this->info("Found {$expiredShares->count()} expired payment(s):");
            $this->newLine();

            $processedCount = 0;
            $errorCount = 0;
            $paymentFailureService = new PaymentFailureService();

            foreach ($expiredShares as $share) {
                $deadlineMinutes = $share->payment_deadline_minutes ?? 60;
                $timeoutTime = Carbon::parse($share->created_at)->addMinutes($deadlineMinutes);
                $minutesOverdue = Carbon::now()->diffInMinutes($timeoutTime);
                
                $this->info("📋 Processing: {$share->ticket_no} (User: {$share->user_id})");
                $this->line("   ⏰ Deadline: {$deadlineMinutes} min, Overdue: {$minutesOverdue} min");
                
                if (!$dryRun) {
                    try {
                        // Update share status to failed
                        $share->status = 'failed';
                        $share->save();
                        
                        // Handle payment failure for the user
                        $result = $paymentFailureService->handlePaymentFailure(
                            $share->user_id, 
                            "Payment timeout - share failed after {$deadlineMinutes} minutes (no payment made)"
                        );
                        
                        if ($result['suspended']) {
                            $this->warn("   🔒 User suspended due to payment failure");
                        }
                        
                        // Return shares to sellers
                        $returnedShares = 0;
                        foreach ($share->pairedShares as $pairedShare) {
                            $sellerShare = UserShare::findOrFail($pairedShare->paired_user_share_id);
                            $sellerShare->hold_quantity -= $pairedShare->share;
                            $sellerShare->total_share_count += $pairedShare->share;
                            $sellerShare->save();
                            $returnedShares += $pairedShare->share;
                            
                            $this->line("   📤 Returned {$pairedShare->share} shares to seller (ID: {$sellerShare->id})");
                        }
                        
                        Log::info("Payment failure processed", [
                            'share_id' => $share->id,
                            'ticket_no' => $share->ticket_no,
                            'deadline_minutes' => $deadlineMinutes,
                            'minutes_overdue' => $minutesOverdue,
                            'returned_shares' => $returnedShares,
                            'user_suspended' => $result['suspended'] ?? false
                        ]);
                        
                        $this->info("   ✅ Processed successfully");
                        $processedCount++;
                        
                    } catch (\Exception $e) {
                        $this->error("   ❌ Error: " . $e->getMessage());
                        Log::error('Error processing expired payment', [
                            'share_id' => $share->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        $errorCount++;
                    }
                } else {
                    $this->line("   💭 Would mark as failed and return shares");
                    $processedCount++;
                }
                
                $this->newLine();
            }
            
            $this->info('📊 Summary:');
            $this->info("   Processed: {$processedCount}");
            if ($errorCount > 0) {
                $this->error("   Errors: {$errorCount}");
            }
            
            if ($dryRun) {
                $this->warn('💡 Run without --dry-run to apply these changes');
            }
            
            return $errorCount > 0 ? 1 : 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Fatal error: ' . $e->getMessage());
            Log::error('Fatal error in ProcessExpiredPayments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}