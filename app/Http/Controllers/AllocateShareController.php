<?php

namespace App\Http\Controllers;

use App\Models\AllocateShareHistory;
use App\Models\Trade;
use App\Models\TradePeriod;
use App\Models\User;
use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllocateShareController extends Controller
{
    public function allocateShareHistory()
    {
        $pageTitle = 'Allocated share data';
        
        // Get AllocateShareHistory records with valid userShare and trade relationships
        $allocateShares = AllocateShareHistory::with(['userShare.trade', 'userShare.user'])
            ->whereHas('userShare', function ($query) {
                $query->whereHas('trade'); // Ensure the trade relationship exists
            })
            ->orderBy('id', 'DESC')
            ->get();
            
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
            // Admin allocated shares should start with countdown timer like regular shares
            $data['is_ready_to_sell'] = 0;
            $data['matured_at'] = null;
            // Set selling_started_at to start_date for admin allocations (they start selling immediately)
            $data['selling_started_at'] = $data['start_date'];

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

    public function pendingPaymentConfirmations(Request $request)
    {
        $pageTitle = 'Pending Payment Confirmations';
        
        // Build the base query with proper eager loading
        $query = UserSharePair::with([
            'pairedUserShare:id,status,ticket_no,trade_id', 
            'pairedShare:id,user_id,status,ticket_no,trade_id,amount', 
            'pairedShare.user:id,name,username', 
            'pairedShare.trade:id,name',
            'payment:id,user_share_pair_id,amount,status,name,number,txs_id,note_by_sender,created_at'
        ])
        ->where('is_paid', 0)
        ->whereHas('payment');

        // Apply filters
        if ($request->has('status') && $request->status != '') {
            if ($request->status == 'pending') {
                $query->whereHas('payment', function($q) {
                    $q->where('status', 'paid');
                });
            } elseif ($request->status == 'failed') {
                $query->whereHas('payment', function($q) {
                    $q->where('status', 'failed');
                });
            }
        }

        if ($request->has('customer') && $request->customer != '') {
            $query->whereHas('pairedShare.user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer . '%')
                  ->orWhere('username', 'like', '%' . $request->customer . '%');
            });
        }

        if ($request->has('ticket_no') && $request->ticket_no != '') {
            $query->whereHas('pairedShare', function($q) use ($request) {
                $q->where('ticket_no', 'like', '%' . $request->ticket_no . '%');
            });
        }

        if ($request->has('trade_id') && $request->trade_id != '') {
            $query->whereHas('pairedShare', function($q) use ($request) {
                $q->where('trade_id', $request->trade_id);
            });
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereHas('payment', function($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            });
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereHas('payment', function($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            });
        }

        // Get paginated results
        $pendingShares = $query->orderBy('id', 'DESC')->paginate(20);
        
        // Get filter data
        $trades = Trade::where('status', 1)->get();
        
        // Get statistics
        $totalPending = UserSharePair::where('is_paid', 0)->whereHas('payment')->count();
        $totalToday = UserSharePair::where('is_paid', 0)
            ->whereHas('payment', function($q) {
                $q->whereDate('created_at', today());
            })->count();
        $totalThisWeek = UserSharePair::where('is_paid', 0)
            ->whereHas('payment', function($q) {
                $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            })->count();
            
        return view('admin-panel.share-management.pending-payment-confirmations', 
            compact('pageTitle', 'pendingShares', 'trades', 'totalPending', 'totalToday', 'totalThisWeek'));
    }

}
