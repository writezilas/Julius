<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;

echo "\n🔍 INVESTIGATING MISSING SELLER VIEWS FOR PAYMENT CONFIRMATION\n";
echo str_repeat('=', 80) . "\n\n";

$buyerTicket = 'AB-17584353854220';
$buyerShare = UserShare::where('ticket_no', $buyerTicket)->first();

echo "📋 Buyer Share: {$buyerShare->ticket_no} (ID: {$buyerShare->id})\n";
echo "   Amount: {$buyerShare->amount} (should be split into 75000 + 10000)\n\n";

// Get all pairings where this share is the buyer
$buyerPairs = UserSharePair::where('user_share_id', $buyerShare->id)->get();

echo "🔗 DETAILED SELLER ANALYSIS:\n";
echo str_repeat('-', 60) . "\n";

$sellerGroups = [];

foreach ($buyerPairs as $pair) {
    $sellerShare = UserShare::find($pair->paired_user_share_id);
    if ($sellerShare) {
        $sellerId = $sellerShare->user_id;
        
        if (!isset($sellerGroups[$sellerId])) {
            $sellerGroups[$sellerId] = [
                'user_id' => $sellerId,
                'shares' => [],
                'total_amount' => 0
            ];
        }
        
        $sellerGroups[$sellerId]['shares'][] = [
            'share' => $sellerShare,
            'pair' => $pair,
            'amount' => $pair->share
        ];
        $sellerGroups[$sellerId]['total_amount'] += $pair->share;
    }
}

foreach ($sellerGroups as $sellerId => $group) {
    echo "\n👤 SELLER USER ID: {$sellerId}\n";
    echo "   📊 Total Amount from this seller: {$group['total_amount']}\n";
    echo str_repeat('-', 40) . "\n";
    
    foreach ($group['shares'] as $item) {
        $sellerShare = $item['share'];
        $pair = $item['pair'];
        
        echo "   🔗 Pair ID {$pair->id}: {$pair->share} shares\n";
        echo "      Share: {$sellerShare->ticket_no} (ID: {$sellerShare->id})\n";
        echo "      Status: {$sellerShare->status}, Ready: " . ($sellerShare->is_ready_to_sell ? 'Yes' : 'No') . "\n";
        echo "      Is Paid: {$pair->is_paid}\n";
        
        // Check if this seller share has the sold-share-view route
        echo "      Details URL: /sold-share/view/{$sellerShare->id}\n";
        
        // Check payments for this pair
        $payments = UserSharePayment::where('user_share_pair_id', $pair->id)->get();
        echo "      Payments: " . $payments->count() . "\n";
        
        if ($payments->count() > 0) {
            foreach ($payments as $payment) {
                echo "        Payment ID {$payment->id}: {$payment->status}, Amount: {$payment->amount}\n";
            }
        } else {
            echo "        ❌ No payment records found\n";
        }
        
        // Simulate accessing the sold-share-view for this seller share
        echo "      Testing sold-share-view access...\n";
        
        // Check if all pairings for this seller share appear in the view
        $allPairsForThisShare = UserSharePair::where('paired_user_share_id', $sellerShare->id)->get();
        echo "      All pairs for this seller share: " . $allPairsForThisShare->count() . "\n";
        
        foreach ($allPairsForThisShare as $sellerPair) {
            $payment = UserSharePayment::where('user_share_pair_id', $sellerPair->id)->first();
            $hasPayment = $payment ? 'YES' : 'NO';
            echo "        Pair {$sellerPair->id}: {$sellerPair->share} shares, Payment: {$hasPayment}\n";
            
            if (!$payment) {
                echo "          ❌ This pair will NOT show payment confirmation modal\n";
                echo "          ❌ Because line 64 in sold-share-view.blade.php requires @if(\$payment)\n";
            }
        }
        echo "\n";
    }
    
    // Check if this seller appears in sold shares page
    $soldShares = UserShare::with('trade')
        ->where('user_id', $sellerId)
        ->where(function($query) {
            $query->where(function($subQuery) {
                $subQuery->where('get_from', '!=', 'purchase')
                       ->whereHas('pairedWithThis')
                       ->whereIn('status', ['completed', 'sold', 'paired'])
                       ->whereNotNull('start_date')
                       ->where('start_date', '!=', '');
            })
            ->orWhere(function($subQuery) {
                $subQuery->where('get_from', '!=', 'purchase')
                       ->where('is_ready_to_sell', 1)
                       ->whereIn('status', ['completed', 'sold', 'paired'])
                       ->whereNotNull('start_date')
                       ->where('start_date', '!=', '');
            })
            ->orWhere(function($subQuery) {
                $subQuery->where('get_from', 'allocated-by-admin')
                       ->whereIn('status', ['completed', 'sold'])
                       ->whereNotNull('start_date')
                       ->where('start_date', '!=', '')
                       ->whereNotNull('selling_started_at');
            })
            ->orWhere(function($subQuery) {
                $subQuery->where('get_from', 'purchase')
                       ->where('status', 'completed')
                       ->where('is_ready_to_sell', 0)
                       ->whereNotNull('start_date')
                       ->where('start_date', '!=', '');
            })
            ->orWhere(function($subQuery) {
                $subQuery->where('get_from', 'purchase')
                       ->where('status', 'completed')
                       ->where('is_ready_to_sell', 1)
                       ->whereNotNull('start_date')
                       ->where('start_date', '!=', '');
            })
            ->orWhere(function($subQuery) {
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
    
    echo "   📄 Seller's sold shares page contains " . $soldShares->count() . " shares:\n";
    foreach ($soldShares as $soldShare) {
        echo "      - {$soldShare->ticket_no} (ID: {$soldShare->id})\n";
    }
    
    echo "\n" . str_repeat('=', 60) . "\n";
}

echo "\n🎯 PROBLEM ANALYSIS:\n";
echo "The issue is likely that payment confirmation modals don't appear\n";
echo "for pairs that don't have payment records yet, due to the @if(\$payment)\n";
echo "condition on line 64 of sold-share-view.blade.php\n\n";

echo "💡 EXPECTED BEHAVIOR:\n";
echo "- Seller should see all their paired shares in the sold-share-view\n";
echo "- Payment confirmation should be available even if no payment submitted yet\n";
echo "- The view should show 'Waiting for payment' status for unpaid pairs\n";

echo "\n" . str_repeat('=', 80) . "\n";