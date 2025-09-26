<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use App\Services\ShareStatusService;

echo "=== INVESTIGATING STATUS UNKNOWN FOR TRADE AB-17584713427 ===\n";
echo str_repeat("=", 70) . "\n\n";

$share = UserShare::where('ticket_no', 'AB-17584713427')->first();

if (!$share) {
    echo "Share not found\n";
    exit;
}

echo "📊 SHARE DETAILS:\n";
echo "   Share ID: " . $share->id . "\n";
echo "   Ticket: " . $share->ticket_no . "\n";
echo "   Status: " . $share->status . "\n";
echo "   is_ready_to_sell: " . $share->is_ready_to_sell . "\n";
echo "   is_sold: " . $share->is_sold . "\n";
echo "   start_date: " . ($share->start_date ?? 'NULL') . "\n";
echo "   period: " . ($share->period ?? 'NULL') . "\n";
echo "   total_share_count: " . $share->total_share_count . "\n";
echo "   hold_quantity: " . $share->hold_quantity . "\n";
echo "   sold_quantity: " . $share->sold_quantity . "\n";
echo "   selling_started_at: " . ($share->selling_started_at ?? 'NULL') . "\n\n";

$service = new ShareStatusService();

echo "🔧 TESTING TIME REMAINING LOGIC:\n";
$timeInfo = $service->getTimeRemaining($share, 'sold');
echo "   Time text: " . $timeInfo['text'] . "\n";
echo "   Time class: " . $timeInfo['class'] . "\n";
echo "   Time color: " . $timeInfo['color'] . "\n\n";

echo "📈 PAIRING STATS:\n";
$stats = $service->getSoldSharePairingStats($share);
echo "   Paid: " . $stats['paid'] . "\n";
echo "   Unpaid: " . $stats['unpaid'] . "\n";
echo "   Awaiting confirmation: " . $stats['awaiting_confirmation'] . "\n";
echo "   Failed: " . $stats['failed'] . "\n";
echo "   Total: " . $stats['total'] . "\n";
echo "   Total amount paired: " . $stats['total_amount_paired'] . "\n\n";

echo "🔗 DETAILED PAIRING ANALYSIS:\n";
$sellerSidePairings = UserSharePair::where('paired_user_share_id', $share->id)->get();
echo "   Found " . $sellerSidePairings->count() . " seller-side pairing(s):\n";

foreach ($sellerSidePairings as $pairing) {
    echo "     Pairing ID: " . $pairing->id . "\n";
    echo "     Buyer Share ID: " . $pairing->user_share_id . "\n";
    echo "     Share amount: " . $pairing->share . "\n";
    echo "     is_paid: " . $pairing->is_paid . "\n";
    
    $buyerShare = UserShare::find($pairing->user_share_id);
    if ($buyerShare) {
        echo "     Buyer status: " . $buyerShare->status . "\n";
        
        // Check for payment records
        $hasSubmittedPayment = $buyerShare->payments()
            ->where('user_share_pair_id', $pairing->id)
            ->where('status', 'paid')
            ->exists();
        
        $confirmedPayment = $buyerShare->payments()
            ->where('user_share_pair_id', $pairing->id)
            ->where('status', 'conformed')
            ->exists();
            
        echo "     Has submitted payment: " . ($hasSubmittedPayment ? 'YES' : 'NO') . "\n";
        echo "     Has confirmed payment: " . ($confirmedPayment ? 'YES' : 'NO') . "\n";
    }
    echo "\n";
}

echo "🔍 CHECKING SPECIFIC LOGIC CONDITIONS:\n";

// Test condition 1: is_ready_to_sell
echo "   is_ready_to_sell == 1: " . ($share->is_ready_to_sell == 1 ? 'TRUE' : 'FALSE') . "\n";

// Test condition 2: hasShareMatured
$hasMatured = false;
if ($share->start_date && $share->period && $share->status === 'completed') {
    try {
        $maturityDate = \Carbon\Carbon::parse($share->start_date)->addDays($share->period);
        $now = \Carbon\Carbon::now();
        $hasMatured = $maturityDate <= $now;
    } catch (Exception $e) {
        echo "   Error checking maturity: " . $e->getMessage() . "\n";
    }
}
echo "   hasShareMatured(): " . ($hasMatured ? 'TRUE' : 'FALSE') . "\n";

// Test which branch should be taken in getSoldShareTimeRemaining
echo "\n🧠 LOGIC FLOW ANALYSIS:\n";

if ($share->is_ready_to_sell == 1) {
    echo "   ✅ Would enter is_ready_to_sell branch\n";
    if ($stats['awaiting_confirmation'] > 0) {
        echo "   ✅ Would show 'Awaiting Confirmation'\n";
    } else {
        echo "   ✅ Would show 'Share Matured'\n";
    }
} else {
    echo "   ❌ Would NOT enter is_ready_to_sell branch\n";
    echo "   Checking status-based conditions:\n";
    
    switch ($share->status) {
        case 'paired':
            echo "   ✅ Status is 'paired'\n";
            if ($stats['awaiting_confirmation'] > 0) {
                echo "   ✅ Would show 'Payment received - confirm to complete'\n";
            } else {
                echo "   ✅ Would show 'Waiting for payments'\n";
            }
            break;
        case 'completed':
            echo "   ✅ Status is 'completed'\n";
            if ($share->start_date && $share->period && $share->is_ready_to_sell == 0) {
                echo "   ✅ Would show timer-active (sell maturity countdown)\n";
            } else {
                echo "   ❌ Would skip timer condition\n";
            }
            break;
        case 'failed':
            echo "   ✅ Status is 'failed', would show 'Transaction failed'\n";
            break;
        default:
            echo "   ❌ Status '" . $share->status . "' not handled in switch, would fall through to 'Status unknown'\n";
            break;
    }
}

echo "\n💡 DIAGNOSIS:\n";
if ($timeInfo['text'] === 'Status unknown') {
    echo "   ❌ ISSUE CONFIRMED: Falling through to 'Status unknown'\n";
    echo "   🔧 LIKELY CAUSE: Share status is '" . $share->status . "' with is_ready_to_sell=" . $share->is_ready_to_sell . "\n";
    
    if ($share->status === 'completed' && $share->is_ready_to_sell == 1) {
        echo "   🔧 EXPECTED: Should show 'Share Matured'\n";
        echo "   🐛 BUG: Logic is not correctly handling completed + ready_to_sell combination\n";
    } elseif ($share->status === 'completed' && $share->is_ready_to_sell == 0) {
        echo "   🔧 EXPECTED: Should show sell maturity timer\n";
        echo "   🐛 BUG: Timer conditions not met or logic issue\n";
    }
} else {
    echo "   ✅ Status display is working correctly\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "✅ INVESTIGATION COMPLETED\n";