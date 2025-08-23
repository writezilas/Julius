<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\Log;
use App\Models\Trade;
use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OthersController extends Controller
{
    public function bid(Request $request)
    {
        $data = $request->validate([
            'trade_id' => 'bail|required',
            'amount' => 'bail|required|numeric:min:0',
            'period' => 'bail|required',
        ]);

        $user = auth()->user();

        $trade = Trade::findOrFail($request->trade_id);

        $minTradeAmount = GeneralSetting::where('key', 'min_trading_price')->first();

        $maxTradeAmount = GeneralSetting::where('key', 'max_trading_price')->first();


        if ($request->amount < $minTradeAmount->value) {
            toastr()->info('Minimum trading amount is ' . $minTradeAmount->value);
            return back();
        }

        if ($request->amount > $maxTradeAmount->value) {
            toastr()->info('Maximum trading amount is ' . $maxTradeAmount->value);
            return back();
        }

        // if ($trade->price >= $request->amount) {
        //     toastr()->info('Amount is not enough to buy at lest one share. Please try with a another amount');
        //     return back();
        // }


        $userShares = UserShare::whereTradeId($trade->id)
            ->where('status', 'completed')
            ->where('is_ready_to_sell', 1)
            ->where('total_share_count', '>', 0)
            ->where('user_id', '!=', auth()->user()->id)
            ->whereHas('user', function ($query) {
                $query->whereIn('status', ['pending', 'fine']);
            })->get();
        
        $shareCount = $userShares->sum('total_share_count');
        // old code commented by md
        // foreach ($userShares as $userShare) {
        //     $shareCount += ($userShare->total_share_count - $userShare->sold_quantity);
        // }
        if (floor($request->amount / $trade->price) > $shareCount) {
            toastr()->error('Not enough shares right now. Try again later or try with less amount.');
            return back();
        }

        
        try {
            DB::beginTransaction();
            $ticketNo = 'AB-' . time() . rand(3, 8);

            $userShareWithTicket = UserShare::where('ticket_no', $ticketNo)->exists();
            $count = 2;

            if ($userShareWithTicket) {
                $data['ticket_no'] = $ticketNo . $count++;
            } else {
                $data['ticket_no'] = $ticketNo;
            }

            $data['user_id'] = auth()->user()->id;

            $sharesWillGet = floor($request->amount / $trade->price);
            $data['share_will_get'] = $sharesWillGet;
            $data['status'] = 'paired';
            
            $createdShare = UserShare::create($data);

            $totalShare = $sharesWillGet;
            $matchedShare = 0;
            // example: if user what to buy 1000 share so sort with the higer then the request amount, if there is not any so try pair with multiple users until the request amount is not equal to the matched share

            $highestShares = $userShares->where('total_share_count', '>=', $totalShare)->shuffle();
            $arrayShares = count($highestShares) ? $highestShares : $userShares->shuffle();
            
            $checkingArray = [];
            foreach ($arrayShares as $key => $share) {
                $currentShare = UserShare::findOrFail($share->id);
                $total_share_count = $currentShare->total_share_count;
                if ($total_share_count >= $totalShare) {
                    $currentShare->increment('hold_quantity', $totalShare);
                    $currentShare->decrement('total_share_count', $totalShare);
                    $matchedShare += $totalShare;

                    $checkingArray[$key]['cond'] = 'if';
                    $checkingArray[$key]['matchedShare'] = $matchedShare;
                    $checkingArray[$key]['totalShare'] = $totalShare;
                    $checkingArray[$key]['total_share_count'] = $total_share_count;

                    // update paired table
                    $sharePaired = new UserSharePair();
                    $sharePaired->user_id              = $user->id;
                    $sharePaired->user_share_id        = $createdShare->id;
                    $sharePaired->paired_user_share_id = $currentShare->id;
                    $sharePaired->share                = $totalShare;
                } else {
                    
                    $totalShare = abs($totalShare - $total_share_count);
                    $matchedShare += $total_share_count;

                    $checkingArray[$key]['cond'] = 'else';
                    $checkingArray[$key]['matchedShare']      = $matchedShare;
                    $checkingArray[$key]['totalShare']        = $totalShare;
                    $checkingArray[$key]['total_share_count'] = $total_share_count;
                    // update paired table
                    $sharePaired = new UserSharePair();
                    $sharePaired->user_id              = $user->id;
                    $sharePaired->user_share_id        = $createdShare->id;
                    $sharePaired->paired_user_share_id = $currentShare->id;
                    $sharePaired->share                = $total_share_count;
                    $currentShare->increment('hold_quantity', $total_share_count);
                    $currentShare->decrement('total_share_count', $total_share_count);
                }
                
                $sharePaired->save();    
                $currentShare->save();

                if ($sharesWillGet <= $matchedShare) {
                    break;
                }
            }

            // return $checkingArray;

            // Save log
            $log = new Log();
            $log->remarks = "Share bought successfully.";
            $log->type    = "share";
            $log->value   = $totalShare;
            $log->user_id = auth()->user()->id;
            $createdShare->logs()->save($log);

            DB::commit();
            toastr()->success('Share bought successfully. Navigate to bought shares page and make payment');
        } catch (\Exception $e) {
            \Log::error('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            DB::rollBack();
            toastr()->info('Shares NOT Bought. Try again');
        }

        return back();
    }


    public function notification_read($id)
    {
        $notification = auth()->user()->unreadNotifications->find($id);
        $notification->read_at = date('Y-m-H');
        $notification->save();
        if (isset($notification->data['for']) && $notification->data['for'] == 'payment-sent') {
            if (isset($notification->data['payment_id'])) {
                $payment = UserSharePayment::find($notification->data['payment_id']);
                return redirect()->route('sold-share.view', $payment->paired->paired_user_share_id);
            }
        } elseif (isset($notification->data['for']) && $notification->data['for'] == 'payment-received') {
            $payment = UserSharePayment::find($notification->data['payment_id']);
            return redirect()->route('bought-share.view', $payment->user_share_id);
        }
        return back();
    }
    
    public function notification_readAll()
    {
        $allNoti = auth()->user()->unreadNotifications->all();
        foreach($allNoti as $noti){
            $noti->read_at = date('Y-m-H');
            $noti->save();
        }
        toastr()->success('Marks as read successfully all the notification');
        return back();
    }
}
