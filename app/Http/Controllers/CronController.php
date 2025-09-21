<?php

namespace App\Http\Controllers;

use App\Models\TradePeriod;
use App\Models\User;
use App\Models\UserShare;
use App\Services\PaymentFailureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CronController extends Controller
{
     // cron job for all there timer share transfer and update status

    public function cronForEveryUpdate() {

        // make all share to ready to sell
        try {
            DB::beginTransaction();

            $shares = UserShare::with('tradePeriod', 'pairedShares')
                ->whereIn('status', ['completed', 'paired'])
                ->where('is_ready_to_sell', 0)->get();
            
            $completedShares = $shares->where('status', 'completed');
            $pairedShares    = $shares->where('status', 'paired');

            if(count($completedShares) > 0) {
                $this->updateAsReadyToSell($completedShares);
            }

            if(count($pairedShares) > 0) {
                $bought_time = get_gs_value('bought_time');
                // $pairedShares = $pairedShares
                //         ->where('created_at', '<=', now()->subMinutes($bought_time));
                $this->updateShareStatusAsFailed($pairedShares);
            }
            $this->checkUnPaidReffMatureUser();
            $this->unsuspendExpiredUsers();
            DB::commit();
            return 1;
        }catch (\Exception $e) {
            \Log::error('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            DB::rollBack();
            return 0;
        }
        
    }

    public function updateAsReadyToSell($shares)
    {
        $tradePeriods = TradePeriod::where('status', 1)->get();
        foreach ($tradePeriods as $period) {
        
            $latestShares = $shares->where('period', $period->days)
                ->where('start_date', '<=', now()->subDays($period->days)->format('Y/m/d H:i:s'));
            
            foreach($latestShares as $share){
                $share->is_ready_to_sell = 1;
                $per     = $period->percentage;
                // Calculate profit based on original share amount, not total_share_count
                $earning = ($share->share_will_get * $per / 100);
                $share->profit_share = $earning;
                // Don't add profit to total_share_count to maintain consistency
                // Profit is tracked separately in profit_share column
                $share->save();
                
                \Log::info('Share marked as ready to sell - ID: ' . $share->id . ', User: ' . $share->user_id . ', Profit: ' . $earning);
            }   
        }
        
        return 1;
    }

    public function updateShareStatusAsFailed($shares) {
        $paymentFailureService = new PaymentFailureService();
        
        foreach ($shares as $key => $share) {
            // Use stored deadline instead of current admin setting to ensure consistency
            $deadlineMinutes = $share->payment_deadline_minutes ?? get_gs_value('bought_time') ?? 60;
            
            // CRITICAL FIX: Don't mark as failed if payment was submitted (timer paused)
            if ($share->timer_paused) {
                \Log::info('Skipping share ' . $share->ticket_no . ' - payment submitted, timer paused');
                continue; // Skip this share - payment was submitted
            }
            
            // Additional check: if there are payment records, don't mark as failed
            if ($share->payments()->exists()) {
                \Log::info('Skipping share ' . $share->ticket_no . ' - payment records found');
                continue; // Skip this share - payments exist
            }
            
            // Extra safety check: if buyer share status was manually set to 'paired', respect it
            if ($share->status === 'paired') {
                \Log::info('Skipping share ' . $share->ticket_no . ' - status manually set to paired');
                continue; // Skip this share - manually set as paired
            }
            
            // Check if the payment timeout has been reached (only for shares without payments)
            if ($share->created_at->addMinutes($deadlineMinutes)->isPast()) {
                $share->status = 'failed';
                $share->save();
                
                $pairedShares = $share->pairedShares;
                $paidPairedShares = $pairedShares->where('is_paid', 1);
                $unpaidPairedShares = $pairedShares->where('is_paid', 0);
                
                // Return shares to sellers for unpaid pairs
                foreach ($unpaidPairedShares as $pairedShare) {
                    if (empty($pairedShare->payment)) {
                        $sellerShare = $pairedShare->pairedShare;
                        if ($sellerShare && $pairedShare->share > 0) {
                            $sellerShare->decrement('hold_quantity', $pairedShare->share);
                            $sellerShare->increment('total_share_count', $pairedShare->share);
                            \Log::info('Returned ' . $pairedShare->share . ' shares to seller (UserShare ID: ' . $sellerShare->id . ') due to buyer payment timeout');
                        }
                        $pairedShare->is_paid = 2; // Mark as failed payment
                        $pairedShare->save();
                    }
                }
                
                // Only allocate shares if buyer made some payments (not all failed)
                $paidSharesSum = $paidPairedShares->sum('share');
                if ($paidSharesSum > 0) {
                    saveAllocateShare($share->user_id, $share, $paidSharesSum, $key + 1);
                    \Log::info('Allocated ' . $paidSharesSum . ' shares to buyer (User ID: ' . $share->user_id . ') for partially paid transaction');
                } else {
                    \Log::info('No shares allocated to buyer (User ID: ' . $share->user_id . ') - no payments were made within timeout period');
                    
                    // CRITICAL FIX: Handle payment failure tracking and suspension logic
                    // This was the missing piece that caused Danny not to be suspended
                    try {
                        $reason = "Payment timeout - no payments made within {$bought_time} minutes (CronController)";
                        $result = $paymentFailureService->handlePaymentFailure(
                            $share->user_id, 
                            $reason
                        );
                        
                        if ($result['suspended']) {
                            \Log::warning('User suspended due to payment failure: User ID ' . $share->user_id . ' (Level ' . $result['suspension_level'] . ', Duration: ' . $result['suspension_duration_hours'] . 'h)');
                        } else {
                            \Log::info('Payment failure recorded for User ID ' . $share->user_id . ' (Consecutive failures: ' . $result['consecutive_failures'] . ')');
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error handling payment failure for User ID ' . $share->user_id . ': ' . $e->getMessage());
                    }
                }
            }
        }
    }

    public function checkUnPaidReffMatureUser(){

        $users = User::withCount('shares')
            ->with(['refferalBy' => function($q){
                $q->withCount('shares')
                    ->whereHas('shares', function($q){
                    $q->where('is_ready_to_sell', 1);
                });
            }])->where('refferal_code', '!=', '')
            ->whereHas('shares', function($q){
                $q->where('is_ready_to_sell', 1);
            })->where('ref_amount', 0)->get();
        
        $sharesWillGet = get_gs_value('reffaral_bonus') ?? 100;
        foreach ($users as $user) {
            if($user->refferalBy){
                createRefferalBonus($user, $user->refferalBy);
                $user->ref_amount = $sharesWillGet;
                $user->save();
            }    
        }
        
        return 1;
    }

    /**
     * Automatically unsuspend users whose suspension period has expired
     */
    public function unsuspendExpiredUsers()
    {
        try {
            $expiredSuspensions = User::whereIn('status', ['suspend', 'suspended'])
                ->where('suspension_until', '<', now())
                ->whereNotNull('suspension_until')
                ->get();
            
            foreach ($expiredSuspensions as $user) {
                $user->update([
                    'status' => 'active',
                    'suspension_until' => null,
                    'suspension_reason' => null
                ]);
                
                \Log::info("Auto-unsuspended user: {$user->username} (ID: {$user->id})");
            }
            
            if ($expiredSuspensions->count() > 0) {
                \Log::info("Auto-unsuspended {$expiredSuspensions->count()} user(s) whose suspension period expired.");
            }
            
        } catch (\Exception $e) {
            \Log::error('Error in unsuspendExpiredUsers: ' . $e->getMessage());
        }
    }
}
