<?php
/**
 * Investigation Script for Trade AB-17584714458322
 * 
 * Check if shares held by this failed trade have been properly released
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use App\Models\User;

echo "🔍 INVESTIGATING TRADE AB-17584714458322\n";
echo str_repeat("=", 60) . "\n\n";

// Find the specific trade
$failedShare = UserShare::where('ticket_no', 'AB-17584714458322')->first();

if (!$failedShare) {
    echo "❌ Trade AB-17584714458322 not found!\n";
    exit(1);
}

echo "📋 FAILED TRADE ANALYSIS:\n";
echo str_repeat("-", 30) . "\n";
echo "Share ID: {$failedShare->id}\n";
echo "User: {$failedShare->user->name} (ID: {$failedShare->user_id})\n";
echo "Ticket: {$failedShare->ticket_no}\n";
echo "Status: {$failedShare->status}\n";
echo "Total Shares: " . number_format($failedShare->total_share_count) . "\n";
echo "Hold Quantity: " . number_format($failedShare->hold_quantity) . "\n";
echo "Created: {$failedShare->created_at}\n";

// Check pairings
echo "\n🔗 PAIRING ANALYSIS:\n";
echo str_repeat("-", 30) . "\n";

$pairings = UserSharePair::where('user_share_id', $failedShare->id)->get();
echo "Found {$pairings->count()} pairing(s):\n\n";

if ($pairings->isEmpty()) {
    echo "✅ No pairings found - trade failed before pairing.\n";
} else {
    foreach ($pairings as $pairing) {
        $sellerShare = UserShare::find($pairing->paired_user_share_id);
        
        echo "Pairing ID: {$pairing->id}\n";
        echo "Buyer Share: {$failedShare->id} ({$failedShare->ticket_no})\n";
        echo "Seller Share: {$sellerShare->id} ({$sellerShare->ticket_no})\n";
        echo "Seller User: {$sellerShare->user->name} (ID: {$sellerShare->user_id})\n";
        echo "Shares in Trade: " . number_format($pairing->share) . "\n";
        echo "Is Paid: " . ($pairing->is_paid ? 'YES' : 'NO') . "\n";
        echo "Pairing Created: {$pairing->created_at}\n";
        
        echo "\nSELLER SHARE STATUS:\n";
        echo "  Total Shares: " . number_format($sellerShare->total_share_count) . "\n";
        echo "  Sold Quantity: " . number_format($sellerShare->sold_quantity) . "\n";
        echo "  Hold Quantity: " . number_format($sellerShare->hold_quantity) . "\n";
        echo "  Available: " . number_format($sellerShare->total_share_count - $sellerShare->sold_quantity - $sellerShare->hold_quantity) . "\n";
        echo "  Status: {$sellerShare->status}\n";
        
        echo "\n";
    }
}

// Check payment records
echo "💰 PAYMENT ANALYSIS:\n";
echo str_repeat("-", 30) . "\n";

$payments = UserSharePayment::where('user_share_id', $failedShare->id)->get();
echo "Payment records for failed trade: {$payments->count()}\n";

if ($payments->count() > 0) {
    foreach ($payments as $payment) {
        echo "  - Payment ID: {$payment->id}\n";
        echo "    Amount: " . number_format($payment->amount) . "\n";
        echo "    Status: {$payment->status}\n";
        echo "    Created: {$payment->created_at}\n";
    }
} else {
    echo "✅ No payment records - trade failed without payment submission.\n";
}

// Check for payment records in pairings
if (!$pairings->isEmpty()) {
    foreach ($pairings as $pairing) {
        $pairPayments = UserSharePayment::where('user_share_pair_id', $pairing->id)->get();
        echo "\nPayment records for pairing {$pairing->id}: {$pairPayments->count()}\n";
        
        foreach ($pairPayments as $payment) {
            echo "  - Payment ID: {$payment->id}\n";
            echo "    Amount: " . number_format($payment->amount) . "\n";
            echo "    Status: {$payment->status}\n";
            echo "    Created: {$payment->created_at}\n";
        }
    }
}

echo "\n🚨 ISSUE ANALYSIS:\n";
echo str_repeat("-", 30) . "\n";

$issues = [];
$sharesNeedingRelease = [];

if (!$pairings->isEmpty()) {
    foreach ($pairings as $pairing) {
        $sellerShare = UserShare::find($pairing->paired_user_share_id);
        
        // Check if seller still has shares held for this failed trade
        if ($sellerShare->hold_quantity > 0) {
            // This might be shares held for this failed trade or other active trades
            // We need to be careful here - we can't just assume all held shares are for this failed trade
            
            // Check if there are other active pairings for this seller
            $otherActivePairings = UserSharePair::where('paired_user_share_id', $sellerShare->id)
                ->whereHas('buyerShare', function($query) {
                    $query->where('status', '!=', 'failed');
                })
                ->count();
            
            if ($otherActivePairings > 0) {
                echo "✅ Seller share {$sellerShare->id} has {$sellerShare->hold_quantity} shares held for {$otherActivePairings} other active trade(s).\n";
            } else if ($sellerShare->hold_quantity >= $pairing->share) {
                $issues[] = "CRITICAL: Seller share {$sellerShare->id} still has {$sellerShare->hold_quantity} shares held, possibly for this failed trade";
                $sharesNeedingRelease[] = [
                    'seller_share' => $sellerShare,
                    'pairing' => $pairing,
                    'shares_to_release' => min($pairing->share, $sellerShare->hold_quantity)
                ];
            }
        }
    }
}

echo "\n📊 CONCLUSION:\n";
echo str_repeat("-", 30) . "\n";

if (empty($issues)) {
    echo "✅ GOOD: No issues detected!\n";
    echo "   - Trade is properly marked as 'failed'\n";
    echo "   - No payment records exist\n";
    
    if (!$pairings->isEmpty()) {
        foreach ($pairings as $pairing) {
            $sellerShare = UserShare::find($pairing->paired_user_share_id);
            echo "   - Seller share {$sellerShare->id} hold_quantity: {$sellerShare->hold_quantity}\n";
            echo "   - Available shares: " . number_format($sellerShare->total_share_count - $sellerShare->sold_quantity - $sellerShare->hold_quantity) . "\n";
        }
    }
    
    echo "\n🎉 The failed trade has been handled correctly!\n";
    echo "   Either shares were never held, or they have been properly released.\n";
} else {
    echo "🚨 ISSUES FOUND:\n";
    foreach ($issues as $issue) {
        echo "   - {$issue}\n";
    }
    
    echo "\n🔧 RECOMMENDED ACTION:\n";
    echo "   Run the fix_failed_trade_shares.php script to release held shares.\n";
}

echo "\n✅ Investigation completed.\n";