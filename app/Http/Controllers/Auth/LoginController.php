<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\AuthenticationService;
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
     * The authentication service instance.
     *
     * @var AuthenticationService
     */
    protected $authService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
        $this->middleware('guest')->except('logout');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // Get user by email or username
        $user = $this->authService->getUserByEmailOrUsername($request->login);
        
        if(@$user->status === 'blocked'){
            // Check if it's a temporary block that has expired
            if($user->block_until && $user->block_until->isPast()) {
                // Auto-unblock if block time has passed
                $user->update([
                    'status' => 'active',
                    'block_until' => null
                ]);
            } else {
                // Redirect to blocked account page (could be temporary or permanent)
                return redirect()->route('account.blocked', ['user' => $user->id]);
            }
        }

        if(@$user->status === 'suspended'){
            // Check if suspension has expired
            if($user->suspension_until && $user->suspension_until->isPast()) {
                // Auto-unsuspend if suspension time has passed
                $user->update([
                    'status' => 'active',
                    'suspension_until' => null,
                    'suspension_reason' => null
                ]);
            } else {
                // Show suspension page with countdown
                return redirect()->route('account.suspended', ['user' => $user->id]);
            }
        }
        
        // Attempt authentication with email or username
        if ($this->authService->attemptLoginWithEmailOrUsername(
            $request->login, 
            $request->password, 
            $request->filled('remember')
        )) {
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
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Use our custom validation service
        $this->authService->validateLoginCredentials($request->only('login', 'password'));
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'login';
    }

   /**
    * Show suspended account page with countdown
    */
   public function suspended(Request $request)
   {
       $user = User::findOrFail($request->user);
       
       // Double-check if user is actually suspended
       if($user->status !== 'suspended') {
           return redirect()->route('login');
       }
       
       // If suspension_until is set, check if it has expired
       if($user->suspension_until) {
           if($user->suspension_until->isPast()) {
               $user->update([
                   'status' => 'active',
                   'suspension_until' => null,
                   'suspension_reason' => null
               ]);
               return redirect()->route('login')->with('success', 'Your account suspension has expired. You can now log in.');
           }
       }
       // If suspension_until is null, it's an indefinite suspension - still show the page
       
       return view('auth.suspended', compact('user'));
   }

   /**
    * Show blocked account page
    */
   public function blocked(Request $request)
   {
       $user = User::findOrFail($request->user);
       
       // Double-check if user is actually blocked
       if($user->status !== 'blocked') {
           return redirect()->route('login');
       }
       
       return view('auth.blocked', compact('user'));
   }

}
