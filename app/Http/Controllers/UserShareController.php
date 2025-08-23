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
        $share = UserShare::with('pairedShares', 'refferal', 'payments',
                                'pairedShares.user:id,username')
                ->findOrFail($id);

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
        //     }elseif(\Carbon\Carbon::parse($pairedShare->created_at)->addHour(3) >= now() && $pairedShare->is_paid == 0){
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
        //     }elseif(\Carbon\Carbon::parse($pairedShare->created_at)->addHour(3) >= now() && $pairedShare->is_paid == 0){
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
        $share = UserShare::findOrFail($id);
        $pageTitle = 'Sold share view '.$share->ticket_no;

        return view('user-panel.share.sold-share-view', compact('pageTitle', 'share'));
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
