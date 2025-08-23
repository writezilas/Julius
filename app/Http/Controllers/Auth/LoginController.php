<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
    }

   public function login(Request $request)
   {
        $this->validateLogin($request);

        $user = User::where('email', $request->email)->first();
        if(@$user->status === 'block'){
            toastr()->error("Your account is blocked, Kindly Contact support");
            throw ValidationException::withMessages([
                'email' => "Your account is blocked, Kindly Contact support",
            ]);
            
        }

        if(@$user->status === 'suspend'){
            
            $duration = Carbon::parse($user->suspended_until)->format('H:i:s');
            toastr()->error("Your account is suspended for $duration time, Kindly Contact support");
            throw ValidationException::withMessages([
                'email' => "Your account is banned for $duration time, Kindly Contact support",
            ]);
            
        }
        if ($this->attemptLogin($request)) {
            
            if($user){
                $log = new Log();
                $log->remarks = "Login Successfully.";
                $log->type    = "login";
                $log->value   = 0;
                $log->user_id = $user->id;
                $user->logs()->save($log);
            }
            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
   }


}
