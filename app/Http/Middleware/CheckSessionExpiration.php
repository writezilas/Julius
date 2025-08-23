<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckSessionExpiration
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && ! $request->session()->has('lastActivityTime')) {
            // First time after login, set the last activity time
            $request->session()->put('lastActivityTime', now());
        }

        $lastActivityTime = $request->session()->get('lastActivityTime');

        // Check if the session has expired (e.g., after 1 hour)
        if ($lastActivityTime && now()->diffInMinutes($lastActivityTime) >= config('session.lifetime')) {
            // Session has expired, clear the session and redirect to the login page
            $request->session()->forget('lastActivityTime');
            Auth::logout();

            return redirect('/login'); // Replace with your login page URL
        }

        return $next($request);
    }
}
