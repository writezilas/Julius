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
        if (auth()->check()) {
            $user = auth()->user();
            
            // Check if user is suspended
            if ($user->status === 'suspended') {
                // Check if suspension has expired (if suspension_until is set)
                if ($user->suspension_until && $user->suspension_until->isPast()) {
                    // Suspension expired, lift it
                    $user->update([
                        'status' => 'active',
                        'suspension_until' => null,
                        'suspension_reason' => null
                    ]);
                    
                    // Resume suspended trades if user has method
                    if (method_exists($user, 'liftSuspension')) {
                        $user->liftSuspension();
                    }
                    
                    toastr()->success('Your account suspension has been automatically lifted. Welcome back!');
                    return $next($request);
                }
                
                // Still suspended, force logout and redirect
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                if ($user->suspension_until) {
                    // Redirect to suspension page with countdown
                    return redirect()->route('account.suspended', ['user' => $user->id]);
                } else {
                    // Indefinite suspension or data inconsistency - show generic message
                    toastr()->error('Your account has been suspended. Please contact support for more information.');
                    return redirect()->route('login')->with([
                        'suspension_message' => 'Your account has been suspended. Please contact support for assistance.',
                        'suspended_user' => true
                    ]);
                }
            }
            
            // Check if user is blocked
            if ($user->status === 'blocked') {
                // Check if it's a temporary block that has expired
                if ($user->block_until && $user->block_until->isPast()) {
                    // Block expired, unblock user
                    $user->update([
                        'status' => 'active',
                        'block_until' => null
                    ]);
                    
                    toastr()->success('Your account block has been automatically lifted. Welcome back!');
                    return $next($request);
                }
                
                // Still blocked, force logout
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Redirect to blocked page (handles both temporary and permanent)
                return redirect()->route('account.blocked', ['user' => $user->id]);
            }
            
            // Legacy support for old status values
            if ($user->status === 'suspend') {
                // Update to new status value
                $user->update(['status' => 'suspended']);
                // Re-check with new status
                return $this->handle($request, $next);
            }
            
            if ($user->status === 'block') {
                // Update to new status value
                $user->update(['status' => 'blocked']);
                // Re-check with new status
                return $this->handle($request, $next);
            }
        }

        return $next($request);
    }
}
