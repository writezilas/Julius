<?php

use App\Models\AllocateShareHistory;
use App\Models\GeneralSetting;
use App\Models\Invoice;
use App\Models\Market;
use App\Models\Trade;
use App\Models\UserShare;
use Illuminate\Support\Facades\DB;

function get_general_settings($name)
{
    $config = null;
    foreach (GeneralSetting::all() as $setting) {
        if ($setting['key'] == $name) {
            $config = json_decode($setting['value'], true);
        }
    }
    return $config;
}

function get_gs_value($key, $full = false)
{
    $config = GeneralSetting::where('key', $key)->first();
    if (!$full) {
        return $config->value;
    }
    return $config;
}

function isAllPermissionOfModuleActive($permissionOfModule, $allPermission): bool
{
    $status = true;
    foreach ($permissionOfModule as $permissionItem) {
        if (!in_array($permissionItem->name, $allPermission)) {
            $status = false;
        }
    }
    return $status;
}

//highlights the selected navigation on admin panel
if (!function_exists('areActiveRoutes')) {
    function areActiveRoutes(array $routes, $output = "active")
    {
        if (in_array(Route::currentRouteName(), $routes)) {
            return $output;
        }
    }
}

//highlights the selected navigation on admin panel
if (!function_exists('areActiveRoutesBool')) {
    function areActiveRoutesBool(array $routes, $output = true)
    {
        if (in_array(Route::currentRouteName(), $routes)) {
            return $output;
        }
        return false;
    }
}

if (!function_exists('checkAvailableSharePerTrade')) {
    function checkAvailableSharePerTrade($tradeId)
    {
        return  \App\Models\UserShare::whereTradeId($tradeId)
            ->whereStatus('completed')
            ->where('is_ready_to_sell', 1)
            ->where('user_id', '!=', auth()->user()->id)
            ->whereHas('user', function ($query) {
                $query->whereIn('status', ['pending', 'fine']);
            })->sum('total_share_count');

        $shareCount = 0;
        foreach ($userShares as $userShare) {
            //            if(\Carbon\Carbon::parse($userShare->start_date)->addDays($userShare->period) < \Carbon\Carbon::now()) {
            $shareCount += $userShare->total_share_count;
            //            }
        }

        return $shareCount;
    }
}

if (!function_exists('formatPrice')) {
    function formatPrice($price)
    {
        return 'Ksh ' . $price;
    }
}

if (!function_exists('updateMaturedShareStatus')) {
    function updateMaturedShareStatus()
    {
        $shares = \App\Models\UserShare::whereStatus('completed')->whereNotNull('start_date')->where('is_ready_to_sell', 0)->get();

        foreach ($shares as $key => $share) {
            if (\Carbon\Carbon::parse($share->start_date)->addDays($share->period) < \Carbon\Carbon::now()) {

                $profit = calculateProfitOfShare($share);

                $share->is_ready_to_sell = 1;
                $share->matured_at = date_format(now(), "Y/m/d H:i:s");
                $share->profit_share = $profit;
                $share->total_share_count = $share->total_share_count + $profit;
                $share->save();


                $profitHistoryData = [
                    'user_share_id' => $share->id,
                    'shares' => $profit,
                ];

                \App\Models\UserProfitHistory::create($profitHistoryData);

                Log::info('Share update as ready to sell: ' . $share->id);
            }
        }
    }
}

if (!function_exists('calculateProfitOfShare')) {
    function calculateProfitOfShare($share)
    {
        $trade = \App\Models\Trade::where('id', $share->trade_id)->first();
        $period = \App\Models\TradePeriod::where('days', $share->period)->first();

        return ($period->percentage / 100) * $share->total_share_count;
    }
}

if (!function_exists('updatePaymentFailedShareStatus')) {
    function updatePaymentFailedShareStatus()
    {
        $shares = \App\Models\UserShare::whereStatus('paired')->whereNull('start_date')->get();

        foreach ($shares as $key => $share) {
            if (\Carbon\Carbon::parse($share->created_at)->addHours(3) < \Carbon\Carbon::now()) {

                $share->status = 'failed';
                $share->save();

                foreach ($share->pairedShares as $pairedShare) {
                    $userShare = UserShare::findOrFail($pairedShare->paired_user_share_id);
                    $userShare->hold_quantity -= $pairedShare->share;
                    $userShare->total_share_count += $pairedShare->share;
                    $userShare->save();
                }

                Log::info('Payment failed share status update successfully: ' . $share->id);
            }
        }
    }
}

if (!function_exists('getSoldShareStatus')) {
    function getSoldShareStatus($share): string
    {
        if ($share->start_date != '' && $share->is_reqdy_to_sell === 0) {
            return 'Active';
        } elseif ((($share->share_will_get + $share->profit_share) > $share->total_share_count) && ($share->total_share_count !== 0 || $share->hold_quantity !== 0)) {
            return 'paired';
        } elseif ($share->total_share_count === 0 && $share->hold_quantity === 0) {
            return 'Completed';
        } else {
            return 'Pending';
        }
    }
}

