<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;

echo "\n🔍 INVESTIGATING MISSING PAIR VIEW ISSUE\n";
echo str_repeat('=', 70) . "\n\n";

$ticket = 'AB-17584353854220';
echo "📋 Investigating ticket: $ticket\n";
echo str_repeat('-', 50) . "\n";

// Find the main share
$share = UserShare::where('ticket_no', $ticket)->first();
if (!$share) {
    echo "❌ Share not found with ticket: $ticket\n";
    exit;
}

echo "🗂️ Main Share Info:\n";
echo "   ID: {$share->id}\n";
echo "   User ID: {$share->user_id}\n";
echo "   Status: {$share->status}\n";
echo "   Get From: {$share->get_from}\n";
echo "   Amount: {$share->amount}\n";
echo "   Ready to Sell: " . ($share->is_ready_to_sell ? 'Yes' : 'No') . "\n\n";

// Find all pairings related to this share
echo "👥 PAIRING ANALYSIS:\n";
echo str_repeat('-', 30) . "\n";

// As seller (this share is being sold to others)
echo "1. AS SELLER (paired_user_share_id = {$share->id}):\n";
$sellerPairs = UserSharePair::where('paired_user_share_id', $share->id)->get();
echo "   Count: " . $sellerPairs->count() . "\n";

foreach ($sellerPairs as $pair) {
    echo "   Pair ID {$pair->id}:\n";
    echo "     - Share amount: {$pair->share}\n";
    echo "     - Is paid: {$pair->is_paid} (0=unpaid, 1=paid, 2=failed)\n";
    echo "     - Created: {$pair->created_at}\n";
    
    // Get buyer info
    $buyerShare = UserShare::find($pair->user_share_id);
    if ($buyerShare) {
        echo "     - Buyer ticket: {$buyerShare->ticket_no}\n";
        echo "     - Buyer user ID: {$buyerShare->user_id}\n";
        echo "     - Buyer amount: {$buyerShare->amount}\n";
        
        // Check for payments on this pair
        $payments = UserSharePayment::where('user_share_pair_id', $pair->id)->get();
        echo "     - Payments: " . $payments->count() . "\n";
        
        foreach ($payments as $payment) {
            echo "       Payment ID {$payment->id}: Status={$payment->status}, Amount={$payment->amount}\n";
        }
    }
    echo "\n";
}

// As buyer (this share bought from others)
echo "2. AS BUYER (user_share_id = {$share->id}):\n";
$buyerPairs = UserSharePair::where('user_share_id', $share->id)->get();
echo "   Count: " . $buyerPairs->count() . "\n";

foreach ($buyerPairs as $pair) {
    echo "   Pair ID {$pair->id}:\n";
    echo "     - Share amount: {$pair->share}\n";
    echo "     - Is paid: {$pair->is_paid}\n";
    echo "     - Seller share ID: {$pair->paired_user_share_id}\n";
    echo "\n";
}

// Now check what appears in sold shares page
echo "📊 SOLD SHARES PAGE QUERY SIMULATION:\n";
echo str_repeat('-', 40) . "\n";

// Get the seller user ID
$sellerUserId = $share->user_id;
echo "Seller User ID: $sellerUserId\n\n";

// Simulate the sold shares query from HomeController
$soldShares = UserShare::with('trade')
    ->where('user_id', $sellerUserId)
    ->where(function($query) {
        // Group ALL conditions under a single WHERE to ensure proper user_id scoping
        $query->where(function($subQuery) {
            // Show shares that have buyers (traditional selling)
            $subQuery->where('get_from', '!=', 'purchase')
                   ->whereHas('pairedWithThis')
                   ->whereIn('status', ['completed', 'sold', 'paired'])
                   ->whereNotNull('start_date')
                   ->where('start_date', '!=', '');
        })
        ->orWhere(function($subQuery) {
            // OR show matured shares that are ready to sell (excluding buyer trades)
            $subQuery->where('get_from', '!=', 'purchase')
                   ->where('is_ready_to_sell', 1)
                   ->whereIn('status', ['completed', 'sold', 'paired'])
                   ->whereNotNull('start_date')
                   ->where('start_date', '!=', '');
        })
        ->orWhere(function($subQuery) {
            // OR show admin-allocated shares
            $subQuery->where('get_from', 'allocated-by-admin')
                   ->whereIn('status', ['completed', 'sold'])
                   ->whereNotNull('start_date')
                   ->where('start_date', '!=', '')
                   ->whereNotNull('selling_started_at');
        })
        ->orWhere(function($subQuery) {
            // OR show purchased shares in countdown
            $subQuery->where('get_from', 'purchase')
                   ->where('status', 'completed')
                   ->where('is_ready_to_sell', 0)
                   ->whereNotNull('start_date')
                   ->where('start_date', '!=', '');
        })
        ->orWhere(function($subQuery) {
            // OR show purchased shares that have matured
            $subQuery->where('get_from', 'purchase')
                   ->where('status', 'completed')
                   ->where('is_ready_to_sell', 1)
                   ->whereNotNull('start_date')
                   ->where('start_date', '!=', '');
        })
        ->orWhere(function($subQuery) {
            // OR show shares with active buyer pairings
            $subQuery->whereHas('pairedWithThis', function($pairQuery) {
                      $pairQuery->where('is_paid', 0)
                               ->whereHas('payment', function($paymentQuery) {
                                   $paymentQuery->where('status', 'paid');
                               });
                   })
                   ->whereIn('status', ['completed', 'sold', 'paired'])
                   ->whereNotNull('start_date')
                   ->where('start_date', '!=', '');
        });
    })
    ->get();

echo "Shares appearing in sold shares page for user $sellerUserId:\n";
foreach ($soldShares as $soldShare) {
    echo "- Share ID {$soldShare->id} ({$soldShare->ticket_no}): Status={$soldShare->status}, Amount={$soldShare->amount}\n";
    
    if ($soldShare->id == $share->id) {
        echo "  ✅ Main share IS appearing in sold shares page\n";
    }
}

// Check if our main share appears
$mainShareInSold = $soldShares->where('id', $share->id)->first();
if ($mainShareInSold) {
    echo "\n✅ Main share appears in sold shares page\n";
} else {
    echo "\n❌ Main share does NOT appear in sold shares page\n";
}

echo "\n" . str_repeat('=', 70) . "\n";