<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSuspension
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Check if suspension has expired and auto-lift it
            if ($user->checkSuspensionExpiry()) {
                $user->liftSuspension();
                toastr()->success('Your account suspension has been lifted. Welcome back!');
                return $next($request);
            }
            
            // If user is still suspended, immediately logout and redirect
            if ($user->isSuspended()) {
                // Allow access only to logout and login routes
                $allowedRoutes = ['logout', 'login'];
                
                if (!in_array($request->route()->getName(), $allowedRoutes)) {
                    // Force logout the user
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    // Redirect to login with suspension message
                    return redirect()->route('login')->with([
                        'suspension_message' => 'Your account has been suspended due to 3 consecutive payment failures. You have been automatically logged out.',
                        'suspension_until' => $user->suspension_until,
                        'auto_logged_out' => true
                    ]);
                }
            }
        }

        return $next($request);
    }
}