if (!function_exists('unblokTemporaryBlockedUsers')) {
    function unblockTemporaryBlockedUsers()
    {
        $blockedUsers = \App\Models\User::where('status', 'suspend')->get();

        foreach ($blockedUsers as $user) {
            if (\Carbon\Carbon::parse($user->bock_until) < \Carbon\Carbon::now()) {
                $user->status = 'pending';
                $user->block_until = null;
                $user->save();
                Log::info('user unblocked for: ' . $user->username);
            }
        }
    }
}
if (!function_exists('saveAllocateShare')) {
    function saveAllocateShare($userID, $share, $sharesWillGet, $key = 0)
    {
        // Prevent allocation if shares to get is 0 or negative
        if ($sharesWillGet <= 0) {
            \Log::warning('Attempted to allocate 0 or negative shares. UserID: ' . $userID . ', SharesWillGet: ' . $sharesWillGet);
            return false;
        }

        $trade = Trade::where('id', $share->trade_id)->first();

        if (!$trade) {
            \Log::error('Trade not found for share allocation. Trade ID: ' . $share->trade_id);
            return false;
        }

        $data = [
            'trade_id' => $share->trade_id,
            'amount'   => $trade->price * $sharesWillGet,
            'period'   => $share->period,
        ];

        DB::beginTransaction();

        $ticketNo = 'AB-'.time().rand(3,8).$userID.$key;

        $userShareWithTicket = UserShare::where('ticket_no', $ticketNo)->exists();
        $count = 2;

        if($userShareWithTicket) {
            $data['ticket_no'] = $ticketNo. $count++;
        }else {
            $data['ticket_no'] = $ticketNo;
        }

        $data['user_id'] = $userID;

        $data['share_will_get']    = $sharesWillGet;
        $data['total_share_count'] = $sharesWillGet;
        $data['start_date']        = date_format(now(),"Y/m/d H:i:s");
        $data['status']            = 'completed';
        
        $data['get_from']          = 'allocated-by-paid-share';

        $createdShare = UserShare::create($data);
        $allocateShareHistoryData = [
            'user_share_id' => $createdShare->id,
            'shares'        => $sharesWillGet,
            'created_by'    => 1,
        ];
        // AllocateShareHistory::create($allocateShareHistoryData);
        DB::commit();
        return 'ds';
    }
}
if (!function_exists('get_markets')) {
    function get_markets()
    {
        return Market::select('open_time', 'close_time')
                ->orderBy('open_time')->get();
    }
}
if (!function_exists('createRefferalBonus')) {
    function createRefferalBonus($user, $refferal){
        $trade = Trade::where('id', 1)->first();

        $sharesWillGet = get_gs_value('reffaral_bonus') ?? 100;
        $data = [
            'trade_id' => $trade->id,
            'amount' => $trade->price * $sharesWillGet,
            'period' => 1,
        ];

        $ticketNo = 'AB-'.time().rand(3,8).$user->id;

        $userShareWithTicket = UserShare::where('ticket_no', $ticketNo)->exists();
        $count = 2;

        if($userShareWithTicket) {
            $data['ticket_no'] = $ticketNo. $count++;
        }else {
            $data['ticket_no'] = $ticketNo;
        }

        $user->ref_amount = $sharesWillGet;
        $user->save();
        
        $data['user_id'] = $refferal->id;
        $data['share_will_get']    = $sharesWillGet;
        $data['total_share_count'] = $sharesWillGet;
        $data['start_date']        = date_format(now(),"Y/m/d H:i:s");
        $data['status']            = 'completed';
        $data['is_ready_to_sell']  = 1;
        $data['get_from']          = 'refferal-bonus';

        $createdShare = UserShare::create($data);

        $allocateShareHistoryData = [
            'user_share_id' => $createdShare->id,
            'shares'        => $sharesWillGet,
            'created_by'    => $user->id,
        ];
        AllocateShareHistory::create($allocateShareHistoryData);

        $oldamount = UserShare::where('status', 'completed')
                        ->where('user_id', $refferal->id)
                        ->sum('amount');

        $invoiceData = [
            'user_id'      => $refferal->id,
            'reff_user_id' => $user->id,
            'share_id'     => $createdShare->id,
            'old_amount'   => ($oldamount - $sharesWillGet),
            'add_amount'   => $sharesWillGet,
            'new_amount'   => $oldamount,
            'type'         => 'refferal-bonus',
        ];
        Invoice::create($invoiceData);
    }
}
if(!function_exists('send_sms')){
    function send_sms($sms){

        $request = [
            "userid"         => "driftwood",
            "password"       => "Juli2011@",
            "senderid"       => "DRIFTWOODHW",
            "msgType"        => "text",
            "duplicatecheck" => "true",
            "sendMethod"     => "quick",
            "sms" => $sms,
        ];
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => 'https://portal.zettatel.com/SMSApi/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($request),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        
        return $response;
    }
}