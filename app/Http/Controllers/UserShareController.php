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
        
        // Get pairing context to determine what to show
        $pairingContext = $this->getPairingContextForSoldShare($share);
        
        // Determine what pair history to show based on context
        $shouldShowPairHistory = $pairingContext['shouldShow'];
        $showBuyerHistory = $pairingContext['showBuyerHistory'];
        $showSellerHistory = $pairingContext['showSellerHistory'];
        $hasSellerPairings = $pairingContext['hasSellerPairings'];
        $hasBuyerPairings = $pairingContext['hasBuyerPairings'];

        return view('user-panel.share.sold-share-view', compact(
            'pageTitle', 
            'share', 
            'shouldShowPairHistory',
            'showBuyerHistory',
            'showSellerHistory',
            'hasSellerPairings',
            'hasBuyerPairings'
        ));
    }
    
    /**
     * Get pairing context to determine what pair history should be shown for a sold share
     * 
     * @param UserShare $share
     * @return array
     */
    private function getPairingContextForSoldShare($share)
    {
        // Check for seller-side pairings (current selling activity)
        $sellerPairings = UserSharePair::where('paired_user_share_id', $share->id)->exists();
        
        // Check for buyer-side pairings (historical buying activity)
        $buyerPairings = UserSharePair::where('user_share_id', $share->id)->exists();
        
        // Determine what to show based on share type and available pairings
        $context = [
            'hasSellerPairings' => $sellerPairings,
            'hasBuyerPairings' => $buyerPairings,
            'shouldShow' => false,
            'showSellerHistory' => false,
            'showBuyerHistory' => false,
        ];
        
        // If this is an admin-allocated share or other non-purchase share,
        // show all pair history as these are legitimate selling shares
        if ($share->get_from !== 'purchase') {
            $context['shouldShow'] = $sellerPairings || $buyerPairings;
            $context['showSellerHistory'] = $sellerPairings;
            $context['showBuyerHistory'] = $buyerPairings;
            return $context;
        }
        
        // For purchased shares that have transitioned to selling phase:
        // Always show current selling activity (seller-side pairings)
        // Only show old buying activity if there's no current selling activity
        if ($share->get_from === 'purchase') {
            if ($sellerPairings) {
                // Has current selling activity - show it
                $context['shouldShow'] = true;
                $context['showSellerHistory'] = true;
                $context['showBuyerHistory'] = false; // Hide old buying history to prevent confusion
            } elseif ($buyerPairings && $share->is_ready_to_sell == 0) {
                // No current selling activity but has old buying history and not ready to sell yet
                // Show buying history as it's still relevant
                $context['shouldShow'] = true;
                $context['showSellerHistory'] = false;
                $context['showBuyerHistory'] = true;
            } else {
                // No current selling activity and either no buying history or ready to sell
                // Don't show anything
                $context['shouldShow'] = false;
            }
        }
        
        return $context;
    }
    
    /**
     * Legacy method - kept for backward compatibility
     * Use getPairingContextForSoldShare() instead for new implementations
     * 
     * @param UserShare $share
     * @return bool
     */
    private function shouldShowPairHistoryForSoldShare($share)
    {
        $context = $this->getPairingContextForSoldShare($share);
        return $context['shouldShow'];
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
