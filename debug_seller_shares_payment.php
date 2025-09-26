<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;

echo "\n🔍 CHECKING SELLER SHARES FOR PAYMENT CONFIRMATION\n";
echo str_repeat('=', 70) . "\n\n";

$buyerTicket = 'AB-17584353854220';
$buyerShare = UserShare::where('ticket_no', $buyerTicket)->first();

echo "📋 Buyer Share: {$buyerShare->ticket_no} (ID: {$buyerShare->id})\n";
echo "   User ID: {$buyerShare->user_id}\n";
echo "   Amount: {$buyerShare->amount}\n\n";

// Get all pairings where this share is the buyer
$buyerPairs = UserSharePair::where('user_share_id', $buyerShare->id)->get();

echo "🔗 ANALYZING SELLER SHARES & PAYMENT CONFIRMATION:\n";
echo str_repeat('-', 50) . "\n";

foreach ($buyerPairs as $pair) {
    echo "Pair ID {$pair->id}: {$pair->share} shares, is_paid={$pair->is_paid}\n";
    
    // Get the seller share
    $sellerShare = UserShare::find($pair->paired_user_share_id);
    if ($sellerShare) {
        echo "  📊 Seller Share: {$sellerShare->ticket_no} (ID: {$sellerShare->id})\n";
        echo "     - Seller User ID: {$sellerShare->user_id}\n";
        echo "     - Seller Amount: {$sellerShare->amount}\n";
        echo "     - Status: {$sellerShare->status}\n";
        echo "     - Ready to Sell: " . ($sellerShare->is_ready_to_sell ? 'Yes' : 'No') . "\n";
        
        // Check if seller share appears in seller's sold shares page
        $sellerSoldShares = UserShare::with('trade')
            ->where('user_id', $sellerShare->user_id)
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
        
        $inSoldPage = $sellerSoldShares->where('id', $sellerShare->id)->first();
        echo "     - Appears in seller's sold shares page: " . ($inSoldPage ? 'YES ✅' : 'NO ❌') . "\n";
        
        // Check for payments on this pair
        $payments = UserSharePayment::where('user_share_pair_id', $pair->id)->get();
        echo "     - Payments: " . $payments->count() . "\n";
        
        foreach ($payments as $payment) {
            echo "       Payment ID {$payment->id}:\n";
            echo "         - Status: {$payment->status}\n";
            echo "         - Amount: {$payment->amount}\n";
            echo "         - Name: {$payment->name}\n";
            echo "         - Number: {$payment->number}\n";
            echo "         - Created: {$payment->created_at}\n";
        }
        
        // Check why seller share might not appear in sold shares
        if (!$inSoldPage) {
            echo "     ❌ DIAGNOSIS - Why seller share doesn't appear:\n";
            echo "       - get_from: {$sellerShare->get_from}\n";
            echo "       - status: {$sellerShare->status}\n";
            echo "       - start_date: " . ($sellerShare->start_date ?: 'NULL') . "\n";
            echo "       - is_ready_to_sell: " . ($sellerShare->is_ready_to_sell ? '1' : '0') . "\n";
            echo "       - has pairedWithThis: " . ($sellerShare->pairedWithThis()->count() > 0 ? 'YES' : 'NO') . "\n";
            echo "       - selling_started_at: " . ($sellerShare->selling_started_at ?: 'NULL') . "\n";
        }
    }
    echo "\n" . str_repeat('-', 30) . "\n";
}

echo "\n🎯 SUMMARY:\n";
echo "The issue appears to be that some seller shares are not appearing in their\n";
echo "respective sellers' sold shares pages, which prevents payment confirmation.\n";
echo "This needs to be fixed in the sold shares query logic.\n";

echo "\n" . str_repeat('=', 70) . "\n";