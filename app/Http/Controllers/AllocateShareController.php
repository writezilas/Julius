<?php

namespace App\Http\Controllers;

use App\Models\AllocateShareHistory;
use App\Models\Trade;
use App\Models\TradePeriod;
use App\Models\User;
use App\Models\UserShare;
use App\Models\UserSharePair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllocateShareController extends Controller
{
    public function allocateShareHistory()
    {
        $pageTitle = 'Allocated share data';
        $allocateShares = AllocateShareHistory::with('userShare')->orderBy('id', 'DESC')->get();
        return view('admin-panel.share-management.allocate-share-history', compact('pageTitle', 'allocateShares'));
    }
    public function allocateShare()
    {
        $pageTitle = 'Allocate share';
        $users = User::where('role_id', 2)->get();
        $trades = Trade::where('status', 1)->get();
        $periods = TradePeriod::where('status', 1)->get();

        return view('admin-panel.share-management.allocate-share', compact('pageTitle', 'users', 'trades', 'periods'));
    }

    public function saveAllocateShare(Request $request)
    {
        $request->validate([
            'trade_id' => 'required',
            'to_user' => 'required',
            'no_of_share_for_transfer' => 'required',
            'period' => 'required',
        ]);

        $trade = Trade::where('id', $request->trade_id)->first();

        $data = [
            'trade_id' => $request->trade_id,
            'amount' => $trade->price * $request->no_of_share_for_transfer,
            'period' => $request->period,
        ];

        $user = User::findOrFail($request->to_user);

//        $userShares = UserShare::where('user_id', $request->from_user)
//            ->where('trade_id', $request->trade_id)
//            ->where('is_ready_to_sell', 1)
//            ->where('total_share_count', '>', 0)
//            ->get();

//        if($userShares->sum('total_share_count') < $request->no_of_share_for_transfer) {
//            toastr()->error('Not enough shares right now. Try again later');
//            return back();
//        }

        try {

            DB::beginTransaction();

            $ticketNo = 'AB-'.time().rand(3,8);

            $userShareWithTicket = UserShare::where('ticket_no', $ticketNo)->exists();
            $count = 2;

            if($userShareWithTicket) {
                $data['ticket_no'] = $ticketNo. $count++;
            }else {
                $data['ticket_no'] = $ticketNo;
            }

            $data['user_id'] = $request->to_user;

            $sharesWillGet = $request->no_of_share_for_transfer;

            $data['share_will_get'] = $sharesWillGet;
            $data['total_share_count'] = $sharesWillGet;
            $data['start_date'] = date_format(now(),"Y/m/d H:i:s");
            $data['status'] = 'completed';
            $data['get_from'] = 'allocated-by-admin';

            $createdShare = UserShare::create($data);

            $allocateShareHistoryData = [
                'user_share_id' => $createdShare->id,
                'shares' => $sharesWillGet,
                'created_by' => auth()->user()->id,
            ];

            $allocateShareHistory = AllocateShareHistory::create($allocateShareHistoryData);

            DB::commit();
            toastr()->success('Share Allocate successfully');

        }catch (\Exception $e) {
            \Log::error('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            DB::rollBack();
            toastr()->info('Failed to allocate. Try again');
        }

        return back();

    }

    public function transferShare()
    {
        $pageTitle = 'Transfer share';
        $users = User::where('role_id', 2)->get();
        $trades = Trade::where('status', 1)->get();
        $periods = TradePeriod::where('status', 1)->get();
        return view('admin-panel.share-management.transfer-share', compact('pageTitle', 'users', 'trades', 'periods'));
    }

    public function saveTransferShare(Request $request)
    {
        $request->validate([
            'from_user' => 'required',
            'trade_id' => 'required',
            'available_share' => 'required',
            'to_user' => 'required',
            'no_of_share_for_transfer' => 'required',
            'period' => 'required',
        ]);

        $trade = Trade::where('id', $request->trade_id)->first();

        $data = [
            'trade_id' => $request->trade_id,
            'amount' => $trade->price * $request->no_of_share_for_transfer,
            'period' => $request->period,
        ];

        $user = User::findOrFail($request->to_user);

        $userShares = UserShare::where('user_id', $request->from_user)
            ->where('trade_id', $request->trade_id)
            ->where('is_ready_to_sell', 1)
            ->where('total_share_count', '>', 0)
            ->get();

        if($userShares->sum('total_share_count') < $request->no_of_share_for_transfer) {
            toastr()->error('Not enough shares right now. Try again later');
            return back();
        }

        try {

            DB::beginTransaction();

            $ticketNo = 'AB-'.time().rand(3,8);

            $userShareWithTicket = UserShare::where('ticket_no', $ticketNo)->exists();
            $count = 2;

            if($userShareWithTicket) {
                $data['ticket_no'] = $ticketNo. $count++;
            }else {
                $data['ticket_no'] = $ticketNo;
            }

            $data['user_id'] = $request->to_user;


            $sharesWillGet = $request->no_of_share_for_transfer;


            $data['share_will_get'] = $sharesWillGet;
            $data['total_share_count'] = $sharesWillGet;
            $data['status'] = 'completed';
            $data['is_ready_to_sell'] = 1;
            $data['get_from'] = "transferred-by-admin";
            $data['start_date'] = date_format(now()->subDays($request->period),"Y/m/d H:i:s");

            $createdShare = UserShare::create($data);

            $totalShare = $sharesWillGet;
            $matchedShare = 0;
            foreach ($userShares as $share) {
                if($share->total_share_count > $share->sold_quantity) {
                    $currentShare = UserShare::findOrFail($share->id);
                    if($currentShare->total_share_count >= $totalShare) {
//                        $currentShare->hold_quantity = $totalShare;
                        $currentShare->total_share_count = $share['total_share_count'] - $totalShare;
                        $matchedShare += $totalShare;

                        // update paired table
                        $sharePaired = new UserSharePair();
                        $sharePaired->user_id = $user->id;
                        $sharePaired->user_share_id = $createdShare->id;
                        $sharePaired->paired_user_share_id = $currentShare->id;
                        $sharePaired->share = $totalShare;
                        $sharePaired->is_paid = 1;

                    }else {
                        $currentShare->total_share_count = 0;
//                        $currentShare->hold_quantity = $currentShare->total_share_count;
                        $matchedShare += $currentShare->total_share_count;

                        // update paired table
                        $sharePaired = new UserSharePair();
                        $sharePaired->user_id = $user->id;
                        $sharePaired->user_share_id = $createdShare->id;
                        $sharePaired->paired_user_share_id = $currentShare->id;
                        $sharePaired->share = $currentShare->total_share_count;
                        $sharePaired->is_paid = 1;
                    }

                    $sharePaired->save();
                    $currentShare->save();
                }

                if($totalShare === $matchedShare) {
                    break;
                }
            }

            DB::commit();
            toastr()->success('Share Transferred successfully');

        }catch (\Exception $e) {
            \Log::error('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            DB::rollBack();
            toastr()->info('Failed to Transfer share. Try again');
        }

        return back();

    }


    public function getShareByTradeAndUser(Request $request)
    {
        $shareCount = UserShare::where('user_id', $request->userId)->where('trade_id', $request->tradeId)->sum('total_share_count');
        return $shareCount;
    }

    public function destroy($share_id)
    {
        $share = UserShare::findOrFail($share_id);
        if($share->delete()) {
            toastr()->success('Allocated share removed successfully');
        }else {
            toastr()->info('Failed to remove allocated share. Try again');
        }
        return back();
    }

}
