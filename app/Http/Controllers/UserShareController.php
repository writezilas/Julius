<?php

namespace App\Http\Controllers;

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserShareController extends Controller
{
    public function boughtShareView($id)
    {
        // Find the share and check if it belongs to the current user
        $share = UserShare::with('pairedShares', 'refferal', 'payments',
                                'pairedShares.user:id,username')
                ->where('user_id', auth()->id())
                ->findOrFail($id);
                
        // Additional check for admin-allocated shares that should be viewable
        if (!$share) {
            abort(403, 'Unauthorized access to this share.');
        }

        $pairedIds = $share->pairedShares->pluck('paired_user_share_id')->toArray();


        $groupByShare = UserShare::whereIn('id', $pairedIds)
                        ->with(['user:id,username,name,business_profile', 
                        'pairedWithThis' => function($q) use($id){
                            $q->where('user_share_id', $id);
                        }])
                        ->get()
                        ->groupBy('user.username'); 
        // return $groupByShare;
        
        // $boughtViewData = [];

        // foreach($share->pairedShares as $pairedShare){
        //     $profileData = json_decode($pairedShare->pairedShare->user->business_profile);
        //     $payment = UserSharePayment::where('user_share_pair_id', $pairedShare->id)->exists();
        //     $status = [];    
                    
        //     if($pairedShare->is_paid == 1){
        //         $status = [
        //             'text' => 'Paid and confirmed',
        //             'class' => 'badge bg-success'
        //         ];
        //     }elseif($payment == 1){
        //         $status = [
        //             'text' => 'Paid, waiting for confirmation',
        //             'class' => 'badge bg-info'
        //         ];
        //     }elseif(\Carbon\Carbon::parse($pairedShare->created_at)->addMinutes($share->payment_deadline_minutes ?? 60) >= now() && $pairedShare->is_paid == 0){
        //         $status = [
        //             'text' => 'Waiting for payment',
        //             'class' => 'badge bg-primary'
        //         ];
        //     }else{
        //         $status = [
        //             'text' => 'Payment time expired',
        //             'class' => 'badge bg-danger'
        //         ];
        //     }

        //     $action = [];
            

        //     if($pairedShare->is_paid == 1){
        //         $action = [
        //             'text' => 'Paid and confirmed',
        //             'class' => 'badge bg-success'
        //         ];
        //     }elseif($payment){
        //         $action = [
        //             'text' => 'Paid, waiting for confirmation',
        //             'class' => 'badge bg-info'
        //         ];
        //     }elseif(\Carbon\Carbon::parse($pairedShare->created_at)->addMinutes($share->payment_deadline_minutes ?? 60) >= now() && $pairedShare->is_paid == 0){
        //         $action = [
        //             'text' => 'modal',
        //             'class' => 'btn btn-primary',
        //         ];
        //     }else{
        //         $action = [
        //             'text' => 'Payment time expired',
        //             'class' => 'badge bg-danger'
        //         ];
        //     }

        //     $boughtViewData[$pairedShare->pairedShare->user->username][] = [
        //         'name'       => $pairedShare->pairedShare->user->name,
        //         'username'   => $pairedShare->pairedShare->user->username,
        //         'mpesa_name' => $profileData->mpesa_name,
        //         'mpesa_no'   => $profileData->mpesa_no,
        //         'share'      => $pairedShare->share,
        //         'status'     => $status,
        //         'action'     => $action,
        //     ];
        // }
        
        // return $boughtViewData;
        $pageTitle = 'Share view '.$share->ticket_no;

        return view('user-panel.share.bought-share-view', compact('pageTitle', 'share', 'groupByShare'));
    }
    public function soldShareView($id)
    {
        // Find the share and check if it belongs to the current user
        $share = UserShare::where('user_id', auth()->id())->findOrFail($id);
        $pageTitle = 'Sold share view '.$share->ticket_no;
        
        // Allow purchased shares to be viewed on the sold shares page when they have transitioned
        // to the selling phase, but hide their pair history to prevent confusion
        
        // Determine if we should show pair history
        // Only show pair history for shares that were originally selling shares
        // Do NOT show pair history for shares that have transitioned from bought to sold phase
        $shouldShowPairHistory = $this->shouldShowPairHistoryForSoldShare($share);

        return view('user-panel.share.sold-share-view', compact('pageTitle', 'share', 'shouldShowPairHistory'));
    }
    
    /**
     * Determine if pair history should be shown for a sold share
     * 
     * @param UserShare $share
     * @return bool
     */
    private function shouldShowPairHistoryForSoldShare($share)
    {
        // If the share was originally purchased (get_from = 'purchase'),
        // and it has transitioned to the sold phase, we should NOT show pair history
        // because it will create confusion when the shares mature and are paired with new buyers
        
        // Case 1: If this is a purchased share that has matured and is ready to sell,
        // do NOT show old pair history from the buying phase
        if ($share->get_from === 'purchase' && $share->is_ready_to_sell == 1) {
            return false;
        }
        
        // Case 2: If this is a purchased share in countdown mode (not ready to sell yet),
        // do NOT show old pair history either as it's still in transition
        if ($share->get_from === 'purchase' && $share->is_ready_to_sell == 0) {
            return false;
        }
        
        // Case 3: If this is an admin-allocated share or other non-purchase share,
        // show pair history as these are legitimate selling shares
        if ($share->get_from !== 'purchase') {
            return true;
        }
        
        // Default: don't show pair history for safety
        return false;
    }

    public function updateShareStatusAsFailed(Request $request) {
        try {
            DB::beginTransaction();

            $share = UserShare::findOrFail($request->id);
            $share->status = $request->status;
            $share->save();

            foreach ($share->pairedShares as $pairedShare) {
                $userShare = UserShare::findOrFail($pairedShare->paired_user_share_id);
                $userShare->decrement('hold_quantity', $pairedShare->share);
                $userShare->increment('total_share_count', $pairedShare->share); 
            }
            DB::commit();
            return 1;
        }catch (\Exception $e) {
            \Log::error('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            DB::rollBack();
            return 0;
        }

    }

    public function updateAsReadyToSell(Request $request)
    {   
        $share = UserShare::with('tradePeriod')->findOrFail($request->id);
        $share->is_ready_to_sell = 1;
        $per = $share->tradePeriod->percentage;
        $earning = ($share->share_will_get * $per / 100) * $share->trade->price;
        $share->profit_share = $earning;
        $share->save();
        return 1;
    }

   


}
