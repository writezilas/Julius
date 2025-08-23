<?php

namespace App\Console\Commands;

use App\Models\TradePeriod;
use App\Models\User;
use App\Models\UserShare;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateSharesCommand extends Command
{
    protected $signature = 'update-shares';
    protected $description = 'Update shares every minute';

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
        $this->cronForEveryUpdate();
        $this->info('Shares updated successfully at ' . now());
    }

    public function cronForEveryUpdate()
    {
        // make all share to ready to sell
        try {
            DB::beginTransaction();

            $shares = UserShare::with('tradePeriod', 'pairedShares')
                ->whereIn('status', ['completed', 'paired'])
                ->where('is_ready_to_sell', 0)->get();
            $whereIn = ['allocated-by-paid-share', 'purchase'];
            $completedShares = $shares->whereIn('get_form', $whereIn)->where('status', 'completed');
            $pairedShares    = $shares->where('status', 'paired');

            if (count($completedShares) > 0) {
                $this->updateAsReadyToSell($completedShares);
            }

            if (count($pairedShares) > 0) {
                $bought_time = get_gs_value('bought_time');
                $pairedShares = $pairedShares
                    ->where('created_at', '<=', now()->subMinutes($bought_time));
                $this->updateShareStatusAsFailed($pairedShares);
            }
            $this->checkUnPaidReffMatureUser();
            DB::commit();
            return 1;
        } catch (\Exception $e) {
            \Log::error('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            DB::rollBack();
            return 0;
        }
    }

    public function updateAsReadyToSell($shares)
    {
        $tradePeriods = TradePeriod::where('status', 1)->get();
        foreach ($tradePeriods as $period) {
            // if day 1 so it means 24 hours ago
            $latestShares = $shares->where('period', $period->days)
                ->where('created_at', '<=', now()->subDays($period->days));

            foreach ($latestShares as $share) {
                $share->is_ready_to_sell = 1;
                $per     = $period->percentage;
                $earning = ($share->share_will_get * $per / 100) * $share->trade->price;
                $share->profit_share = $earning;
                $share->increment('total_share_count', $earning);
                $share->save();
            }
        }

        return 1;
    }

    public function updateShareStatusAsFailed($shares)
    {
        $bought_time = get_gs_value('bought_time') ?: 1; // Default 1 minute
        
        foreach ($shares as $key => $share) {
            // Check if the payment timeout has been reached
            if ($share->created_at->addMinutes($bought_time)->isPast()) {
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
}

