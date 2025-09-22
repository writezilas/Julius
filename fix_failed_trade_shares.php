<?php
/**
 * Fix Failed Trade Shares Script
 * 
 * This script finds failed trades that still have held shares and releases them
 * back to available inventory where they belong.
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Models\UserSharePair;
use Illuminate\Support\Facades\Log;

echo "🔧 FIX FAILED TRADE SHARES\n";
echo str_repeat("=", 50) . "\n\n";

// Find all failed shares that might still have pairings with held shares
echo "📋 FINDING FAILED TRADES WITH HELD SHARES:\n";
echo str_repeat("-", 30) . "\n";

$failedShares = UserShare::where('status', 'failed')
    ->whereHas('pairedShares')
    ->with(['pairedShares', 'user'])
    ->get();

echo "Found {$failedShares->count()} failed shares with pairings:\n\n";

if ($failedShares->isEmpty()) {
    echo "✅ No failed shares with pairings found.\n";
    exit(0);
}

$totalFixed = 0;
$totalSharesReleased = 0;

foreach ($failedShares as $failedShare) {
    echo "🔍 Processing Failed Share: {$failedShare->ticket_no}\n";
    echo "   User: {$failedShare->user->name} (ID: {$failedShare->user_id})\n";
    echo "   Status: {$failedShare->status}\n";
    echo "   Created: {$failedShare->created_at}\n";
    
    $pairings = $failedShare->pairedShares;
    echo "   Pairings: {$pairings->count()}\n";
    
    $needsFixing = false;
    $sharesToRelease = [];
    
    foreach ($pairings as $pairing) {
        $sellerShare = UserShare::find($pairing->paired_user_share_id);
        
        if (!$sellerShare) {
            echo "   ⚠️ Warning: Seller share {$pairing->paired_user_share_id} not found\n";
            continue;
        }
        
        echo "   - Paired with: {$sellerShare->ticket_no} (ID: {$sellerShare->id})\n";
        echo "     Shares in Pairing: {$pairing->share}\n";
        echo "     Seller Hold Quantity: {$sellerShare->hold_quantity}\n";
        echo "     Seller Total Shares: {$sellerShare->total_share_count}\n";
        
        // Check if this seller still has shares held for this failed trade
        if ($sellerShare->hold_quantity >= $pairing->share) {
            echo "     🚨 PROBLEM: Seller still has {$pairing->share} shares held for this FAILED trade\n";
            $needsFixing = true;
            $sharesToRelease[] = [
                'seller_share' => $sellerShare,
                'pairing' => $pairing,
                'shares_to_release' => $pairing->share
            ];
        } else {
            echo "     ✅ OK: No shares held (already released)\n";
        }
    }
    
    if ($needsFixing) {
        echo "\n   🔧 FIXING: Releasing held shares for failed trade\n";
        
        foreach ($sharesToRelease as $release) {
            $sellerShare = $release['seller_share'];
            $sharesToReleaseCount = $release['shares_to_release'];
            
            $oldHold = $sellerShare->hold_quantity;
            $oldTotal = $sellerShare->total_share_count;
            
            // Release the held shares
            $sellerShare->hold_quantity -= $sharesToReleaseCount;
            $sellerShare->total_share_count += $sharesToReleaseCount;
            $sellerShare->save();
            
            echo "     ✅ Released {$sharesToReleaseCount} shares from seller {$sellerShare->ticket_no}\n";
            echo "        Hold: {$oldHold} → {$sellerShare->hold_quantity}\n";
            echo "        Total: {$oldTotal} → {$sellerShare->total_share_count}\n";
            
            $totalSharesReleased += $sharesToReleaseCount;
        }
        
        $totalFixed++;
        
        // Log the fix
        Log::info("Fixed failed trade share release", [
            'failed_share_id' => $failedShare->id,
            'failed_ticket' => $failedShare->ticket_no,
            'shares_released' => array_sum(array_column($sharesToRelease, 'shares_to_release')),
            'fixed_by' => 'fix_failed_trade_shares_script'
        ]);
        
    } else {
        echo "   ✅ No fixing needed - shares already properly released\n";
    }
    
    echo "\n" . str_repeat("-", 40) . "\n\n";
}

echo str_repeat("=", 50) . "\n";
echo "📊 SUMMARY:\n";
echo "   Failed Shares Processed: {$failedShares->count()}\n";
echo "   Failed Shares Fixed: {$totalFixed}\n";
echo "   Total Shares Released: " . number_format($totalSharesReleased) . "\n";

if ($totalFixed > 0) {
    echo "\n🎉 SUCCESS! Fixed {$totalFixed} failed trades and released " . number_format($totalSharesReleased) . " shares back to available inventory.\n";
    echo "💡 These shares are now available for new buyers to purchase.\n";
} else {
    echo "\n✅ All failed trades are already properly handled. No fixes needed.\n";
}

echo "\n✅ Script completed!\n";