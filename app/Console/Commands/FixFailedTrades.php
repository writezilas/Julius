<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use App\Models\UserSharePayment;
use App\Services\PaymentFailureService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FixFailedTrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trades:fix-failed {tickets?* : Specific ticket numbers to fix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix failed trades that should have been returned to wallets';

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
        $tickets = $this->argument('tickets');
        
        if (empty($tickets)) {
            // Process the specific trades mentioned in the issue
            $tickets = ['AB-17564479284995', 'AB-17564591752453', 'AB-17564602415878'];
            $this->info('No specific tickets provided. Processing known failed trades: ' . implode(', ', $tickets));
        }

        $this->info('Starting fix for failed trades...');
        
        $paymentFailureService = new PaymentFailureService();
        $processedCount = 0;
        
        foreach ($tickets as $ticketNumber) {
            $this->info("\nProcessing ticket: {$ticketNumber}");
            
            $share = UserShare::where('ticket_no', $ticketNumber)->first();
            
            if (!$share) {
                $this->error("Share not found for ticket: {$ticketNumber}");
                continue;
            }
            
            $this->displayShareInfo($share);
            
            // Check if this share should be processed
            if ($this->shouldProcessShare($share)) {
                if ($this->confirm("Process this failed share and return shares to wallets?")) {
                    $this->processFailedShare($share, $paymentFailureService);
                    $processedCount++;
                } else {
                    $this->info("Skipped ticket: {$ticketNumber}");
                }
            } else {
                $this->info("Share does not meet criteria for processing.");
            }
        }
        
        $this->info("\n✅ Processing complete. Fixed {$processedCount} shares.");
        
        return 0;
    }
    
    /**
     * Display share information
     */
    private function displayShareInfo($share)
    {
        $this->table(['Field', 'Value'], [
            ['Ticket Number', $share->ticket_no],
            ['Status', $share->status],
            ['User ID', $share->user_id],
            ['Total Shares', $share->total_share_count],
            ['Hold Quantity', $share->hold_quantity],
            ['Created At', $share->created_at],
            ['Start Date', $share->start_date ?? 'NULL'],
            ['Paired Shares Count', $share->pairedShares->count()],
        ]);
        
        // Show paired shares info
        if ($share->pairedShares->count() > 0) {
            $this->info("Paired Shares Details:");
            foreach ($share->pairedShares as $paired) {
                $pairedShare = UserShare::find($paired->paired_user_share_id);
                if ($pairedShare) {
                    $this->info("  - Paired with Share ID: {$pairedShare->id} (Ticket: {$pairedShare->ticket_no})");
                    $this->info("    Shares: {$paired->share}, Hold: {$pairedShare->hold_quantity}, Total: {$pairedShare->total_share_count}");
                }
            }
        }
        
        // Show payment info
        $payments = UserSharePayment::where('user_share_id', $share->id)->get();
        $this->info("Payments Count: {$payments->count()}");
        foreach ($payments as $payment) {
            $this->info("  - Payment ID: {$payment->id}, Status: {$payment->status}, Amount: {$payment->amount}");
        }
    }
    
    /**
     * Check if share should be processed
     */
    private function shouldProcessShare($share)
    {
        // Only process shares that are paired but failed to get confirmed payments
        if ($share->status !== 'paired') {
            $this->info("Share status is '{$share->status}' - not 'paired'. Skipping.");
            return false;
        }
        
        // Check if has start_date (means it was activated)
        if (is_null($share->start_date)) {
            $this->info("Share has no start_date - will be caught by normal timeout logic. Skipping.");
            return false;
        }
        
        // Check if payment deadline has passed (based on admin-configured deadline)
        $deadlineMinutes = $share->payment_deadline_minutes ?? 60;
        if (Carbon::parse($share->start_date)->addMinutes($deadlineMinutes) >= Carbon::now()) {
            $this->info("Payment deadline has not passed yet. Skipping.");
            return false;
        }
        
        // Check if has any confirmed payments
        $hasConfirmedPayments = UserSharePayment::where('user_share_id', $share->id)
            ->where('status', 'conformed') // Note: typo in original DB
            ->exists();
            
        if ($hasConfirmedPayments) {
            $this->info("Share has confirmed payments. Skipping.");
            return false;
        }
        
        $this->info("✅ Share meets criteria for processing (paired with start_date but no confirmed payments after deadline).");
        return true;
    }
    
    /**
     * Process the failed share
     */
    private function processFailedShare($share, $paymentFailureService)
    {
        try {
            $this->info("🔄 Processing failed share: {$share->ticket_no}");
            
            // Mark share as failed
            $share->status = 'failed';
            $share->save();
            
            $this->info("✅ Share marked as failed");
            
            // Handle payment failure for the user (suspension tracking, etc.)
            try {
                $result = $paymentFailureService->handlePaymentFailure(
                    $share->user_id, 
                    'Payment timeout - no confirmed payment after 3 hours (fixed by command)'
                );
                
                if ($result['suspended']) {
                    $this->warn("⚠️ User suspended due to payment failure: " . $share->user->username);
                } else {
                    $this->info("✅ Payment failure handled for user");
                }
            } catch (\Exception $e) {
                $this->error("❌ Error handling payment failure: " . $e->getMessage());
            }
            
            // Return shares to their respective wallets
            $returnedShares = 0;
            foreach ($share->pairedShares as $pairedShare) {
                $userShare = UserShare::findOrFail($pairedShare->paired_user_share_id);
                
                $this->info("Processing paired share ID: {$userShare->id} (Ticket: {$userShare->ticket_no})");
                $this->info("  Before - Hold: {$userShare->hold_quantity}, Total: {$userShare->total_share_count}");
                
                // Only adjust if shares are currently in hold_quantity
                if ($userShare->hold_quantity >= $pairedShare->share) {
                    $userShare->hold_quantity -= $pairedShare->share;
                    $userShare->total_share_count += $pairedShare->share;
                    $userShare->save();
                    
                    $returnedShares += $pairedShare->share;
                    
                    $this->info("  After  - Hold: {$userShare->hold_quantity}, Total: {$userShare->total_share_count}");
                    $this->info("✅ Returned {$pairedShare->share} shares to wallet for share ID: {$userShare->id}");
                } else {
                    $this->error("❌ Cannot return shares - insufficient hold_quantity. Share ID: {$userShare->id}, Hold: {$userShare->hold_quantity}, Required: {$pairedShare->share}");
                }
            }
            
            Log::info("Fixed failed share via command: {$share->id} (ticket: {$share->ticket_no}) - Returned {$returnedShares} shares to wallets");
            
            $this->info("✅ Successfully processed failed share: {$share->ticket_no}");
            $this->info("📊 Total shares returned to wallets: {$returnedShares}");
            
        } catch (\Exception $e) {
            $this->error("❌ Error processing failed share {$share->id}: " . $e->getMessage());
            Log::error("Error in FixFailedTrades command for share {$share->id}: " . $e->getMessage());
        }
    }
}
