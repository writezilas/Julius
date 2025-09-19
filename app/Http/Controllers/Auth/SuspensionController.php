<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuspensionController extends Controller
{
    /**
     * Show the suspension page
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        
        // If user is not authenticated, redirect to login
        if (!$user) {
            return redirect()->route('login');
        }
        
        // If user is not suspended, redirect to dashboard
        if (!$user->isSuspended()) {
            return redirect()->route('dashboard')->with('success', 'Your account is active!');
        }
        
        // Get payment failure information
        $paymentFailure = $user->getCurrentPaymentFailure();
        
        return view('auth.suspended', [
            'user' => $user,
            'paymentFailure' => $paymentFailure,
            'suspensionMessage' => session('suspension_message', 'Your account is temporarily suspended.'),
            'suspensionUntil' => session('suspension_until', $user->suspension_until)
        ]);
    }
    
    /**
     * Check suspension/blocking status (AJAX endpoint)
     * Used by JavaScript monitor to detect status changes
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function checkStatus(Request $request)
    {
        if (!auth()->check()) {
            // If accessed directly in browser, redirect to login
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            } else {
                return redirect()->route('login')->with('error', 'Please log in to continue.');
            }
        }

        $user = auth()->user();
        
        // Check if suspension has expired and auto-lift it
        if ($user->status === 'suspended' && $user->suspension_until && $user->suspension_until->isPast()) {
            $user->update([
                'status' => 'active',
                'suspension_until' => null
            ]);
            
            // Resume suspended trades if user has method
            if (method_exists($user, 'liftSuspension')) {
                $user->liftSuspension();
            }
            
            // Refresh the user model
            $user->refresh();
        }
        
        // Check if temporary block has expired and auto-lift it
        if ($user->status === 'blocked' && $user->block_until && $user->block_until->isPast()) {
            $user->update([
                'status' => 'active',
                'block_until' => null
            ]);
            
            // Refresh the user model
            $user->refresh();
        }

        $response = [
            'status' => $user->status,
            'user_id' => $user->id,
            'username' => $user->username,
            'timestamp' => now()->toISOString()
        ];

        // Add suspension details if suspended
        if ($user->status === 'suspended') {
            $response['suspension_until'] = $user->suspension_until ? $user->suspension_until->toISOString() : null;
            $response['is_indefinite'] = !$user->suspension_until;
            
            if ($user->suspension_until) {
                $response['remaining_seconds'] = $user->suspension_until->diffInSeconds(now());
            }
            
            // Legacy compatibility
            $response['suspended'] = true;
        } else {
            $response['suspended'] = false;
        }

        // Add block details if blocked
        if ($user->status === 'blocked') {
            $response['block_until'] = $user->block_until ? $user->block_until->toISOString() : null;
            $response['is_permanent'] = !$user->block_until;
            
            if ($user->block_until) {
                $response['remaining_seconds'] = $user->block_until->diffInSeconds(now());
            }
            
            // Legacy compatibility
            $response['blocked'] = true;
        } else {
            $response['blocked'] = false;
        }

        // If this is a direct browser request (not AJAX) and user is suspended/blocked,
        // redirect them to the appropriate page instead of returning JSON
        if (!$request->expectsJson() && !$request->ajax()) {
            if ($user->status === 'suspended') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                if ($user->suspension_until) {
                    return redirect()->route('account.suspended', ['user' => $user->id]);
                } else {
                    return redirect()->route('login')->with('error', 'Your account has been suspended.');
                }
            }
            
            if ($user->status === 'blocked') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('account.blocked', ['user' => $user->id]);
            }
            
            // If user is active and accessing this directly, redirect to dashboard
            if ($user->status === 'active') {
                return redirect()->route('root')->with('success', 'Your account is active.');
            }
        }
        
        return response()->json($response);
    }
}
