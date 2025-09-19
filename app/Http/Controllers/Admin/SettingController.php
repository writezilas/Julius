<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AllocateShareHistory;
use App\Models\GeneralSetting;
use App\Models\Log;
use App\Models\Trade;
use App\Models\UserProfitHistory;
use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function createSmsSetting() {
        $pageTitle = 'Sms setting';
        $smsSetting = GeneralSetting::where('key', 'sms_setting')->first();
        $smsSetting = $smsSetting ? json_decode($smsSetting->value, true) : null;
        
        return view('admin-panel.settings.sms', compact('pageTitle', 'smsSetting'));
    }
    public function createMailSetting() {
        $pageTitle = 'Email setting';

        $emails = [
            "MAIL_MAILER"       => env("MAIL_MAILER"),
            "MAIL_HOST"         => env('MAIL_HOST'),
            "MAIL_PORT"         => env('MAIL_PORT'),
            "MAIL_USERNAME"     => env('MAIL_USERNAME'),
            "MAIL_PASSWORD"     => env('MAIL_PASSWORD'),
            "MAIL_ENCRYPTION"   => env('MAIL_ENCRYPTION'),
            "MAIL_FROM_ADDRESS" => env('MAIL_FROM_ADDRESS'),
        ];        
        return view('admin-panel.settings.email', compact('pageTitle', 'emails'));
    }

    public function storeSmsSetting(Request $request){
        
        $this->validate($request,[
            'zettatel_user_id'        => 'required',
            'zettatel_password'       => 'required',
            'zettatel_senderid'       => 'required',
            'zettatel_msg_type'       => 'required',
            'zettatel_duplicatecheck' => 'required',
            'zettatel_sendmethod'     => 'required',
        ]);

        GeneralSetting::updateOrCreate(
            ['key' => 'sms_setting'],
            ['value' => json_encode($request->except('_token'))]
        );

        return redirect()->to(route('admin.setting.sms.create'))->with('success', 'Sms setting updated successfully');
    }

    public function storeMailSetting(Request $request) {
            
        
        putenv('MAIL_USERNAME='.strtolower($request->mail_name));
        $fileContents = file_get_contents(base_path('.env'));

        if($request->mail_name)
            $fileContents = preg_replace('/^MAIL_USERNAME=.*/m', 'MAIL_USERNAME='.strtolower($request->mail_name), $fileContents);

        if($request->mail_host)
            $fileContents = preg_replace('/^MAIL_HOST=.*/m', 'MAIL_HOST='.strtolower($request->mail_host), $fileContents);

        if($request->mail_host)
            $fileContents = preg_replace('/^MAIL_PORT=.*/m', 'MAIL_PORT='.strtolower($request->mail_host), $fileContents);

        if($request->mail_host)
            $fileContents = preg_replace('/^MAIL_HOST=.*/m', 'MAIL_HOST='.strtolower($request->mail_host), $fileContents);
            
        if($request->mail_host)
            $fileContents = preg_replace('/^MAIL_HOST=.*/m', 'MAIL_HOST='.strtolower($request->mail_host), $fileContents);

        file_put_contents(base_path('.env'), $fileContents);
        Artisan::call('config:clear');
        

        return redirect()->back()->with('success', 'Mail setting updated successfully');
    }
    
    public function generalSetting(){
        $pageTitle = 'General setting';
        $gs = GeneralSetting::get();
        
        foreach($gs as $key => $g){
            $gs[$g->key] = $g->value;
            unset($gs[$key]);
        }
        
        return view('admin-panel.settings.general', compact('pageTitle', 'gs'));
    }
    
    public function generalSettingStore(Request $request){
         
        $this->validate($request, [
            'settings' => 'required|array',
            'settings.reffaral_bonus' => 'integer',
            // 'settings.open_market' => 'required|date_format:H:i',
            // 'settings.close_market' => 'required|date_format:H:i|after:settings.open_market',
        ], [
            'settings.reffaral_bonus.required' => 'Refferal bonus is required',
            'settings.open_market.required' => 'Open market time is required',
            'settings.open_market.date_format' => 'Open market time is not valid',
            'settings.close_market.required' => 'Close market time is required',
            'settings.close_market.date_format' => 'Close market time is not valid',
            'settings.close_market.after' => 'Close market time must be after open market time',
        ]);
        foreach($request->settings as $key => $set){
            // update of create new
            GeneralSetting::updateOrCreate(
                ['key' => $key],
                [
                    'key' => $key,
                    'value' => $set
                ]
            );
        }

        return redirect()->back()->with('success', 'General setting updated successfully');
    }

    public function trancateTables(){
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        AllocateShareHistory::truncate();
        DB::table('notifications')->truncate();
        UserProfitHistory::truncate();
        UserShare::truncate();
        UserSharePair::truncate();
        UserSharePayment::truncate();
        Log::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $users = DB::table('users')->where('role_id', 2)->take(5)->inRandomOrder()->get();
        foreach($users as $user) {
            $tradeID = Trade::inRandomOrder()->first()->id;
            $sharesWillGet = rand(1, 10783);
            if($user->id)
                $this->saveAllocateShare($user->id, $tradeID, $sharesWillGet);
        }

        return ('All tables truncated successfully');
    }

    public function saveAllocateShare($userID, $tradeID, $sharesWillGet)
    {

        $trade = Trade::where('id', $tradeID)->first();

        $data = [
            'trade_id' => $tradeID,
            'amount'   => $trade->price * $sharesWillGet,
            'period'   => 1,
        ];

        DB::beginTransaction();

        $ticketNo = 'AB-'.time().rand(3,8).$userID;

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
        
        $data['is_ready_to_sell']  = 1;
        
        $data['get_from']          = 'allocated-by-admin';

        $createdShare = UserShare::create($data);
        $allocateShareHistoryData = [
            'user_share_id' => $createdShare->id,
            'shares'        => $sharesWillGet,
            'created_by'    => 1,
        ];
        AllocateShareHistory::create($allocateShareHistoryData);
        DB::commit();
        return 'ds';
    }
}
