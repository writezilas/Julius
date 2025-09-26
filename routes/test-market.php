<?php

use App\Models\User;
use App\Models\UserShare;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Test route for market availability - REMOVE THIS IN PRODUCTION
Route::get('/test-market-availability/{userId}', function($userId) {
    $user = User::find($userId);
    
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }
    
    // Login as the user
    Auth::login($user);
    
    // Test the helper function
    $availableShares = checkAvailableSharePerTrade(1); // Safaricom shares
    
    // Get detailed breakdown
    $allShares = UserShare::where('trade_id', 1)
        ->where('status', 'completed')
        ->where('is_ready_to_sell', 1)
        ->where('total_share_count', '>', 0)
        ->with('user')
        ->get();
        
    $userOwnShares = $allShares->where('user_id', $user->id)->sum('total_share_count');
    $otherShares = $allShares->where('user_id', '!=', $user->id)->sum('total_share_count');
    
    // Logout
    Auth::logout();
    
    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username
        ],
        'shares' => [
            'own_shares' => $userOwnShares,
            'other_users_shares' => $otherShares,
            'helper_function_result' => $availableShares,
            'expected_to_see' => $otherShares
        ],
        'verification' => [
            'is_correct' => $availableShares == $otherShares,
            'message' => $availableShares == $otherShares ? 'Working correctly' : 'Issue detected'
        ]
    ], 200, [], JSON_PRETTY_PRINT);
});