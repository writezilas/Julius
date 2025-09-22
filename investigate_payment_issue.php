<?php
/**
 * Investigation Script for Payment Confirmation Issue
 * 
 * Trade: AB-17584699064
 * Issue: maddypower unable to confirm payment of 110,000 from Johana
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use App\Models\User;
use Illuminate\Support\Facades\Log;

echo "🔍 INVESTIGATING PAYMENT CONFIRMATION ISSUE\n";
echo str_repeat("=", 60) . "\n\n";

// Find the seller share (Maddy Power)
echo "📋 SELLER SHARE ANALYSIS:\n";
echo str_repeat("-", 30) . "\n";

$sellerShare = UserShare::where('ticket_no', 'AB-17584699064')->first();

if (!$sellerShare) {
    echo "❌ ERROR: Share AB-17584699064 not found!\n";
    exit(1);
}

echo "Seller Share ID: {$sellerShare->id}\n";
echo "User: {$sellerShare->user->name} (ID: {$sellerShare->user_id})\n";
echo "Ticket: {$sellerShare->ticket_no}\n";
echo "Total Share Count: " . number_format($sellerShare->total_share_count) . "\n";
echo "Status: {$sellerShare->status}\n";
echo "Is Ready to Sell: " . ($sellerShare->is_ready_to_sell ? 'Yes' : 'No') . "\n";
echo "Sold Quantity: " . number_format($sellerShare->sold_quantity) . "\n";
echo "Available to Sell: " . number_format($sellerShare->total_share_count - $sellerShare->sold_quantity) . "\n\n";

// Find all pairs for this share
echo "🔗 PAIRING ANALYSIS:\n";
echo str_repeat("-", 30) . "\n";

$pairs = UserSharePair::where('paired_user_share_id', $sellerShare->id)->get();

echo "Found " . $pairs->count() . " pairing(s):\n\n";

foreach ($pairs as $pair) {
    $buyerShare = UserShare::find($pair->user_share_id);
    $buyer = User::find($pair->user_id);
    
    echo "Pair ID: {$pair->id}\n";
    echo "Buyer: {$buyer->name} (ID: {$buyer->id})\n";
    echo "Buyer Share ID: {$buyerShare->id} ({$buyerShare->ticket_no})\n";
    echo "Buyer Share Status: {$buyerShare->status}\n";
    echo "Shares to Buy: " . number_format($pair->share) . "\n";
    echo "Is Paid: " . ($pair->is_paid ? 'Yes' : 'No') . "\n";
    echo "Decline Attempts: {$pair->decline_attempts}\n";
    echo "Created: {$pair->created_at}\n";
    
    // Check for payment records
    $payments = UserSharePayment::where('user_share_pair_id', $pair->id)->get();
    echo "Payment Records: " . $payments->count() . "\n";
    
    foreach ($payments as $payment) {
        echo "  - Payment ID: {$payment->id}\n";
        echo "  - Amount: " . number_format($payment->amount) . "\n";
        echo "  - Status: {$payment->status}\n";
        echo "  - Created: {$payment->created_at}\n";
    }
    
    echo "\n" . str_repeat("-", 20) . "\n\n";
}

// Check for issues
echo "🚨 ISSUE ANALYSIS:\n";
echo str_repeat("-", 30) . "\n";

$issues = [];

// Issue 1: Share has 0 total_share_count but pairs exist
if ($sellerShare->total_share_count == 0 && $pairs->count() > 0) {
    $issues[] = "CRITICAL: Seller share has 0 shares but has active pairs";
}

// Issue 2: Multiple pairs for same seller share
if ($pairs->count() > 1) {
    $issues[] = "WARNING: Multiple buyers paired with same seller share";
}

// Issue 3: Total requested shares exceed available shares
$totalRequested = $pairs->sum('share');
$available = $sellerShare->total_share_count - $sellerShare->sold_quantity;

if ($totalRequested > $available) {
    $issues[] = "CRITICAL: Total requested shares (" . number_format($totalRequested) . ") exceed available shares (" . number_format($available) . ")";
}

// Issue 4: Payment made but pair not marked as paid
foreach ($pairs as $pair) {
    $paidPayments = UserSharePayment::where('user_share_pair_id', $pair->id)
        ->where('status', 'paid')
        ->count();
    
    if ($paidPayments > 0 && !$pair->is_paid) {
        $issues[] = "INCONSISTENCY: Pair {$pair->id} has paid payments but is_paid = 0";
    }
}

if (empty($issues)) {
    echo "✅ No critical issues detected.\n";
} else {
    echo "Found " . count($issues) . " issue(s):\n\n";
    foreach ($issues as $index => $issue) {
        echo ($index + 1) . ". {$issue}\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";

// Suggest solutions
echo "💡 RECOMMENDED SOLUTIONS:\n";
echo str_repeat("-", 30) . "\n";

if ($sellerShare->total_share_count == 0) {
    echo "1. RESTORE SELLER SHARES:\n";
    echo "   The seller share was matured but total_share_count is 0.\n";
    echo "   This should be 110,000 (100,000 original + 10,000 profit).\n";
    echo "   Fix: UPDATE user_shares SET total_share_count = 110000 WHERE id = {$sellerShare->id};\n\n";
}

if ($pairs->count() > 1) {
    echo "2. RESOLVE DUPLICATE PAIRINGS:\n";
    echo "   Only one buyer should be paired with the seller.\n";
    echo "   Review and cancel/fail duplicate pairs.\n\n";
}

$paidPair = $pairs->whereIn('id', function($query) {
    return UserSharePayment::where('status', 'paid')
        ->pluck('user_share_pair_id');
})->first();

if ($paidPair && !$paidPair->is_paid) {
    echo "3. UPDATE PAYMENT STATUS:\n";
    echo "   Pair {$paidPair->id} has confirmed payment but is_paid flag is not set.\n";
    echo "   Fix: UPDATE user_share_pairs SET is_paid = 1 WHERE id = {$paidPair->id};\n\n";
}

echo "✅ Investigation completed.\n";