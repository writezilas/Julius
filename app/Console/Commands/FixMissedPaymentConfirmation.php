<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use App\Models\User;
use App\Models\Log;
use App\Notifications\PaymentApproved;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class FixMissedPaymentConfirmation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:fix-missed-confirmations {payment_ids?* : Specific payment IDs to fix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix payments that were made but never confirmed due to failed share status';

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
        $paymentIds = $this->argument('payment_ids');
        
        if (empty($paymentIds)) {
            // Find payments that are "paid" but share pairs are not marked as paid
            $problematicPayments = UserSharePayment::where('status', 'paid')
                ->whereHas('sharePair', function($query) {
                    $query->where('is_paid', 0);
                })
                ->whereHas('sharePair.buyerUserShare', function($query) {
                    $query->where('status', 'failed');
                })
                ->get();
                
            if ($problematicPayments->count() == 0) {
                $this->info('No payments found that need confirmation fix.');
                return 0;
            }
            
            $this->info('Found ' . $problematicPayments->count() . ' payments that need confirmation:');
            foreach ($problematicPayments as $payment) {
                $this->info("Payment ID: {$payment->id}, Amount: {$payment->amount}");
            }
            
            if (!$this->confirm('Do you want to process these payments?')) {
                return 0;
            }
            
            $paymentIds = $problematicPayments->pluck('id')->toArray();
        }

        $this->info('🔧 Starting payment confirmation fix process...');
        
        $processedCount = 0;
        
        foreach ($paymentIds as $paymentId) {
            $this->info("\n📋 Processing payment ID: {$paymentId}");
            
            try {
                DB::beginTransaction();
                
                $payment = UserSharePayment::findOrFail($paymentId);
                $sharePair = UserSharePair::findOrFail($payment->user_share_pair_id);
                
                if ($sharePair->share <= 0) {
                    $this->error("❌ Cannot approve payment for 0 or negative shares. Skipping.");
                    continue;
                }
                
                if ($payment->status === 'conformed') {
                    $this->info("✅ Payment already confirmed. Skipping.");
                    continue;
                }
                
                $this->displayPaymentInfo($payment, $sharePair);
                
                // Approve the payment
                $payment->status = 'conformed';
                $payment->save();
                
                // Mark share pair as paid
                $sharePair->is_paid = 1;
                $sharePair->save();
                
                // Update buyer share (recipient of the shares)
                $buyerShare = UserShare::findOrFail($sharePair->user_share_id);
                $buyerShare->increment('total_share_count', $sharePair->share);
                
                // Update seller share (only if hold_quantity allows it)
                $sellerShare = UserShare::findOrFail($sharePair->paired_user_share_id);
                if ($sellerShare->hold_quantity >= $sharePair->share) {
                    $sellerShare->decrement('hold_quantity', $sharePair->share);
                } else {
                    $this->warn("⚠️  Seller share hold_quantity ({$sellerShare->hold_quantity}) is less than required ({$sharePair->share}). Shares already released.");
                }
                $sellerShare->increment('sold_quantity', $sharePair->share);
                
                // Check if shares should be marked as completed
                if ($buyerShare->share_will_get == $buyerShare->total_share_count) {
                    $buyerShare->status = 'completed';
                    $buyerShare->start_date = now()->format('Y/m/d H:i:s');
                    $buyerShare->save();
                    $this->info("✅ Buyer share marked as completed");
                }
                
                if ($sellerShare->share_will_get == $sellerShare->sold_quantity) {
                    $sellerShare->status = 'completed';
                    $sellerShare->start_date = now()->format('Y/m/d H:i:s');
                    $sellerShare->save();
                    $this->info("✅ Seller share marked as completed");
                }
                
                // Create logs
                $sender = User::findOrFail($payment->sender_id);
                $receiver = User::findOrFail($payment->receiver_id);
                
                // Save log for payment receiver (confirmer)
                $log = new Log();
                $log->remarks = "You confirmed a payment from " . $sender->username . " (Fixed via command)";
                $log->type = "payment";
                $log->value = $payment->amount;
                $log->user_id = $receiver->id;
                $payment->logs()->save($log);

                // Save log for payment sender  
                $log = new Log();
                $log->remarks = "Your payment is confirmed by " . $receiver->username . " (Fixed via command)";
                $log->type = "payment";
                $log->value = $payment->amount;
                $log->user_id = $sender->id;
                $payment->logs()->save($log);
                
                // Send notification
                try {
                    Notification::send($sender, new PaymentApproved($payment));
                } catch (\Exception $e) {
                    $this->warn("⚠️  Failed to send notification: " . $e->getMessage());
                }
                
                DB::commit();
                $processedCount++;
                
                $this->info("✅ Successfully processed payment ID {$paymentId}");
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("❌ Error processing payment ID {$paymentId}: " . $e->getMessage());
                \Log::error("Error in FixMissedPaymentConfirmation for payment {$paymentId}: " . $e->getMessage());
            }
        }
        
        $this->info("\n✅ Processing complete!");
        $this->info("📊 Fixed {$processedCount} payments");
        
        return 0;
    }
    
    private function displayPaymentInfo($payment, $sharePair)
    {
        $buyerShare = UserShare::find($sharePair->user_share_id);
        $sellerShare = UserShare::find($sharePair->paired_user_share_id);
        
        $this->table(['Field', 'Value'], [
            ['Payment ID', $payment->id],
            ['Status', $payment->status],
            ['Amount', $payment->amount],
            ['Sender ID', $payment->sender_id],
            ['Receiver ID', $payment->receiver_id],
            ['Share Pair ID', $sharePair->id],
            ['Shares Amount', $sharePair->share],
            ['Is Paid', $sharePair->is_paid ? 'Yes' : 'No'],
            ['Buyer Share', $buyerShare ? "{$buyerShare->ticket_no} (Status: {$buyerShare->status})" : 'N/A'],
            ['Seller Share', $sellerShare ? "{$sellerShare->ticket_no} (Status: {$sellerShare->status})" : 'N/A'],
        ]);
    }
}
