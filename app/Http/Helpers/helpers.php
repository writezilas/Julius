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
        return $config ? $config->value : null;
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
        // Get the trade info for better logging
        $trade = \App\Models\Trade::find($tradeId);
        $tradeName = $trade ? $trade->name : 'Unknown Trade';        
        
        $query = \App\Models\UserShare::whereTradeId($tradeId)
            ->whereStatus('completed')
            ->where('is_ready_to_sell', 1)
            ->where('total_share_count', '>', 0); // Only shares with available count
        
        // Exclude current user if authenticated
        $userId = null;
        $userName = 'Guest';
        if (auth()->check()) {
            $userId = auth()->user()->id;
            $userName = auth()->user()->name;
            $query->where('user_id', '!=', $userId);
            
            // Log this for debugging
            \Illuminate\Support\Facades\Log::debug("Market availability check", [
                'user_id' => $userId,
                'user_name' => $userName,
                'trade_id' => $tradeId,
                'trade_name' => $tradeName,
                'exclude_own_shares' => true
            ]);
        }
        
        // Check for users with active status (not suspended/banned)
        $query->whereHas('user', function ($subQuery) {
            $subQuery->whereIn('status', ['active', 'pending', 'fine']);
        });
        
        // Get the total count
        $total = $query->sum('total_share_count');
        
        // Log the result for debugging
        if (auth()->check()) {
            \Illuminate\Support\Facades\Log::debug("Market availability result", [
                'user_id' => $userId,
                'user_name' => $userName,
                'trade_id' => $tradeId,
                'trade_name' => $tradeName,
                'available_shares' => $total
            ]);
        }
        
        return $total;
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
        // Find shares that are in completed status and ready for investment maturity checking
        $shares = \App\Models\UserShare::whereStatus('completed')
            ->whereNotNull('start_date')
            ->where('is_ready_to_sell', 0)
            ->get();
            
        $enhancedTimerService = new \App\Services\EnhancedTimerManagementService();

        foreach ($shares as $key => $share) {
            // For purchased shares, use the new selling timer system
            if ($share->get_from === 'purchase') {
                // Check if selling timer has been started
                if (!$share->selling_started_at) {
                    // Selling timer should be started by payment confirmation, not here
                    // If share is completed but no selling timer, it means payment was confirmed but timer wasn't started
                    \Log::warning("Purchased share {$share->ticket_no} is completed but has no selling timer - payment confirmation may have failed to start timer");
                    continue; // Skip this share until selling timer is properly started
                }
                
                // Check if share should mature using selling timer
                if ($enhancedTimerService->shouldShareMature($share)) {
                    $profit = calculateProfitOfShare($share);

                    // Mature the share
                    $share->is_ready_to_sell = 1;
                    $share->matured_at = date_format(now(), "Y/m/d H:i:s");
                    $share->profit_share = $profit;
                    $share->total_share_count = $share->total_share_count + $profit;
                    
                    // Clean up legacy timer fields but keep selling timer data
                    $share->timer_paused = 0;
                    $share->timer_paused_at = null;
                    
                    $share->save();

                    $profitHistoryData = [
                        'user_share_id' => $share->id,
                        'shares' => $profit,
                    ];

                    \App\Models\UserProfitHistory::create($profitHistoryData);

                    Log::info('Share matured using new selling timer system: ' . $share->id, [
                        'ticket_no' => $share->ticket_no,
                        'user_id' => $share->user_id,
                        'profit_added' => $profit,
                        'selling_started_at' => $share->selling_started_at,
                        'timer_type' => 'selling_timer'
                    ]);
                }
            } else {
                // For admin-allocated shares, use legacy timer system
                // Skip paused shares (legacy behavior)
                if ($share->timer_paused) {
                    continue;
                }

                // Get adjusted timer to account for paused duration (legacy)
                $paymentFailureService = new \App\Services\PaymentFailureService();
                $timerInfo = $paymentFailureService->getAdjustedShareTimer($share);
                $adjustedEndTime = $timerInfo['adjusted_end_time'];

                if ($adjustedEndTime && $adjustedEndTime < \Carbon\Carbon::now()) {
                    $profit = calculateProfitOfShare($share);

                    $share->is_ready_to_sell = 1;
                    $share->matured_at = date_format(now(), "Y/m/d H:i:s");
                    $share->profit_share = $profit;
                    $share->total_share_count = $share->total_share_count + $profit;
                    
                    // Reset timer state
                    $share->timer_paused = 0;
                    $share->timer_paused_at = null;
                    
                    $share->save();

                    $profitHistoryData = [
                        'user_share_id' => $share->id,
                        'shares' => $profit,
                    ];

                    \App\Models\UserProfitHistory::create($profitHistoryData);

                    Log::info('Share matured using legacy timer system: ' . $share->id, [
                        'ticket_no' => $share->ticket_no,
                        'user_id' => $share->user_id,
                        'profit_added' => $profit,
                        'timer_type' => 'legacy_timer'
                    ]);
                }
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
        // Get all paired shares that might have expired payment deadlines - remove the whereNull('start_date') restriction
        $shares = \App\Models\UserShare::whereStatus('paired')->where('balance', 0)->get();
        $paymentFailureService = new \App\Services\PaymentFailureService();

        foreach ($shares as $key => $share) {
            // Use individual payment_deadline_minutes instead of hardcoded 3 hours
            $deadlineMinutes = $share->payment_deadline_minutes ?? 60; // fallback to 60 minutes if not set
            $timeoutTime = \Carbon\Carbon::parse($share->created_at)->addMinutes($deadlineMinutes);
            
            if ($timeoutTime < \Carbon\Carbon::now()) {

                $share->status = 'failed';
                $share->save();

                // Handle payment failure for the user
                try {
                    $result = $paymentFailureService->handlePaymentFailure(
                        $share->user_id, 
                        'Payment timeout - share failed after ' . $deadlineMinutes . ' minutes (no payment made)'
                    );
                    
                    if ($result['suspended']) {
                        \Illuminate\Support\Facades\Log::warning('User suspended due to payment failure: ' . $share->user->username);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error handling payment failure: ' . $e->getMessage());
                }

                foreach ($share->pairedShares as $pairedShare) {
                    $userShare = \App\Models\UserShare::findOrFail($pairedShare->paired_user_share_id);
                    $userShare->hold_quantity -= $pairedShare->share;
                    $userShare->total_share_count += $pairedShare->share;
                    $userShare->save();
                }

                \Illuminate\Support\Facades\Log::info('Payment failed share status updated successfully: ' . $share->id . ' (deadline: ' . $deadlineMinutes . ' minutes)');
            }
        }
    }
}

if (!function_exists('getSoldShareStatus')) {
    function getSoldShareStatus($share): string
    {
        // If share has been fully sold (no shares left and has sold some)
        if ($share->total_share_count == 0 && $share->hold_quantity == 0 && $share->sold_quantity > 0) {
            return 'Sold';
        }
        // If share is active and not ready to sell yet
        elseif ($share->start_date != '' && $share->is_ready_to_sell === 0) {
            return 'Active';
        }
        // If share is partially sold (some shares sold, some remaining)
        elseif ($share->sold_quantity > 0 && ($share->total_share_count > 0 || $share->hold_quantity > 0)) {
            return 'Partially Sold';
        }
        // If share has been paired but not fully processed
        elseif ((($share->share_will_get + $share->profit_share) > $share->total_share_count) && ($share->total_share_count !== 0 || $share->hold_quantity !== 0)) {
            return 'Paired';
        }
        // If share is completed but not sold
        elseif ($share->total_share_count === 0 && $share->hold_quantity === 0 && $share->sold_quantity === 0) {
            return 'Completed';
        }
        // Default status
        else {
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
                ->where('is_active', true)
                ->orderBy('open_time')->get();
    }
}
if (!function_exists('get_app_timezone')) {
    function get_app_timezone()
    {
        try {
            return get_gs_value('app_timezone') ?? config('app.timezone', 'UTC');
        } catch (Exception $e) {
            // Fallback to config timezone if database is not accessible
            return config('app.timezone', 'UTC');
        }
    }
}
if (!function_exists('is_market_open')) {
    function is_market_open()
    {
        $markets = get_markets();
        $appTimezone = get_app_timezone();
        $now = \Carbon\Carbon::now($appTimezone);
        
        foreach ($markets as $market) {
            $todayDate = $now->format('Y-m-d');
            $open = \Carbon\Carbon::parse($todayDate . ' ' . $market->open_time, $appTimezone);
            $close = \Carbon\Carbon::parse($todayDate . ' ' . $market->close_time, $appTimezone);
            
            if ($now->between($open, $close)) {
                return true;
            }
        }
        
        return false;
    }
}
if (!function_exists('get_next_market_open_time')) {
    function get_next_market_open_time()
    {
        $markets = get_markets();
        $appTimezone = get_app_timezone();
        $now = \Carbon\Carbon::now($appTimezone);
        $todayDate = $now->format('Y-m-d');
        
        if (count($markets) == 0) {
            return null;
        }
        
        // Check if there's a market that opens later today
        foreach ($markets as $market) {
            $open = \Carbon\Carbon::parse($todayDate . ' ' . $market->open_time, $appTimezone);
            
            if ($now->lt($open)) {
                return $open;
            }
        }
        
        // If no market opens today, return tomorrow's first market opening
        $firstMarket = $markets->first();
        return \Carbon\Carbon::parse($todayDate . ' ' . $firstMarket->open_time, $appTimezone)->addDay();
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
if (!function_exists('calculateTradeProgressPercentage')) {
    /**
     * Calculate the progress percentage for a trade using the centralized service
     * This replaces any inline progress calculation logic and ensures consistency
     * 
     * @param int $tradeId
     * @return float
     */
    function calculateTradeProgressPercentage(int $tradeId): float
    {
        try {
            $progressService = new \App\Services\ProgressCalculationService();
            $progressData = $progressService->computeTradeProgress($tradeId);
            
            return $progressData['progress_percentage'] ?? 0.0;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error calculating progress percentage for trade {$tradeId}: " . $e->getMessage());
            return 0.0;
        }
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
