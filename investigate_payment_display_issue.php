<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;

echo "=== INVESTIGATING PAYMENT DISPLAY ISSUE ===\n";
echo str_repeat("=", 60) . "\n\n";

$share = UserShare::where('ticket_no', 'AB-17584718053546')->first();

if (!$share) {
    echo "❌ Share not found\n";
    exit;
}

echo "📊 SHARE DETAILS:\n";
echo "   Share ID: " . $share->id . "\n";
echo "   Ticket: " . $share->ticket_no . "\n";
echo "   Owner: " . $share->user->name . " (" . $share->user->username . ")\n\n";

echo "🔗 SELLER-SIDE PAIRINGS (where this share is being sold):\n";
$sellerPairings = UserSharePair::where('paired_user_share_id', $share->id)
    ->with(['pairedUserShare.user'])
    ->get();

foreach ($sellerPairings as $pairing) {
    echo "   Pairing ID: " . $pairing->id . "\n";
    echo "   Buyer Share ID: " . $pairing->user_share_id . "\n";
    echo "   Buyer: " . $pairing->pairedUserShare->user->name . " (" . $pairing->pairedUserShare->user->username . ")\n";
    echo "   Amount: " . number_format($pairing->share) . "\n";
    echo "   is_paid: " . $pairing->is_paid . "\n";
    
    // Get buyer's business profile (this should be displayed)
    $buyerProfile = json_decode($pairing->pairedUserShare->user->business_profile);
    echo "   Buyer's MPESA Name: " . ($buyerProfile->mpesa_name ?? 'N/A') . "\n";
    echo "   Buyer's MPESA No: " . ($buyerProfile->mpesa_no ?? 'N/A') . "\n";
    
    // Get seller's business profile (this is what's incorrectly being displayed)
    $sellerProfile = json_decode($share->user->business_profile);
    echo "   Seller's MPESA Name: " . ($sellerProfile->mpesa_name ?? 'N/A') . "\n";
    echo "   Seller's MPESA No: " . ($sellerProfile->mpesa_no ?? 'N/A') . "\n";
    
    // Check payment records for this pairing
    $payments = UserSharePayment::where('user_share_pair_id', $pairing->id)->get();
    echo "   Payment Records: " . $payments->count() . "\n";
    
    foreach ($payments as $payment) {
        echo "\n   💰 PAYMENT DETAILS:\n";
        echo "     Payment ID: " . $payment->id . "\n";
        echo "     Status: " . $payment->status . "\n";
        echo "     Amount: " . number_format($payment->amount) . "\n";
        echo "     Sender Name: " . $payment->name . "\n";
        echo "     Sender Phone: " . $payment->number . "\n";
        echo "     Transaction ID: " . $payment->txs_id . "\n";
        echo "     Note by Sender: " . ($payment->note_by_sender ?? 'None') . "\n";
        echo "     Created: " . $payment->created_at . "\n";
        
        echo "\n   🎯 WHAT SHOULD BE DISPLAYED:\n";
        echo "     ✅ Buyer Info: " . $pairing->pairedUserShare->user->name . " (" . $pairing->pairedUserShare->user->username . ")\n";
        echo "     ✅ Buyer MPESA: " . ($buyerProfile->mpesa_name ?? 'N/A') . " - " . ($buyerProfile->mpesa_no ?? 'N/A') . "\n";
        echo "     ✅ Payment Submitted by: " . $payment->name . " (" . $payment->number . ")\n";
        
        echo "\n   ❌ WHAT'S INCORRECTLY DISPLAYED:\n";
        echo "     ❌ If showing seller info: " . $share->user->name . " (" . $share->user->username . ")\n";
        echo "     ❌ If showing seller MPESA: " . ($sellerProfile->mpesa_name ?? 'N/A') . " - " . ($sellerProfile->mpesa_no ?? 'N/A') . "\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n";
}

echo "\n✅ INVESTIGATION COMPLETED\n";
echo str_repeat("=", 60) . "\n";