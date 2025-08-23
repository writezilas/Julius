<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AllocateShareHistory;
use App\Models\Log;
use App\Models\Trade;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\UserShare;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone'    => ['required', 'unique:users'],
            'username' => ['required', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'avatar'   => ['image' ,'mimes:jpg,jpeg,png','max:1024'],
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
        if($data['refferal']) {
            $refferal = User::where('username', $data['refferal'])->first();
            if(!$refferal){
                $error = ValidationException::withMessages([
                   'refferal' => ['Refferal code not present in our database!'],
                ]);
                throw $error;
            }
        }
        $avatarName = 'assets/images/users/default.png';
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
            'refferal_code'       => $data['refferal'],
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

        return $user;
    }

}
