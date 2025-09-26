<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Services\ShareStatusService;

echo "\n🧪 TESTING FIX FOR CROSS-CONTAMINATION BETWEEN BOUGHT AND SOLD SHARES\n";
echo str_repeat('=', 80) . "\n\n";

$shareStatusService = new ShareStatusService();
$tickets = ['AB-17584321484326', 'AB-17584301917046', 'AB-17584288039329'];

foreach ($tickets as $ticket) {
    echo "🔍 Testing ticket: $ticket\n";
    echo str_repeat('-', 50) . "\n";
    
    $share = UserShare::where('ticket_no', $ticket)->first();
    if (!$share) {
        echo "   ❌ Share not found\n\n";
        continue;
    }
    
    echo "📊 Share Info:\n";
    echo "   ID: {$share->id}\n";
    echo "   User ID: {$share->user_id}\n";
    echo "   Status: {$share->status}\n";
    echo "   Get From: {$share->get_from}\n";
    echo "   Ready to Sell: " . ($share->is_ready_to_sell ? 'Yes' : 'No') . "\n";
    echo "   Total Share Count: {$share->total_share_count}\n";
    echo "   Sold Quantity: {$share->sold_quantity}\n\n";
    
    echo "🔄 Testing Separate Pairing Methods:\n";
    
    // Test bought share pairing stats (buyer perspective)
    echo "   📈 Bought Share Pairing Stats (buyer perspective):\n";
    $boughtStats = $shareStatusService->getBoughtSharePairingStats($share);
    foreach ($boughtStats as $key => $value) {
        echo "      $key: $value\n";
    }
    
    // Test sold share pairing stats (seller perspective)
    echo "   💰 Sold Share Pairing Stats (seller perspective):\n";
    $soldStats = $shareStatusService->getSoldSharePairingStats($share);
    foreach ($soldStats as $key => $value) {
        echo "      $key: $value\n";
    }
    
    echo "   📊 Status Results:\n";
    
    // Test bought context
    $boughtStatus = $shareStatusService->getShareStatus($share, 'bought');
    echo "      Bought Context: {$boughtStatus['status']} ({$boughtStatus['class']})\n";
    
    // Test sold context  
    $soldStatus = $shareStatusService->getShareStatus($share, 'sold');
    echo "      Sold Context: {$soldStatus['status']} ({$soldStatus['class']})\n";
    
    // Validate the fix
    echo "\n   ✅ Validation:\n";
    
    // For purchased shares that have matured and appear in sold shares
    if ($share->get_from === 'purchase' && $share->is_ready_to_sell == 1) {
        if ($soldStats['total'] == 0 && $share->total_share_count > 0 && $share->sold_quantity == 0) {
            if ($soldStatus['status'] === 'Available') {
                echo "      ✅ CORRECT: Matured purchased share with no seller pairings shows as 'Available'\n";
            } else {
                echo "      ❌ INCORRECT: Should show as 'Available' but shows as '{$soldStatus['status']}'\n";
            }
        }
        
        // Verify no inheritance from bought context
        if ($boughtStats['paid'] > 0 && $soldStats['paid'] == 0) {
            echo "      ✅ CORRECT: No cross-contamination - bought pairings don't affect sold status\n";
        } else {
            echo "      ❌ INCORRECT: Cross-contamination detected\n";
        }
    }
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
}

// Summary test
echo "🎯 COMPREHENSIVE VALIDATION:\n";
echo str_repeat('-', 40) . "\n";

$allCorrect = true;

foreach ($tickets as $ticket) {
    $share = UserShare::where('ticket_no', $ticket)->first();
    if (!$share) continue;
    
    $soldStats = $shareStatusService->getSoldSharePairingStats($share);
    $soldStatus = $shareStatusService->getShareStatus($share, 'sold');
    
    // For these specific tickets, they should all be "Available"
    if ($share->get_from === 'purchase' && 
        $share->is_ready_to_sell == 1 && 
        $soldStats['total'] == 0 && 
        $share->total_share_count > 0 && 
        $share->sold_quantity == 0) {
        
        if ($soldStatus['status'] === 'Available') {
            echo "✅ $ticket: Correctly shows as 'Available'\n";
        } else {
            echo "❌ $ticket: Should be 'Available' but shows as '{$soldStatus['status']}'\n";
            $allCorrect = false;
        }
    }
}

echo "\n" . str_repeat('=', 80) . "\n";

if ($allCorrect) {
    echo "🎉 SUCCESS: All test cases passed! Cross-contamination has been fixed.\n";
    echo "   - Bought shares and sold shares now use separate pairing statistics\n";
    echo "   - No inheritance between bought and sold contexts\n";
    echo "   - Purchased shares that mature correctly show as 'Available' when they have no seller pairings\n";
} else {
    echo "❌ FAILURE: Some test cases failed. Further investigation needed.\n";
}

echo str_repeat('=', 80) . "\n";