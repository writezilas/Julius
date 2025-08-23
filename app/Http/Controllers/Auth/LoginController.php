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
            // Redirect to blocked account page
            return redirect()->route('account.blocked', ['user' => $user->id]);
        }

        if(@$user->status === 'suspend'){
            // Check if suspension has expired
            if($user->suspension_until && $user->suspension_until->isPast()) {
                // Auto-unsuspend if suspension time has passed
                $user->update([
                    'status' => 'fine',
                    'suspension_until' => null
                ]);
            } else {
                // Show suspension page with countdown
                return redirect()->route('account.suspended', ['user' => $user->id]);
            }
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

   /**
    * Show suspended account page with countdown
    */
   public function suspended(Request $request)
   {
       $user = User::findOrFail($request->user);
       
       // Double-check if user is actually suspended
       if($user->status !== 'suspend' || !$user->suspension_until) {
           return redirect()->route('login');
       }
       
       // Check if suspension has expired
       if($user->suspension_until->isPast()) {
           $user->update([
               'status' => 'fine',
               'suspension_until' => null
           ]);
           return redirect()->route('login')->with('success', 'Your account suspension has expired. You can now log in.');
       }
       
       return view('auth.suspended', compact('user'));
   }

   /**
    * Show blocked account page
    */
   public function blocked(Request $request)
   {
       $user = User::findOrFail($request->user);
       
       // Double-check if user is actually blocked
       if($user->status !== 'block') {
           return redirect()->route('login');
       }
       
       return view('auth.blocked', compact('user'));
   }

}
