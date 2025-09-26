<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Http\Controllers\UserShareController;

echo "=== TESTING DISPLAY FIX FOR TRADE AB-17584718053546 ===\n";
echo str_repeat("=", 70) . "\n\n";

$share = UserShare::where('ticket_no', 'AB-17584718053546')->first();

if (!$share) {
    echo "❌ Share not found\n";
    exit;
}

echo "📊 SHARE DETAILS:\n";
echo "   ID: " . $share->id . "\n";
echo "   Ticket: " . $share->ticket_no . "\n";
echo "   User: " . $share->user->name . "\n";
echo "   get_from: " . ($share->get_from ?? 'NULL') . "\n";
echo "   is_ready_to_sell: " . $share->is_ready_to_sell . "\n\n";

// Test the new logic by creating a controller instance
$controller = new UserShareController();

// Use reflection to call the private method
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('getPairingContextForSoldShare');
$method->setAccessible(true);

$context = $method->invoke($controller, $share);

echo "🔧 NEW PAIRING CONTEXT:\n";
echo "   shouldShow: " . ($context['shouldShow'] ? 'TRUE' : 'FALSE') . "\n";
echo "   showSellerHistory: " . ($context['showSellerHistory'] ? 'TRUE' : 'FALSE') . "\n";
echo "   showBuyerHistory: " . ($context['showBuyerHistory'] ? 'TRUE' : 'FALSE') . "\n";
echo "   hasSellerPairings: " . ($context['hasSellerPairings'] ? 'TRUE' : 'FALSE') . "\n";
echo "   hasBuyerPairings: " . ($context['hasBuyerPairings'] ? 'TRUE' : 'FALSE') . "\n\n";

echo "🎯 EXPECTED BEHAVIOR:\n";
if ($context['shouldShow']) {
    echo "   ✅ Pair history WILL be displayed\n";
    if ($context['showSellerHistory']) {
        echo "   ✅ Current selling pairings WILL be shown\n";
    }
    if ($context['showBuyerHistory']) {
        echo "   ✅ Historical buying pairings WILL be shown\n";
    }
} else {
    echo "   ❌ Pair history will NOT be displayed\n";
    if ($context['hasSellerPairings']) {
        echo "   ⚠️  WARNING: Share has seller pairings but they won't be shown\n";
    }
}

echo "\n🔍 COMPARISON WITH OLD LOGIC:\n";
$oldMethod = $reflection->getMethod('shouldShowPairHistoryForSoldShare');
$oldMethod->setAccessible(true);
$oldResult = $oldMethod->invoke($controller, $share);

echo "   Old logic result: " . ($oldResult ? 'TRUE' : 'FALSE') . "\n";
echo "   New logic result: " . ($context['shouldShow'] ? 'TRUE' : 'FALSE') . "\n";

if ($oldResult !== $context['shouldShow']) {
    echo "   🎉 LOGIC CHANGED: Display behavior has been improved!\n";
} else {
    echo "   ⚠️  Same result as before\n";
}

echo "\n✅ TEST COMPLETED\n";
echo str_repeat("=", 70) . "\n";