<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\AllocateShareHistory;
use App\Models\Log;
use App\Models\Trade;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\UserShare;
use App\Mail\NewUserRegistrationMail;
use App\Helpers\SettingHelper;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone'               => ['required', 'unique:users'],
            'username'            => ['required', 'unique:users'],
            'password'            => ['required', 'string', 'min:8', 'confirmed'],
            'avatar'              => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'refferal'            => ['nullable', 'string', 'exists:users,username'],
            'business_account_id' => ['required', 'integer', 'in:1,2'],
            'mpesa_no'            => ['required', 'string', 'max:20'],
            'mpesa_name'          => ['required', 'string', 'max:255'],
            'mpesa_till_no'       => ['nullable', 'string', 'max:20'],
            'mpesa_till_name'     => ['nullable', 'string', 'max:255'],
            'trade_id'            => ['required', 'integer', 'exists:trades,id'],
            'terms'               => ['required', 'accepted'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // Handle referral validation (already handled in validator, but keep for additional checks)
        if(isset($data['refferal']) && !empty($data['refferal'])) {
            // Prevent self-referral
            if($data['refferal'] === $data['username']) {
                $error = ValidationException::withMessages([
                   'refferal' => ['You cannot refer yourself!'],
                ]);
                throw $error;
            }
            
            $refferal = User::where('username', $data['refferal'])->first();
            if(!$refferal){
                $error = ValidationException::withMessages([
                   'refferal' => ['Referral code not present in our database!'],
                ]);
                throw $error;
            }
        }
        $avatarName = 'assets/images/users/default.jpg';
        if (request()->has('avatar')) {
            $avatar = request()->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = public_path('/images/');
            $avatar->move($avatarPath, $avatarName);
            $avatarName = 'images/' . $avatarName;
            
        }

        $business_profile = [
            'mpesa_no'        => $data['mpesa_no'],
            'mpesa_name'      => $data['mpesa_name'],
            'mpesa_till_no'   => $data['mpesa_till_no'],
            'mpesa_till_name' => $data['mpesa_till_name'],
        ];


        $user = User::create([
            'name'                => $data['name'],
            'email'               => $data['email'],
            'phone'               => $data['phone'],
            'username'            => $data['username'],
            'refferal_code'       => $data['refferal'] ?? null,
            'password'            => Hash::make($data['password']),
            'avatar'              => $avatarName,
            'business_profile'    => json_encode($business_profile, true),
            'business_account_id' => $data['business_account_id'],
            'trade_id'            => $data['trade_id'],
        ]);

        $log = new Log();
        $log->remarks = "Signup Successfully.";
        $log->type    = "signup";
        $log->value   = 0;
        $log->user_id = $user->id;
        $user->logs()->save($log);

        // Set referral amount and capture bonus at registration time
        if(isset($data['refferal']) && !empty($data['refferal'])) {
            try {
                $referrer = User::where('username', $data['refferal'])->first();
                if($referrer) {
                    // Get the current referral bonus amount and store it for this user
                    $bonusAmount = get_gs_value('reffaral_bonus') ?? 100;
                    
                    // Store both the potential earnings and the bonus amount at registration
                    $user->ref_amount = $bonusAmount;
                    $user->referral_bonus_at_registration = $bonusAmount; // Track bonus at time of registration
                    $user->save();
                    
                    // Log the referral setup with bonus tracking
                    $refLog = new Log();
                    $refLog->remarks = "Referral setup: Potential earning of KSH {$bonusAmount} for being referred by {$referrer->username}. Bonus amount locked at registration time: KSH {$bonusAmount}. Status: Pending until bonus shares are sold.";
                    $refLog->type = "referral_setup";
                    $refLog->value = $bonusAmount;
                    $refLog->user_id = $user->id;
                    $user->logs()->save($refLog);
                    
                    \Log::info("Referral setup: User {$user->username} has potential earning of KSH {$bonusAmount} for referral by {$referrer->username}. Bonus amount locked at registration: KSH {$bonusAmount}. Waiting for bonus shares to be created and sold.");
                }
            } catch (\Exception $e) {
                \Log::error("Failed to set up referral for user {$user->username}: " . $e->getMessage());
                // Continue with registration even if referral setup fails
            }
        }

        // Create admin notification for new user signup
        try {
            AdminNotification::newUserSignup($user);
        } catch (\Exception $e) {
            \Log::error("Failed to create admin notification for new user {$user->username}: " . $e->getMessage());
            // Don't fail registration if notification creation fails
        }
        
        // Send admin email notification for new user registration
        try {
            $adminEmail = SettingHelper::get('admin_email');
            if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($adminEmail)->send(new NewUserRegistrationMail($user));
                \Log::info("Admin email sent successfully for new user registration: {$user->username} to {$adminEmail}");
            } else {
                \Log::warning("Admin email not configured or invalid - skipping email notification for new user: {$user->username}");
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send admin email for new user registration {$user->username}: " . $e->getMessage());
            // Don't fail registration if email sending fails
        }

        return $user;
    }

}
