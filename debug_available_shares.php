<?php

/**
 * Debug Script: Available Shares Count Investigation
 * 
 * This script investigates why the available shares count might be incorrect
 * and helps understand what shares should be included in the total count.
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\UserShare;
use App\Models\Trade;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Available Shares Debug Investigation ===\n\n";

try {
    // Get Safaricom trade (assuming it's ID 1)
    $trade = Trade::find(1);
    if (!$trade) {
        echo "❌ Trade ID 1 (Safaricom) not found\n";
        exit(1);
    }
    
    echo "🔍 Investigating available shares for: {$trade->name} (ID: {$trade->id})\n\n";
    
    // Step 1: Get all shares for this trade that meet basic criteria
    echo "Step 1: All shares for {$trade->name} with basic criteria:\n";
    echo "- Status: completed\n";
    echo "- Ready to sell: 1\n";
    echo "- Total share count > 0\n\n";
    
    $allShares = UserShare::where('trade_id', $trade->id)
        ->where('status', 'completed')
        ->where('is_ready_to_sell', 1)
        ->where('total_share_count', '>', 0)
        ->with('user')
        ->get();
    
    echo "Found {$allShares->count()} total shares:\n";
    echo "┌─────┬──────────────┬─────────────┬────────────┬─────────────────┬──────────────┬───────────────┐\n";
    echo "│ ID  │ Ticket       │ User        │ Get From   │ Total Count     │ User Status  │ Available For │\n";
    echo "├─────┼──────────────┼─────────────┼────────────┼─────────────────┼──────────────┼───────────────┤\n";
    
    $totalAvailableCount = 0;
    
    foreach ($allShares as $share) {
        $userName = $share->user ? $share->user->username : 'N/A';
        $userStatus = $share->user ? $share->user->status : 'N/A';
        $isUserActive = $share->user && in_array($share->user->status, ['active', 'pending', 'fine']);
        $availableForTrading = $isUserActive ? 'YES' : 'NO';
        
        if ($isUserActive) {
            $totalAvailableCount += $share->total_share_count;
        }
        
        printf("│ %-3d │ %-12s │ %-11s │ %-10s │ %-15s │ %-12s │ %-13s │\n", 
            $share->id,
            substr($share->ticket_no, 0, 12),
            substr($userName, 0, 11),
            substr($share->get_from, 0, 10),
            $share->total_share_count,
            $userStatus,
            $availableForTrading
        );
    }
    
    echo "└─────┴──────────────┴─────────────┴────────────┴─────────────────┴──────────────┴───────────────┘\n";
    echo "Total Available Count (from active users): {$totalAvailableCount}\n\n";
    
    // Step 2: Test the checkAvailableSharePerTrade function with different users
    echo "Step 2: Testing checkAvailableSharePerTrade function:\n\n";
    
    // Test as guest (no authentication)
    auth()->logout();
    $guestResult = checkAvailableSharePerTrade($trade->id);
    echo "As Guest: {$guestResult} shares available\n";
    
    // Test as different users
    $users = User::take(3)->get();
    foreach ($users as $user) {
        auth()->login($user);
        $userResult = checkAvailableSharePerTrade($trade->id);
        echo "As {$user->username} (ID: {$user->id}): {$userResult} shares available\n";
        auth()->logout();
    }
    
    echo "\n";
    
    // Step 3: Breakdown by share type
    echo "Step 3: Breakdown by share type:\n\n";
    
    $breakdown = UserShare::where('trade_id', $trade->id)
        ->where('status', 'completed')
        ->where('is_ready_to_sell', 1)
        ->where('total_share_count', '>', 0)
        ->whereHas('user', function ($query) {
            $query->whereIn('status', ['active', 'pending', 'fine']);
        })
        ->selectRaw('get_from, COUNT(*) as count, SUM(total_share_count) as total_shares')
        ->groupBy('get_from')
        ->get();
    
    echo "Share Type Breakdown:\n";
    echo "┌────────────────────┬───────┬──────────────┐\n";
    echo "│ Type               │ Count │ Total Shares │\n";
    echo "├────────────────────┼───────┼──────────────┤\n";
    
    foreach ($breakdown as $item) {
        printf("│ %-18s │ %-5d │ %-12d │\n", 
            $item->get_from,
            $item->count,
            $item->total_shares
        );
    }
    
    echo "└────────────────────┴───────┴──────────────┘\n\n";
    
    // Step 4: Check for specific referral bonus shares
    echo "Step 4: Referral Bonus Shares Analysis:\n\n";
    
    $referralBonuses = UserShare::where('trade_id', $trade->id)
        ->where('get_from', 'refferal-bonus')
        ->with(['user', 'invoice.reff_user'])
        ->get();
    
    echo "Found {$referralBonuses->count()} referral bonus shares:\n";
    
    if ($referralBonuses->count() > 0) {
        echo "┌─────┬──────────────┬─────────────┬────────────┬─────────────────┬──────────────┬────────────────┐\n";
        echo "│ ID  │ Ticket       │ Owner       │ Status     │ Total Count     │ Ready to Sell│ Matured At     │\n";
        echo "├─────┼──────────────┼─────────────┼────────────┼─────────────────┼──────────────┼────────────────┤\n";
        
        foreach ($referralBonuses as $bonus) {
            $ownerName = $bonus->user ? $bonus->user->username : 'N/A';
            $maturedAt = $bonus->matured_at ? $bonus->matured_at->format('M d, H:i') : 'Not matured';
            
            printf("│ %-3d │ %-12s │ %-11s │ %-10s │ %-15s │ %-12s │ %-14s │\n", 
                $bonus->id,
                substr($bonus->ticket_no, 0, 12),
                substr($ownerName, 0, 11),
                $bonus->status,
                $bonus->total_share_count,
                $bonus->is_ready_to_sell ? 'Yes' : 'No',
                substr($maturedAt, 0, 14)
            );
        }
        
        echo "└─────┴──────────────┴─────────────┴────────────┴─────────────────┴──────────────┴────────────────┘\n";
    } else {
        echo "No referral bonus shares found.\n";
    }
    
    echo "\n";
    
    // Step 5: Summary and recommendations
    echo "Step 5: Summary and Recommendations:\n\n";
    
    echo "📊 Current State:\n";
    echo "- Total shares meeting criteria: {$allShares->count()}\n";
    echo "- Total share count available: {$totalAvailableCount}\n";
    echo "- Function result (as guest): {$guestResult}\n";
    
    if ($guestResult != $totalAvailableCount) {
        echo "\n⚠️  DISCREPANCY DETECTED!\n";
        echo "Expected: {$totalAvailableCount}, Got: {$guestResult}\n";
        echo "This suggests there might be an issue with the user status filtering or other conditions.\n";
    } else {
        echo "\n✅ Counts match! The function is working correctly.\n";
    }
    
    // Additional debugging for referral bonus issues
    $referralBonusTotal = UserShare::where('trade_id', $trade->id)
        ->where('get_from', 'refferal-bonus')
        ->where('status', 'completed')
        ->where('is_ready_to_sell', 1)
        ->where('total_share_count', '>', 0)
        ->whereHas('user', function ($query) {
            $query->whereIn('status', ['active', 'pending', 'fine']);
        })
        ->sum('total_share_count');
    
    echo "\n🎁 Referral Bonus Analysis:\n";
    echo "- Referral bonus shares available: {$referralBonusTotal}\n";
    
    if ($referralBonusTotal > 0) {
        echo "- Referral bonuses are being counted correctly\n";
    } else {
        echo "- No referral bonuses available (either not created, not matured, or owners inactive)\n";
    }
    
    echo "\n=== Debug Investigation Complete ===\n";
    
} catch (Exception $e) {
    echo "❌ Debug failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}