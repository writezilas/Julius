<?php

namespace App\Http\Controllers;

use App\Models\UserShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserShareController extends Controller
{
    public function boughtShareView($id)
    {
        $share = UserShare::findOrFail($id);
        $pageTitle = 'Share view '.$share->ticket_no;

        return view('user-panel.share.bought-share-view', compact('pageTitle', 'share'));
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
                $userShare->hold_quantity -= $pairedShare->share;
                $userShare->total_share_count += $pairedShare->share;
                $userShare->save();
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
        $share = UserShare::findOrFail($request->id);
        $share->is_ready_to_sell = 1;
        $share->save();
        return 1;
    }


}
