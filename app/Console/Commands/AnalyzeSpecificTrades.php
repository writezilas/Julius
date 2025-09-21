<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use App\Models\UserSharePayment;
use App\Models\UserSharePair;
use App\Services\PaymentFailureService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AnalyzeSpecificTrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analyze:specific-trades {ticket1} {ticket2} {--fix : Apply status corrections based on analysis}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze specific trades to determine their correct status using updated logic';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ticket1 = $this->argument('ticket1');
        $ticket2 = $this->argument('ticket2');
        $shouldFix = $this->option('fix');
        
        $this->info('🔍 Analyzing Specific Trades with Updated Logic...');
        $this->info("Trade 1: {$ticket1}");
        $this->info("Trade 2: {$ticket2}");
        if ($shouldFix) {
            $this->warn('🔧 Fix mode enabled - status corrections will be applied');
        } else {
            $this->info('📊 Analysis mode - no changes will be made');
        }
        $this->newLine();
        
        try {
            $trade1 = UserShare::where('ticket_no', $ticket1)->first();
            $trade2 = UserShare::where('ticket_no', $ticket2)->first();
            
            if (!$trade1) {
                $this->error("❌ Trade {$ticket1} not found");
            }
            if (!$trade2) {
                $this->error("❌ Trade {$ticket2} not found");
            }
            
            if (!$trade1 && !$trade2) {
                return 1;
            }
            
            $trades = array_filter([$trade1, $trade2]);
            
            foreach ($trades as $trade) {
                $this->analyzeTrade($trade, $shouldFix);
                $this->newLine();
            }
            
            // Summary comparison if both trades exist
            if ($trade1 && $trade2) {
                $this->info('🔍 Comparison Summary:');
                $this->compareTradesStatus($trade1, $trade2);
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error during analysis: ' . $e->getMessage());
            Log::error('Error in AnalyzeSpecificTrades', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
    
    private function analyzeTrade(UserShare $trade, bool $shouldFix = false): void
    {
        $this->info("📋 Analyzing Trade: {$trade->ticket_no}");
        $this->line("   User ID: {$trade->user_id}");
        $this->line("   Current Status: {$trade->status}");
        $this->line("   Created At: {$trade->created_at}");
        $this->line("   Balance: {$trade->balance}");
        
        // Timer states
        $legacyTimerPaused = $trade->timer_paused ?? false;
        $enhancedTimerPaused = $trade->payment_timer_paused ?? false;
        
        $this->line("   Legacy Timer Paused: " . ($legacyTimerPaused ? 'Yes' : 'No'));
        $this->line("   Enhanced Timer Paused: " . ($enhancedTimerPaused ? 'Yes' : 'No'));
        
        if ($legacyTimerPaused && $trade->timer_paused_at) {
            $this->line("   Legacy Timer Paused At: {$trade->timer_paused_at}");
        }
        if ($enhancedTimerPaused && $trade->payment_timer_paused_at) {
            $this->line("   Enhanced Timer Paused At: {$trade->payment_timer_paused_at}");
        }
        
        // Payment analysis
        $payments = $trade->payments()->get();
        $paidPayments = $payments->where('status', 'paid');
        $confirmedPayments = $payments->where('status', 'conformed');
        
        $this->line("   Total Payments: {$payments->count()}");
        $this->line("   Paid Payments: {$paidPayments->count()}");
        $this->line("   Confirmed Payments: {$confirmedPayments->count()}");
        
        // Pairing analysis
        $pairings = $trade->pairedShares()->get();
        $paidPairings = $pairings->where('is_paid', 1);
        $unpaidPairings = $pairings->where('is_paid', 0);
        
        $this->line("   Total Pairings: {$pairings->count()}");
        $this->line("   Paid Pairings: {$paidPairings->count()}");
        $this->line("   Unpaid Pairings: {$unpaidPairings->count()}");
        
        // Payment deadline analysis
        $deadlineMinutes = $trade->payment_deadline_minutes ?? 60;
        $deadlineTime = Carbon::parse($trade->created_at)->addMinutes($deadlineMinutes);
        $isExpired = $deadlineTime->isPast();
        $minutesOverdue = $isExpired ? Carbon::now()->diffInMinutes($deadlineTime) : 0;
        
        $this->line("   Payment Deadline: {$deadlineTime->format('Y-m-d H:i:s')} ({$deadlineMinutes} min)");
        $this->line("   Deadline Status: " . ($isExpired ? "EXPIRED ({$minutesOverdue} min overdue)" : "Active"));
        
        // Apply updated logic to determine correct status
        $correctStatus = $this->determineCorrectStatus($trade);
        $shouldBeProtected = $this->shouldBeProtectedFromFailure($trade);
        
        $this->newLine();
        $this->info("🎯 Analysis Results:");
        $this->line("   Should be protected from failure: " . ($shouldBeProtected ? 'YES' : 'NO'));
        $this->line("   Determined correct status: {$correctStatus}");
        
        if ($trade->status !== $correctStatus) {
            $this->warn("   ⚠️  STATUS MISMATCH - Current: {$trade->status}, Should be: {$correctStatus}");
            
            if ($shouldFix) {
                $this->info("   🔧 Applying status correction...");
                $this->fixTradeStatus($trade, $correctStatus);
            } else {
                $this->info("   💡 Run with --fix to apply this correction");
            }
        } else {
            $this->info("   ✅ Status is CORRECT");
        }
        
        // Detailed reasoning
        $this->newLine();
        $this->info("🧠 Reasoning:");
        $reasoning = $this->getStatusReasoning($trade, $shouldBeProtected, $isExpired);
        foreach ($reasoning as $reason) {
            $this->line("   • {$reason}");
        }
    }
    
    private function shouldBeProtectedFromFailure(UserShare $trade): bool
    {
        // Apply the UPDATED logic from the fixed updatePaymentFailedShareStatus function
        
        // Check both legacy and enhanced timer fields (THE FIX)
        if ($trade->timer_paused || $trade->payment_timer_paused) {
            return true; // Protected - payment was submitted
        }
        
        // Check if there are payment records
        if ($trade->payments()->exists()) {
            return true; // Protected - payment records found
        }
        
        // Check for confirmed payments in pairings
        if ($trade->pairedShares()->where('is_paid', 1)->exists()) {
            return true; // Protected - confirmed payments exist
        }
        
        return false; // Not protected - can be marked as failed if expired
    }
    
    private function determineCorrectStatus(UserShare $trade): string
    {
        $deadlineMinutes = $trade->payment_deadline_minutes ?? 60;
        $deadlineTime = Carbon::parse($trade->created_at)->addMinutes($deadlineMinutes);
        $isExpired = $deadlineTime->isPast();
        $shouldBeProtected = $this->shouldBeProtectedFromFailure($trade);
        
        // If trade is currently failed, check if it should be
        if ($trade->status === 'failed') {
            if ($shouldBeProtected) {
                // Should not be failed - determine what it should be
                if ($trade->payments()->where('status', 'conformed')->exists()) {
                    return 'completed';
                } elseif ($trade->payments()->where('status', 'paid')->exists()) {
                    return 'paired'; // Payment submitted, awaiting confirmation
                } else {
                    return 'paired'; // Has pairings but payment process ongoing
                }
            } else {
                // Should remain failed if expired and unprotected
                return $isExpired ? 'failed' : 'paired';
            }
        }
        
        // If trade is not failed, check if it should be
        if ($trade->status !== 'failed') {
            if ($isExpired && !$shouldBeProtected) {
                return 'failed'; // Should be marked as failed
            }
        }
        
        // For other statuses, return current status if logic is satisfied
        return $trade->status;
    }
    
    private function getStatusReasoning(UserShare $trade, bool $shouldBeProtected, bool $isExpired): array
    {
        $reasoning = [];
        
        if ($shouldBeProtected) {
            $reasoning[] = "Trade is PROTECTED from failure because:";
            
            if ($trade->timer_paused) {
                $reasoning[] = "  - Legacy timer is paused (payment submitted)";
            }
            if ($trade->payment_timer_paused) {
                $reasoning[] = "  - Enhanced timer is paused (payment submitted)";
            }
            if ($trade->payments()->exists()) {
                $reasoning[] = "  - Payment records exist";
            }
            if ($trade->pairedShares()->where('is_paid', 1)->exists()) {
                $reasoning[] = "  - Confirmed payments exist in pairings";
            }
        } else {
            $reasoning[] = "Trade is NOT protected from failure because:";
            $reasoning[] = "  - No timer pause flags set";
            $reasoning[] = "  - No payment records found";
            $reasoning[] = "  - No confirmed payments in pairings";
        }
        
        if ($isExpired) {
            $reasoning[] = "Payment deadline has EXPIRED";
        } else {
            $reasoning[] = "Payment deadline is still active";
        }
        
        return $reasoning;
    }
    
    private function fixTradeStatus(UserShare $trade, string $correctStatus): void
    {
        try {
            $originalStatus = $trade->status;
            $trade->status = $correctStatus;
            $trade->save();
            
            $this->info("   ✅ Status updated from '{$originalStatus}' to '{$correctStatus}'");
            
            Log::info("Trade status corrected by AnalyzeSpecificTrades command", [
                'ticket_no' => $trade->ticket_no,
                'user_id' => $trade->user_id,
                'original_status' => $originalStatus,
                'corrected_status' => $correctStatus,
                'reason' => 'Applied updated payment failure logic'
            ]);
            
        } catch (\Exception $e) {
            $this->error("   ❌ Failed to update status: " . $e->getMessage());
        }
    }
    
    private function compareTradesStatus(UserShare $trade1, UserShare $trade2): void
    {
        $this->line("   Trade 1 ({$trade1->ticket_no}): {$trade1->status}");
        $this->line("   Trade 2 ({$trade2->ticket_no}): {$trade2->status}");
        
        $protection1 = $this->shouldBeProtectedFromFailure($trade1);
        $protection2 = $this->shouldBeProtectedFromFailure($trade2);
        
        $this->line("   Trade 1 protected: " . ($protection1 ? 'YES' : 'NO'));
        $this->line("   Trade 2 protected: " . ($protection2 ? 'YES' : 'NO'));
        
        if ($protection1 && $protection2) {
            $this->info("   🤔 Both trades are protected - this seems unusual for the reported issue");
        } elseif ($protection1 && !$protection2) {
            $this->info("   ✅ Expected pattern: Trade 1 protected (payment), Trade 2 not protected");
        } elseif (!$protection1 && $protection2) {
            $this->warn("   ⚠️  Unexpected: Trade 2 protected instead of Trade 1");
        } else {
            $this->warn("   ⚠️  Neither trade is protected - both should be failed if expired");
        }
    }
}