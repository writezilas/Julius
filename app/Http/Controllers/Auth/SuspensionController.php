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
     * Check suspension status (AJAX endpoint)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Check if suspension has expired
        if ($user->checkSuspensionExpiry()) {
            $user->liftSuspension();
            return response()->json([
                'suspended' => false,
                'message' => 'Your suspension has been lifted!',
                'redirect' => route('dashboard')
            ]);
        }
        
        return response()->json([
            'suspended' => $user->isSuspended(),
            'remaining_seconds' => $user->getSuspensionRemainingSeconds(),
            'suspension_until' => $user->suspension_until ? $user->suspension_until->toISOString() : null
        ]);
    }
}
