<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Models\UserSharePair;

echo "=== DIAGNOSTIC: WHY PAIRS DON'T SHOW ON SOLD SHARES VIEW ===\n";
echo str_repeat("=", 70) . "\n\n";

$share = UserShare::where('ticket_no', 'AB-17584718053546')->first();

if (!$share) {
    echo "❌ Share not found\n";
    exit;
}

echo "📊 SHARE ANALYSIS:\n";
echo "   ID: " . $share->id . "\n";
echo "   Ticket: " . $share->ticket_no . "\n";
echo "   User: " . $share->user->name . " (ID: " . $share->user_id . ")\n";
echo "   get_from: " . ($share->get_from ?? 'NULL') . "\n";
echo "   is_ready_to_sell: " . $share->is_ready_to_sell . "\n";
echo "   Status: " . $share->status . "\n\n";

// Test the shouldShowPairHistoryForSoldShare logic
echo "🔍 PAIR HISTORY DISPLAY LOGIC TEST:\n";

// Replicate the exact logic from UserShareController@shouldShowPairHistoryForSoldShare
$shouldShowPairHistory = true;

// Case 1: If this is a purchased share that has matured and is ready to sell
if ($share->get_from === 'purchase' && $share->is_ready_to_sell == 1) {
    $shouldShowPairHistory = false;
    echo "   ❌ HIDDEN: Share is from purchase and is_ready_to_sell = 1\n";
    echo "   Reason: Case 1 - Purchased share that has matured\n";
}
// Case 2: If this is a purchased share in countdown mode (not ready to sell yet)
elseif ($share->get_from === 'purchase' && $share->is_ready_to_sell == 0) {
    $shouldShowPairHistory = false;
    echo "   ❌ HIDDEN: Share is from purchase and is_ready_to_sell = 0\n";
    echo "   Reason: Case 2 - Purchased share still in transition\n";
}
// Case 3: If this is an admin-allocated share or other non-purchase share
elseif ($share->get_from !== 'purchase') {
    $shouldShowPairHistory = true;
    echo "   ✅ SHOWN: Share is not from purchase\n";
    echo "   Reason: Case 3 - Admin-allocated or other non-purchase share\n";
} else {
    $shouldShowPairHistory = false;
    echo "   ❌ HIDDEN: Default case\n";
    echo "   Reason: Safety default\n";
}

echo "\n   Final shouldShowPairHistory: " . ($shouldShowPairHistory ? 'TRUE' : 'FALSE') . "\n\n";

// Check what pairs exist
echo "🔗 EXISTING PAIRINGS ANALYSIS:\n";
$sellerPairs = UserSharePair::where('paired_user_share_id', $share->id)->get();
echo "   Seller-side pairings: " . $sellerPairs->count() . "\n";

foreach ($sellerPairs as $pair) {
    $buyerShare = UserShare::find($pair->user_share_id);
    echo "     - Pairing ID: {$pair->id}\n";
    echo "       Buyer: {$buyerShare->user->name} ({$buyerShare->ticket_no})\n";
    echo "       Amount: " . number_format($pair->share) . "\n";
    echo "       is_paid: {$pair->is_paid}\n";
    echo "       Created: {$pair->created_at}\n\n";
}

$buyerPairs = UserSharePair::where('user_share_id', $share->id)->get();
echo "   Buyer-side pairings: " . $buyerPairs->count() . "\n";

foreach ($buyerPairs as $pair) {
    $sellerShare = UserShare::find($pair->paired_user_share_id);
    echo "     - Pairing ID: {$pair->id}\n";
    echo "       Seller: {$sellerShare->user->name} ({$sellerShare->ticket_no})\n";
    echo "       Amount: " . number_format($pair->share) . "\n";
    echo "       is_paid: {$pair->is_paid}\n";
    echo "       Created: {$pair->created_at}\n\n";
}

echo "🎯 ROOT CAUSE ANALYSIS:\n";
echo "   The share shows as 'Partially Paired' because it HAS pairings.\n";
echo "   However, the pairs are NOT displayed on the view page because:\n\n";

if (!$shouldShowPairHistory) {
    echo "   ❌ PROBLEM IDENTIFIED:\n";
    echo "   The shouldShowPairHistoryForSoldShare() method returns FALSE\n";
    echo "   because this share has get_from = '{$share->get_from}' and is_ready_to_sell = {$share->is_ready_to_sell}\n\n";
    
    echo "   📝 EXPLANATION:\n";
    echo "   This logic was designed to prevent confusion for shares that have\n";
    echo "   transitioned from bought phase to sold phase. The intention was to\n";
    echo "   hide OLD pair history from the buying phase when the share matures\n";
    echo "   and becomes ready to be sold to NEW buyers.\n\n";
    
    echo "   🚨 THE ISSUE:\n";
    echo "   However, this logic is TOO AGGRESSIVE and is hiding CURRENT selling\n";
    echo "   pairings (where this share is being sold) along with old buying\n";
    echo "   pairings (where this share was bought).\n\n";
    
    echo "   💡 WHAT SHOULD HAPPEN:\n";
    echo "   - Hide buyer-side pairings (old history from when user bought this share)\n";
    echo "   - Show seller-side pairings (current activity where user is selling this share)\n";
    echo "   - Or show both with clear distinction\n\n";
} else {
    echo "   ✅ Pair history should be displayed based on logic\n";
    echo "   If pairs are still not showing, check the template logic\n\n";
}

echo "🔧 TEMPLATE LOGIC VERIFICATION:\n";
echo "   In the sold-share-view.blade.php template, pairs are only loaded if:\n";
echo "   - shouldShowPairHistory variable is TRUE (line 42)\n";
echo "   - The template checks: @if (isset(\$shouldShowPairHistory) && \$shouldShowPairHistory)\n";
echo "   \n";
echo "   Since shouldShowPairHistory = " . ($shouldShowPairHistory ? 'TRUE' : 'FALSE') . ",\n";
echo "   the template will " . ($shouldShowPairHistory ? 'LOAD and DISPLAY' : 'NOT LOAD') . " the pairs section.\n\n";

if (!$shouldShowPairHistory) {
    echo "   📋 WHAT USER SEES INSTEAD:\n";
    echo "   - The 'Share Has Matured' message (lines 472-492)\n";
    echo "   - \"Once new buyers are available, pairing information will be displayed here\"\n";
    echo "   - This is misleading because the share IS paired but info is hidden\n\n";
}

echo "✅ DIAGNOSIS COMPLETED\n";
echo str_repeat("=", 70) . "\n";