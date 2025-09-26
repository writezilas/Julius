<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use App\Services\ShareStatusService;

echo "=== INVESTIGATING PARTIALLY PAIRED ISSUE ===\n";
echo str_repeat("=", 70) . "\n\n";

$share = UserShare::where('ticket_no', 'AB-17584718053546')->first();

if (!$share) {
    echo "❌ Share not found\n";
    exit;
}

echo "📊 SHARE DETAILS:\n";
echo "   ID: " . $share->id . "\n";
echo "   Ticket: " . $share->ticket_no . "\n";
echo "   User: " . $share->user->name . " (" . $share->user->username . ")\n";
echo "   Status: " . $share->status . "\n";
echo "   is_ready_to_sell: " . $share->is_ready_to_sell . "\n";
echo "   is_sold: " . $share->is_sold . "\n";
echo "   total_share_count: " . number_format($share->total_share_count) . "\n";
echo "   hold_quantity: " . number_format($share->hold_quantity) . "\n";
echo "   sold_quantity: " . number_format($share->sold_quantity) . "\n";

$statusService = new ShareStatusService();
$statusInfo = $statusService->getShareStatus($share, 'sold');
echo "   Status Display: " . $statusInfo['status'] . "\n\n";

echo "🔗 SELLER-SIDE PAIRINGS (where this share is the seller):\n";
$sellerPairings = UserSharePair::where('paired_user_share_id', $share->id)->get();
echo "   Found " . $sellerPairings->count() . " seller-side pairing(s):\n";

foreach ($sellerPairings as $pairing) {
    echo "     Pairing ID: {$pairing->id}\n";
    echo "     Buyer Share ID: {$pairing->user_share_id}\n";
    echo "     Share amount: " . number_format($pairing->share) . "\n";
    echo "     is_paid: {$pairing->is_paid}\n";
    echo "     Created: {$pairing->created_at}\n";
    
    $buyerShare = UserShare::find($pairing->user_share_id);
    if ($buyerShare) {
        echo "     Buyer: {$buyerShare->user->name} ({$buyerShare->user->username})\n";
        echo "     Buyer Share: {$buyerShare->ticket_no} (Status: {$buyerShare->status})\n";
        
        // Check for payments
        $payments = UserSharePayment::where('user_share_pair_id', $pairing->id)->get();
        echo "     Payment Records: " . $payments->count() . "\n";
        foreach ($payments as $payment) {
            echo "       - Payment ID: {$payment->id}, Status: {$payment->status}, Amount: " . number_format($payment->amount) . "\n";
        }
    }
    echo "\n";
}

echo "🔗 BUYER-SIDE PAIRINGS (where this share is the buyer):\n";
$buyerPairings = UserSharePair::where('user_share_id', $share->id)->get();
echo "   Found " . $buyerPairings->count() . " buyer-side pairing(s):\n";

foreach ($buyerPairings as $pairing) {
    echo "     Pairing ID: {$pairing->id}\n";
    echo "     Seller Share ID: {$pairing->paired_user_share_id}\n";
    echo "     Share amount: " . number_format($pairing->share) . "\n";
    echo "     is_paid: {$pairing->is_paid}\n";
    echo "     Created: {$pairing->created_at}\n";
    
    $sellerShare = UserShare::find($pairing->paired_user_share_id);
    if ($sellerShare) {
        echo "     Seller: {$sellerShare->user->name} ({$sellerShare->user->username})\n";
        echo "     Seller Share: {$sellerShare->ticket_no} (Status: {$sellerShare->status})\n";
    }
    echo "\n";
}

echo "📈 PAIRING STATISTICS:\n";
$stats = $statusService->getSoldSharePairingStats($share);
echo "   Paid: " . $stats['paid'] . "\n";
echo "   Unpaid: " . $stats['unpaid'] . "\n";
echo "   Awaiting confirmation: " . $stats['awaiting_confirmation'] . "\n";
echo "   Failed: " . $stats['failed'] . "\n";
echo "   Total: " . $stats['total'] . "\n";
echo "   Total amount paired: " . number_format($stats['total_amount_paired']) . "\n\n";

echo "🔍 STATUS ANALYSIS:\n";
$investmentPlusProfit = ($share->share_will_get ?? 0) + ($share->profit_share ?? 0);
echo "   Investment + Profit: " . number_format($investmentPlusProfit) . "\n";
echo "   Total Amount Paired: " . number_format($stats['total_amount_paired']) . "\n";
echo "   Percentage Paired: " . ($investmentPlusProfit > 0 ? round(($stats['total_amount_paired'] / $investmentPlusProfit) * 100, 2) : 0) . "%\n\n";

// Check why it shows as partially paired
if ($statusInfo['status'] === 'Partially Paired') {
    echo "💡 WHY 'PARTIALLY PAIRED':\n";
    if ($stats['awaiting_confirmation'] > 0) {
        echo "   - Has pairings awaiting confirmation: " . $stats['awaiting_confirmation'] . "\n";
    }
    if ($stats['unpaid'] > 0) {
        echo "   - Has unpaid pairings: " . $stats['unpaid'] . "\n";
    }
    if ($stats['total_amount_paired'] < $investmentPlusProfit) {
        echo "   - Not fully paired: " . number_format($stats['total_amount_paired']) . " < " . number_format($investmentPlusProfit) . "\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "✅ INVESTIGATION COMPLETED\n";