<?php

use App\Models\UserShare;

function get_general_settings($name) {
	    $config = null;
	    foreach (GeneralSetting::all() as $setting) {
	        if ($setting['key'] == $name) {
	            $config = json_decode($setting['value'], true);
	        }
	    }
	    return $config;
	}

	function get_gs_value($key, $full = false) {

	    $config = GeneralSetting::where('key', $key)->first();

	    if(!$full){
	        return $config->value;
	    }
	    return $config;
}

function isAllPermissionOfModuleActive($permissionOfModule, $allPermission): bool
{
    $status = true;
    foreach($permissionOfModule as $permissionItem) {
        if(!in_array($permissionItem->name, $allPermission)) {
            $status = false;
        }
    }
    return $status;
}

//highlights the selected navigation on admin panel
if (! function_exists('areActiveRoutes')) {
    function areActiveRoutes(array $routes, $output = "active")
    {
        if (in_array(Route::currentRouteName(), $routes)) {
            return $output;
        }
    }
}

//highlights the selected navigation on admin panel
if (! function_exists('areActiveRoutesBool')) {
    function areActiveRoutesBool(array $routes, $output = true)
    {
        if (in_array(Route::currentRouteName(), $routes)) {
            return $output;
        }
        return false;
    }
}

if (! function_exists('checkAvailableSharePerTrade')) {
    function checkAvailableSharePerTrade($tradeId)
    {
         $userShares = \App\Models\UserShare::whereTradeId($tradeId)->whereStatus('completed')->where('is_ready_to_sell', 1)->where('user_id', '!=', auth()->user()->id)->get();

        $shareCount = 0;
        foreach ($userShares as $userShare) {
//            if(\Carbon\Carbon::parse($userShare->start_date)->addDays($userShare->period) < \Carbon\Carbon::now()) {
                $shareCount += $userShare->total_share_count;
//            }
        }

       return $shareCount;
    }
}

if (! function_exists('formatPrice')) {
    function formatPrice($price)
    {
        return 'Ksh '. $price;
    }
}

if (! function_exists('updateMaturedShareStatus')) {
    function updateMaturedShareStatus() {
        $shares = \App\Models\UserShare::whereStatus('completed')->whereNotNull('start_date')->where('is_ready_to_sell', 0)->get();

        foreach ($shares as $key => $share) {
            if(\Carbon\Carbon::parse($share->start_date)->addDays($share->period) < \Carbon\Carbon::now()) {

                $profit = calculateProfitOfShare($share);

                $share->is_ready_to_sell = 1;
                $share->matured_at = date_format(now(),"Y/m/d H:i:s");
                $share->profit_share = $profit;
                $share-> total_share_count = $share->total_share_count + $profit;
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

if (! function_exists('calculateProfitOfShare')) {
    function calculateProfitOfShare($share)
    {
        $trade = \App\Models\Trade::where('id', $share->trade_id)->first();
        $period = \App\Models\TradePeriod::where('days', $share->period)->first();

        return ($period->percentage / 100) * $share->total_share_count;
    }
}

if (! function_exists('updatePaymentFailedShareStatus')) {
    function updatePaymentFailedShareStatus()
    {
        $shares = \App\Models\UserShare::whereStatus('paired')->whereNull('start_date')->get();

        foreach ($shares as $key => $share) {
            if(\Carbon\Carbon::parse($share->created_at)->addHours(3) < \Carbon\Carbon::now()) {

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

if(!function_exists('getSoldShareStatus')) {
    function getSoldShareStatus($share): string
    {
        if($share->start_date != '' && $share->is_reqdy_to_sell === 0) {
            return 'Active';
        }elseif ((($share->share_will_get + $share->profit_share) > $share->total_share_count) && ($share->total_share_count !== 0 || $share->hold_quantity !== 0)) {
            return 'paired';
        }elseif ($share->total_share_count === 0 && $share->hold_quantity === 0) {
            return 'Completed';
        }else {
            return 'Pending';
        }
    }
}

if(!function_exists('unblokTemporaryBlockedUsers')) {
    function unblockTemporaryBlockedUsers()
    {
        $blockedUsers = \App\Models\User::where('status', 'block')->whereNotNull('block_until')->get();

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
