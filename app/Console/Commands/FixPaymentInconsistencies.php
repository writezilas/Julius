<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPaymentInconsistencies extends Command
{
    protected $signature = 'payment:fix-inconsistencies {--dry-run : Only show issues without fixing them}';
    protected $description = 'Fix payment-related data inconsistencies that could cause constraint violations';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('Running in DRY RUN mode - no changes will be made');
        }

        $this->info('Checking for payment data inconsistencies...');

        // Check for shares with negative hold_quantity
        $negativeHoldQuantity = UserShare::where('hold_quantity', '<', 0)->get();
        
        if ($negativeHoldQuantity->count() > 0) {
            $this->warn("Found {$negativeHoldQuantity->count()} shares with negative hold_quantity:");
            
            foreach ($negativeHoldQuantity as $share) {
                $this->line("  Ticket: {$share->ticket_no} (ID: {$share->id}) - hold_quantity: {$share->hold_quantity}");
                
                if (!$isDryRun) {
                    // Reset to 0 to prevent constraint violations
                    $share->hold_quantity = 0;
                    $share->save();
                    $this->info("    Fixed: Reset hold_quantity to 0");
                }
            }
        } else {
            $this->info('✓ No shares with negative hold_quantity found');
        }

        // Check for payments with invalid status or already processed pairs
        $problematicPayments = DB::table('user_share_payments as usp')
            ->join('user_share_pairs as pair', 'usp.user_share_pair_id', '=', 'pair.id')
            ->where('usp.status', 'paid')
            ->where('pair.is_paid', '1')
            ->select('usp.*', 'pair.share', 'pair.is_paid')
            ->get();

        if ($problematicPayments->count() > 0) {
            $this->warn("Found {$problematicPayments->count()} payments in 'paid' status but with already processed pairs:");
            
            foreach ($problematicPayments as $payment) {
                $this->line("  Payment ID: {$payment->id} - Pair is already marked as paid");
                
                if (!$isDryRun) {
                    // Update payment status to conformed since pair is already processed
                    DB::table('user_share_payments')
                        ->where('id', $payment->id)
                        ->update(['status' => 'conformed']);
                    $this->info("    Fixed: Updated payment status to 'conformed'");
                }
            }
        } else {
            $this->info('✓ No payments with inconsistent status found');
        }

        // Check for share pairs with 0 or negative shares that have payments
        $zeroSharePayments = DB::table('user_share_payments as usp')
            ->join('user_share_pairs as pair', 'usp.user_share_pair_id', '=', 'pair.id')
            ->where('pair.share', '<=', 0)
            ->select('usp.*', 'pair.share')
            ->get();

        if ($zeroSharePayments->count() > 0) {
            $this->warn("Found {$zeroSharePayments->count()} payments for share pairs with 0 or negative shares:");
            
            foreach ($zeroSharePayments as $payment) {
                $this->line("  Payment ID: {$payment->id} - Share amount: {$payment->share}");
                
                if (!$isDryRun) {
                    // Mark these payments as failed to prevent processing
                    DB::table('user_share_payments')
                        ->where('id', $payment->id)
                        ->update(['status' => 'failed']);
                    $this->info("    Fixed: Marked payment as 'failed'");
                }
            }
        } else {
            $this->info('✓ No payments for zero/negative share pairs found');
        }

        // Check for completed buyer orders incorrectly matched as sellers
        $incorrectSellerPairings = DB::table('user_share_pairs as usp')
            ->join('user_shares as seller_share', 'usp.paired_user_share_id', '=', 'seller_share.id')
            ->join('user_share_payments as payment', 'usp.id', '=', 'payment.user_share_pair_id')
            ->where('seller_share.status', 'completed')
            ->where('seller_share.total_share_count', DB::raw('seller_share.share_will_get'))
            ->where('seller_share.hold_quantity', 0)
            ->where('usp.is_paid', 0)
            ->where('payment.status', 'paid')
            ->select('usp.*', 'seller_share.ticket_no as seller_ticket', 'seller_share.total_share_count', 'seller_share.share_will_get')
            ->get();

        if ($incorrectSellerPairings->count() > 0) {
            $this->warn("Found {$incorrectSellerPairings->count()} share pairs incorrectly matched to completed buyer orders:");
            
            foreach ($incorrectSellerPairings as $pairing) {
                $this->line("  Share Pair ID: {$pairing->id} -> Seller Ticket: {$pairing->seller_ticket} (completed buyer order)");
                
                if (!$isDryRun) {
                    // Mark the payment as failed and log the issue
                    DB::table('user_share_payments')
                        ->where('user_share_pair_id', $pairing->id)
                        ->update(['status' => 'failed']);
                    
                    // Optionally, you could also remove the share pair or mark it for re-matching
                    // But for safety, we'll just mark the payment as failed
                    $this->info("    Fixed: Marked payment as 'failed' for incorrect seller pairing");
                }
            }
        } else {
            $this->info('✓ No incorrect seller pairings found');
        }

        $this->info('Data consistency check completed.');
        
        if ($isDryRun) {
            $this->info('To fix the issues, run the command without --dry-run flag');
        }
    }
}
