<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IfUserBlocked
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
        if(auth()->check() && (auth()->user()->status == 'block' && auth()->user()->block_until)) {
            Auth::logout();
            toastr()->info('Your account are temporary blocked. Please try again after sometime');
            return redirect()->route('login');
        }elseif (auth()->check() && auth()->user()->status == 'block') {
            Auth::logout();
            toastr()->info('Your account has been blocked permanently. You can contact to via customer support to learn more about it.s');
            return redirect()->route('login');
        }

        return $next($request);
    }
}
