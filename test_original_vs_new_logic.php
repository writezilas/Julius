<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;

echo "=== ORIGINAL vs NEW LOGIC COMPARISON ===\n";
echo str_repeat("=", 70) . "\n\n";

$share = UserShare::where('ticket_no', 'AB-17584718053546')->first();

if (!$share) {
    echo "❌ Share not found\n";
    exit;
}

echo "📊 SHARE DETAILS:\n";
echo "   ID: " . $share->id . "\n";
echo "   Ticket: " . $share->ticket_no . "\n";
echo "   get_from: " . ($share->get_from ?? 'NULL') . "\n";
echo "   is_ready_to_sell: " . $share->is_ready_to_sell . "\n\n";

// Test original logic manually
echo "🔍 ORIGINAL LOGIC TEST:\n";
$originalResult = false;

// Case 1: If this is a purchased share that has matured and is ready to sell
if ($share->get_from === 'purchase' && $share->is_ready_to_sell == 1) {
    $originalResult = false;
    echo "   ❌ Original would return FALSE (Case 1: purchased + ready_to_sell)\n";
}
// Case 2: If this is a purchased share in countdown mode 
elseif ($share->get_from === 'purchase' && $share->is_ready_to_sell == 0) {
    $originalResult = false;
    echo "   ❌ Original would return FALSE (Case 2: purchased + not ready_to_sell)\n";
}
// Case 3: If this is an admin-allocated share or other non-purchase share
elseif ($share->get_from !== 'purchase') {
    $originalResult = true;
    echo "   ✅ Original would return TRUE (Case 3: non-purchase share)\n";
} else {
    $originalResult = false;
    echo "   ❌ Original would return FALSE (Default case)\n";
}

echo "   Original logic result: " . ($originalResult ? 'TRUE' : 'FALSE') . "\n\n";

// Test new logic
echo "🔧 NEW LOGIC TEST:\n";

// Check for pairings
$sellerPairings = \App\Models\UserSharePair::where('paired_user_share_id', $share->id)->exists();
$buyerPairings = \App\Models\UserSharePair::where('user_share_id', $share->id)->exists();

echo "   Has seller pairings: " . ($sellerPairings ? 'YES' : 'NO') . "\n";
echo "   Has buyer pairings: " . ($buyerPairings ? 'YES' : 'NO') . "\n";

$newResult = false;

if ($share->get_from !== 'purchase') {
    $newResult = $sellerPairings || $buyerPairings;
    echo "   Non-purchase share: Show if any pairings exist\n";
} elseif ($share->get_from === 'purchase') {
    if ($sellerPairings) {
        $newResult = true;
        echo "   ✅ Purchase share with seller pairings: SHOW current selling activity\n";
    } elseif ($buyerPairings && $share->is_ready_to_sell == 0) {
        $newResult = true;
        echo "   ✅ Purchase share with buyer pairings (not ready): SHOW buying history\n";
    } else {
        $newResult = false;
        echo "   ❌ Purchase share: No relevant pairings to show\n";
    }
}

echo "   New logic result: " . ($newResult ? 'TRUE' : 'FALSE') . "\n\n";

echo "🎯 COMPARISON RESULT:\n";
if ($originalResult !== $newResult) {
    echo "   🎉 LOGIC IMPROVED!\n";
    echo "   Original: " . ($originalResult ? 'SHOW' : 'HIDE') . " pairs\n";
    echo "   New: " . ($newResult ? 'SHOW' : 'HIDE') . " pairs\n";
    
    if (!$originalResult && $newResult) {
        echo "   ✅ Fix successful: Pairs will now be displayed when they should be\n";
    } elseif ($originalResult && !$newResult) {
        echo "   ⚠️  Logic became more restrictive\n";
    }
} else {
    echo "   Same result as before - but this might still be correct\n";
}

echo "\n📋 WHAT USER WILL SEE:\n";
if ($newResult) {
    echo "   ✅ User will see the pairing table with transaction details\n";
    echo "   ✅ Status cards showing pairing statistics\n";
    echo "   ✅ Payment confirmation options\n";
} else {
    echo "   ❌ User will see 'Share has matured' message\n";
    echo "   ❌ No pairing details displayed\n";
    echo "   ❌ This is the bug we're trying to fix!\n";
}

echo "\n✅ TEST COMPLETED\n";
echo str_repeat("=", 70) . "\n";