<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;

echo "=== INVESTIGATING PAYMENT SUBMISSION DETAILS ISSUE ===\n";
echo str_repeat("=", 60) . "\n\n";

$share = UserShare::where('ticket_no', 'AB-17584718053546')->first();

if (!$share) {
    echo "❌ Share not found\n";
    exit;
}

echo "📊 SHARE: " . $share->ticket_no . " (Seller: " . $share->user->name . ")\n\n";

// Get seller-side pairings
$sellerPairings = UserSharePair::where('paired_user_share_id', $share->id)
    ->with(['pairedUserShare.user'])
    ->get();

foreach ($sellerPairings as $pairing) {
    echo "🔗 PAIRING ID: " . $pairing->id . "\n";
    echo "   Buyer: " . $pairing->pairedUserShare->user->name . " (" . $pairing->pairedUserShare->user->username . ")\n";
    
    $payment = UserSharePayment::where('user_share_pair_id', $pairing->id)
        ->orderBy('id', 'desc')
        ->first();
    
    if ($payment) {
        echo "\n💰 CURRENT PAYMENT RECORD:\n";
        echo "   Payment ID: " . $payment->id . "\n";
        echo "   Sender Name (from payment): " . $payment->name . "\n";
        echo "   Phone Number (from payment): " . $payment->number . "\n";
        echo "   Amount: " . number_format($payment->amount) . "\n";
        echo "   Status: " . $payment->status . "\n";
        
        // Get buyer's business profile
        $buyerProfile = json_decode($pairing->pairedUserShare->user->business_profile);
        echo "\n📋 BUYER'S BUSINESS PROFILE:\n";
        echo "   MPESA Name: " . ($buyerProfile->mpesa_name ?? 'NULL') . "\n";
        echo "   MPESA Number: " . ($buyerProfile->mpesa_no ?? 'NULL') . "\n";
        echo "   MPESA Till Name: " . ($buyerProfile->mpesa_till_name ?? 'NULL') . "\n";
        echo "   MPESA Till Number: " . ($buyerProfile->mpesa_till_number ?? 'NULL') . "\n";
        
        // Get seller's business profile for comparison
        $sellerProfile = json_decode($share->user->business_profile);
        echo "\n👤 SELLER'S BUSINESS PROFILE (currently shown incorrectly):\n";
        echo "   MPESA Name: " . ($sellerProfile->mpesa_name ?? 'NULL') . "\n";
        echo "   MPESA Number: " . ($sellerProfile->mpesa_no ?? 'NULL') . "\n";
        echo "   MPESA Till Name: " . ($sellerProfile->mpesa_till_name ?? 'NULL') . "\n";
        echo "   MPESA Till Number: " . ($sellerProfile->mpesa_till_number ?? 'NULL') . "\n";
        
        echo "\n🎯 PAYMENT DISPLAY LOGIC NEEDED:\n";
        echo "   Current Issue: Payment shows seller's info instead of buyer's\n";
        echo "   \n";
        echo "   ❌ Currently Showing (WRONG):\n";
        echo "     Sender Name: " . $payment->name . " (from payment record)\n";
        echo "     Phone Number: " . $payment->number . " (from payment record)\n";
        echo "   \n";
        echo "   ✅ Should Show (CORRECT):\n";
        
        // Determine what buyer payment info to show
        if (!empty($buyerProfile->mpesa_till_number) && !empty($buyerProfile->mpesa_till_name)) {
            echo "     Buyer's Till Name: " . $buyerProfile->mpesa_till_name . "\n";
            echo "     Buyer's Till Number: " . $buyerProfile->mpesa_till_number . "\n";
        } else {
            echo "     Buyer's MPESA Name: " . ($buyerProfile->mpesa_name ?? 'N/A') . "\n";
            echo "     Buyer's MPESA Number: " . ($buyerProfile->mpesa_no ?? 'N/A') . "\n";
        }
        
        echo "   \n";
        echo "   💡 LOGIC PRIORITY:\n";
        echo "     1. If buyer has Till Name & Till Number (not NULL) -> Show Till info\n";
        echo "     2. Otherwise -> Show regular MPESA Name & Number\n";
        
        echo "\n" . str_repeat("-", 50) . "\n";
    }
}

echo "\n✅ INVESTIGATION COMPLETED\n";
echo str_repeat("=", 60) . "\n";