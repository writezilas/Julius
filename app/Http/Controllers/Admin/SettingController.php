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
use App\Helpers\SettingHelper;
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

        // Try to load email settings from database first, fallback to environment variables
        $mailSetting = GeneralSetting::where('key', 'mail_setting')->first();
        $dbSettings = $mailSetting ? json_decode($mailSetting->value, true) : [];
        
        // Prepare email configuration with database values taking priority over env values
        $emails = [
            "MAIL_MAILER"       => $dbSettings['mail_mailer'] ?? env("MAIL_MAILER") ?? 'smtp',
            "MAIL_HOST"         => $dbSettings['mail_host'] ?? env('MAIL_HOST') ?? '',
            "MAIL_PORT"         => $dbSettings['mail_port'] ?? env('MAIL_PORT') ?? '',
            "MAIL_USERNAME"     => $dbSettings['mail_username'] ?? env('MAIL_USERNAME') ?? '',
            "MAIL_PASSWORD"     => $dbSettings['mail_password'] ?? env('MAIL_PASSWORD') ?? '',
            "MAIL_ENCRYPTION"   => $dbSettings['mail_encryption'] ?? env('MAIL_ENCRYPTION') ?? '',
            "MAIL_FROM_ADDRESS" => $dbSettings['mail_from_address'] ?? env('MAIL_FROM_ADDRESS') ?? '',
            "MAIL_FROM_NAME"    => $dbSettings['mail_from_name'] ?? env('MAIL_FROM_NAME') ?? 'Autobidder',
        ];
        
        // Log the data source for debugging
        \Log::info('Email settings loaded for display', [
            'source' => $mailSetting ? 'database' : 'environment',
            'has_db_settings' => !empty($dbSettings),
            'loaded_host' => $emails['MAIL_HOST'],
            'loaded_port' => $emails['MAIL_PORT'],
            'user' => auth()->user()->name ?? 'Unknown'
        ]);
        
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
        // Debug logging to track form submissions
        \Log::info('Email settings form submitted', [
            'user' => auth()->user()->name ?? 'Unknown',
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'form_data' => [
                'mail_host' => $request->mail_host,
                'mail_username' => $request->mail_username, 
                'mail_from_name' => $request->mail_from_name,
                'port' => $request->port,
                'has_password' => !empty($request->password),
                'encryption' => $request->encryption
            ]
        ]);
        
        // Validate the email configuration inputs
        // Check if there are existing settings to determine password requirement
        $existingMailSetting = GeneralSetting::where('key', 'mail_setting')->first();
        $hasExistingPassword = $existingMailSetting && 
                              !empty(json_decode($existingMailSetting->value, true)['mail_password'] ?? '');
        
        $this->validate($request, [
            'mail_host' => 'required|string|max:255',
            'mail_username' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
            'port' => 'required|numeric|min:1|max:65535',
            'password' => $hasExistingPassword ? 'nullable|string' : 'required|string',
            'encryption' => 'required|string|in:tls,ssl',
        ], [
            'mail_host.required' => 'Server address is required',
            'mail_username.required' => 'Username is required',
            'mail_username.email' => 'Username must be a valid email address',
            'mail_from_name.required' => 'Mail from name is required',
            'port.required' => 'IMAP port is required',
            'port.numeric' => 'IMAP port must be a number',
            'port.min' => 'IMAP port must be at least 1',
            'port.max' => 'IMAP port must not exceed 65535',
            'password.required' => 'Password is required',
            'encryption.required' => 'Encryption is required',
            'encryption.in' => 'Encryption must be either SSL/TLS or STARTTLS',
        ]);

        try {
            // Check if .env file exists and is writable
            $envPath = base_path('.env');
            if (!file_exists($envPath)) {
                throw new \Exception('.env file not found');
            }
            if (!is_writable($envPath)) {
                throw new \Exception('.env file is not writable');
            }
            
            $fileContents = file_get_contents($envPath);
            if ($fileContents === false) {
                throw new \Exception('Could not read .env file');
            }
            
            // Ensure MAIL_MAILER is set to smtp
            if (strpos($fileContents, 'MAIL_MAILER=') !== false) {
                $fileContents = preg_replace('/^MAIL_MAILER=.*/m', 'MAIL_MAILER=smtp', $fileContents);
            } else {
                // Add MAIL_MAILER if it doesn't exist
                $fileContents = "MAIL_MAILER=smtp\n" . $fileContents;
            }
            
            // Helper function to properly quote values that need it
            $quoteValue = function($value) {
                $value = trim($value);
                // Quote if value contains spaces, special characters, or is empty
                if (empty($value) || preg_match('/[\s\$\#\&\*\(\)\|\[\{\}\`\"\'\;]/', $value)) {
                    // Escape special characters and wrap in quotes
                    return '"' . str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value) . '"';
                }
                return $value;
            };
            
            // Safe regex replacement function that handles special characters
            $safeReplace = function($fileContents, $pattern, $replacement) {
                // Use preg_quote to escape special regex characters in replacement
                $escapedReplacement = str_replace('$', '\$', $replacement);
                return preg_replace($pattern, $escapedReplacement, $fileContents);
            };
            
            // Update MAIL_HOST
            if($request->mail_host) {
                $host = $quoteValue($request->mail_host);
                if (strpos($fileContents, 'MAIL_HOST=') !== false) {
                    $fileContents = preg_replace('/^MAIL_HOST=.*/m', 'MAIL_HOST=' . $host, $fileContents);
                } else {
                    $fileContents .= "\nMAIL_HOST=" . $host;
                }
            }
            
            // Update MAIL_PORT
            if($request->port) {
                $port = intval($request->port);
                if (strpos($fileContents, 'MAIL_PORT=') !== false) {
                    $fileContents = preg_replace('/^MAIL_PORT=.*/m', 'MAIL_PORT=' . $port, $fileContents);
                } else {
                    $fileContents .= "\nMAIL_PORT=" . $port;
                }
            }
            
            // Update MAIL_USERNAME (from mail_username field)
            if($request->mail_username) {
                $username = $quoteValue($request->mail_username);
                if (strpos($fileContents, 'MAIL_USERNAME=') !== false) {
                    $fileContents = preg_replace('/^MAIL_USERNAME=.*/m', 'MAIL_USERNAME=' . $username, $fileContents);
                } else {
                    $fileContents .= "\nMAIL_USERNAME=" . $username;
                }
            }
            
            // Update MAIL_PASSWORD (only if provided, otherwise keep existing)
            if(!empty($request->password)) {
                $password = $quoteValue($request->password);
                if (strpos($fileContents, 'MAIL_PASSWORD=') !== false) {
                    $fileContents = $safeReplace($fileContents, '/^MAIL_PASSWORD=.*/m', 'MAIL_PASSWORD=' . $password);
                } else {
                    $fileContents .= "\nMAIL_PASSWORD=" . $password;
                }
            } else {
                // If password is blank and no existing password in env, we need to get it from database
                if ($hasExistingPassword && strpos($fileContents, 'MAIL_PASSWORD=') === false) {
                    $existingData = json_decode($existingMailSetting->value, true);
                    $existingPassword = $quoteValue($existingData['mail_password'] ?? '');
                    if (!empty($existingPassword)) {
                        $fileContents .= "\nMAIL_PASSWORD=" . $existingPassword;
                    }
                }
            }
            
            // Update MAIL_ENCRYPTION
            if($request->encryption) {
                $encryption = strtolower(trim($request->encryption));
                if (strpos($fileContents, 'MAIL_ENCRYPTION=') !== false) {
                    $fileContents = preg_replace('/^MAIL_ENCRYPTION=.*/m', 'MAIL_ENCRYPTION=' . $encryption, $fileContents);
                } else {
                    $fileContents .= "\nMAIL_ENCRYPTION=" . $encryption;
                }
            }
            
            // Update MAIL_FROM_ADDRESS (use username as from address)
            if($request->mail_username) {
                $fromAddress = $quoteValue($request->mail_username);
                if (strpos($fileContents, 'MAIL_FROM_ADDRESS=') !== false) {
                    $fileContents = preg_replace('/^MAIL_FROM_ADDRESS=.*/m', 'MAIL_FROM_ADDRESS=' . $fromAddress, $fileContents);
                } else {
                    $fileContents .= "\nMAIL_FROM_ADDRESS=" . $fromAddress;
                }
            }
            
            // Update MAIL_FROM_NAME (from mail_from_name field)
            if($request->mail_from_name) {
                $fromName = $quoteValue($request->mail_from_name);
                if (strpos($fileContents, 'MAIL_FROM_NAME=') !== false) {
                    $fileContents = preg_replace('/^MAIL_FROM_NAME=.*/m', 'MAIL_FROM_NAME=' . $fromName, $fileContents);
                } else {
                    $fileContents .= "\nMAIL_FROM_NAME=" . $fromName;
                }
            }
            
            // Write the updated contents back to .env file
            $result = file_put_contents($envPath, $fileContents);
            if ($result === false) {
                throw new \Exception('Failed to write to .env file');
            }
            
            // Save email settings to database for easy retrieval
            // Preserve existing password if not provided
            $passwordToSave = !empty($request->password) ? trim($request->password) : 
                             ($hasExistingPassword ? json_decode($existingMailSetting->value, true)['mail_password'] : '');
            
            $mailSettingsData = [
                'mail_mailer' => 'smtp',
                'mail_host' => trim($request->mail_host),
                'mail_port' => intval($request->port),
                'mail_username' => trim($request->mail_username),
                'mail_password' => $passwordToSave,
                'mail_encryption' => strtolower(trim($request->encryption)),
                'mail_from_address' => trim($request->mail_username),
                'mail_from_name' => trim($request->mail_from_name),
                'updated_at' => now()->toDateTimeString(),
                'updated_by' => auth()->user()->name ?? 'Unknown'
            ];
            
            GeneralSetting::updateOrCreate(
                ['key' => 'mail_setting'],
                ['value' => json_encode($mailSettingsData)]
            );
            
            // Clear settings cache if using SettingHelper
            if (class_exists('\App\Helpers\SettingHelper')) {
                \App\Helpers\SettingHelper::clearCache();
            }
            
            // Clear and recache configuration to ensure new settings are loaded
            Artisan::call('config:clear');
            Artisan::call('config:cache');
            
            \Log::info('Mail settings updated successfully', [
                'mail_host' => $request->mail_host,
                'mail_port' => $request->port,
                'mail_username' => $request->mail_username,
                'mail_from_address' => $request->mail_username,
                'mail_from_name' => $request->mail_from_name,
                'mail_encryption' => $request->encryption,
                'updated_by' => auth()->user()->name ?? 'Unknown',
                'saved_to' => 'both database and environment file'
            ]);
            
            return redirect()->back()->with('success', 'Mail settings updated successfully! Your email configuration has been saved and is now active.');
            
        } catch (\Exception $e) {
            \Log::error('Failed to update mail settings', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user' => auth()->user()->name ?? 'Unknown'
            ]);
            
            $errorMessage = 'Failed to update mail settings: ' . $e->getMessage();
            if (strpos($e->getMessage(), 'not writable') !== false) {
                $errorMessage .= ' Please check that your web server has write permissions to the .env file.';
            }
            
            return redirect()->back()->withErrors(['error' => $errorMessage]);
        }
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
            'settings.admin_email' => 'required|email|max:255',
            // 'settings.open_market' => 'required|date_format:H:i',
            // 'settings.close_market' => 'required|date_format:H:i|after:settings.open_market',
        ], [
            'settings.reffaral_bonus.required' => 'Refferal bonus is required',
            'settings.admin_email.required' => 'Admin email is required',
            'settings.admin_email.email' => 'Admin email must be a valid email address',
            'settings.admin_email.max' => 'Admin email must not exceed 255 characters',
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
        
        // Clear settings cache after updating
        SettingHelper::clearCache();

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
