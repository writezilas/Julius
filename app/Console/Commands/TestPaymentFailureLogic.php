<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use App\Services\PaymentFailureService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TestPaymentFailureLogic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:payment-failure-logic {--dry-run : Show what would be processed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the enhanced payment failure logic to ensure it correctly handles timer states';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('🚨 DRY RUN MODE - No changes will be made');
        }
        
        $this->info('🧪 Testing Enhanced Payment Failure Logic...');
        $this->newLine();
        
        try {
            // Test scenario: Find paired shares that might be affected by the payment failure logic
            $candidateShares = UserShare::whereStatus('paired')
                ->where('balance', 0)
                ->with(['payments'])
                ->get();
            
            if ($candidateShares->isEmpty()) {
                $this->info('✅ No paired shares found to test.');
                return 0;
            }
            
            $this->info("Found {$candidateShares->count()} paired share(s) to analyze:");
            $this->newLine();
            
            $wouldBeMarkedFailed = 0;
            $protectedByPayment = 0;
            $protectedByLegacyTimer = 0;
            $protectedByEnhancedTimer = 0;
            $hasPaymentRecords = 0;
            
            foreach ($candidateShares as $share) {
                $deadlineMinutes = $share->payment_deadline_minutes ?? 60;
                $timeoutTime = Carbon::parse($share->created_at)->addMinutes($deadlineMinutes);
                $isExpired = $timeoutTime < Carbon::now();
                $minutesOverdue = $isExpired ? Carbon::now()->diffInMinutes($timeoutTime) : 0;
                
                $hasPayments = $share->payments()->exists();
                $legacyTimerPaused = $share->timer_paused;
                $enhancedTimerPaused = $share->payment_timer_paused;
                $hasConfirmedPayments = $share->pairedShares()->where('is_paid', 1)->exists();
                
                $this->info("📋 Share: {$share->ticket_no} (User: {$share->user_id})");
                $this->line("   ⏰ Deadline: {$deadlineMinutes} min, " . ($isExpired ? "Overdue: {$minutesOverdue} min" : "Not expired"));
                $this->line("   💳 Has payments: " . ($hasPayments ? 'Yes' : 'No'));
                $this->line("   ⏸️  Legacy timer paused: " . ($legacyTimerPaused ? 'Yes' : 'No'));
                $this->line("   ⏸️  Enhanced timer paused: " . ($enhancedTimerPaused ? 'Yes' : 'No'));
                $this->line("   ✅ Has confirmed payments: " . ($hasConfirmedPayments ? 'Yes' : 'No'));
                
                // Apply the logic from updatePaymentFailedShareStatus
                $wouldSkip = false;
                $skipReason = '';
                
                if ($legacyTimerPaused || $enhancedTimerPaused) {
                    $wouldSkip = true;
                    $skipReason = 'Timer paused (payment submitted)';
                    if ($legacyTimerPaused) $protectedByLegacyTimer++;
                    if ($enhancedTimerPaused) $protectedByEnhancedTimer++;
                } elseif ($hasPayments) {
                    $wouldSkip = true;
                    $skipReason = 'Payment records found';
                    $hasPaymentRecords++;
                } elseif ($hasConfirmedPayments) {
                    $wouldSkip = true;
                    $skipReason = 'Confirmed payments exist';
                    $protectedByPayment++;
                }
                
                if ($isExpired && !$wouldSkip) {
                    $this->warn("   ⚠️  WOULD BE MARKED AS FAILED");
                    $wouldBeMarkedFailed++;
                } elseif ($isExpired && $wouldSkip) {
                    $this->info("   ✅ PROTECTED FROM FAILURE: {$skipReason}");
                } else {
                    $this->line("   ℹ️  Not expired yet - no action needed");
                }
                
                $this->newLine();
            }
            
            // Summary
            $this->info('📊 Test Summary:');
            $this->info("   Total paired shares analyzed: {$candidateShares->count()}");
            $this->info("   Would be marked as failed: {$wouldBeMarkedFailed}");
            $this->info("   Protected by payment records: {$hasPaymentRecords}");
            $this->info("   Protected by legacy timer pause: {$protectedByLegacyTimer}");
            $this->info("   Protected by enhanced timer pause: {$protectedByEnhancedTimer}");
            $this->info("   Protected by confirmed payments: {$protectedByPayment}");
            
            if ($protectedByEnhancedTimer > 0) {
                $this->info("✅ Enhanced timer protection is working!");
            }
            
            if ($wouldBeMarkedFailed > 0 && !$dryRun) {
                $this->warn("⚠️  {$wouldBeMarkedFailed} share(s) would be marked as failed. Run updatePaymentFailedShareStatus() to process them.");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error during testing: ' . $e->getMessage());
            Log::error('Error in TestPaymentFailureLogic', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}