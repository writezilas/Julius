<?php
/**
 * Test Script to Verify Payment Confirmation Constraint Fix
 * 
 * This script tests that the payment confirmation issue for maddypower
 * on trade AB-17584713427 is now resolved after our constraint fixes.
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use App\Models\User;
use App\Services\PaymentConfirmationService;
use Illuminate\Support\Facades\DB;

echo "🧪 TESTING PAYMENT CONFIRMATION CONSTRAINT FIXES\n";
echo str_repeat("=", 70) . "\n\n";

$tradeTicket = 'AB-17584713427';
$sellerUsername = 'maddypower';

// Find the seller and payment
$seller = User::where('username', $sellerUsername)->first();
$payment = UserSharePayment::find(68);

if (!$seller || !$payment) {
    echo "❌ ERROR: Could not find seller or payment for testing\n";
    exit(1);
}

echo "📋 TEST SETUP:\n";
echo "   Seller: {$seller->name} ({$seller->username})\n";
echo "   Payment ID: {$payment->id}\n";
echo "   Amount: " . number_format($payment->amount) . "\n";
echo "   Current Status: {$payment->status}\n\n";

// Find the share pair
$sharePair = UserSharePair::find($payment->user_share_pair_id);
$pairedShare = UserShare::find($sharePair->paired_user_share_id);

echo "📊 CURRENT SHARE STATE:\n";
echo "   Share ID: {$pairedShare->id}\n";
echo "   Ticket: {$pairedShare->ticket_no}\n";
echo "   Status: {$pairedShare->status}\n";
echo "   is_ready_to_sell: {$pairedShare->is_ready_to_sell}\n";
echo "   is_sold: {$pairedShare->is_sold}\n";
echo "   total_share_count: " . number_format($pairedShare->total_share_count) . "\n";
echo "   hold_quantity: " . number_format($pairedShare->hold_quantity) . "\n";
echo "   sold_quantity: " . number_format($pairedShare->sold_quantity) . "\n\n";

// Test the validation service
echo "🔧 TESTING VALIDATION SERVICE:\n";
$paymentConfirmationService = new PaymentConfirmationService();

// Test payment validation
$validation = $paymentConfirmationService->validatePaymentConfirmation($payment->id);
echo "   Payment Validation: " . ($validation['success'] ? '✅ PASS' : '❌ FAIL - ' . $validation['message']) . "\n";

if ($validation['success']) {
    // Test quantity validation
    $quantityValidation = $paymentConfirmationService->validateSellerQuantity(
        $validation['pairedShare'], 
        $validation['sharePair']
    );
    echo "   Quantity Validation: " . ($quantityValidation['success'] ? '✅ PASS' : '❌ FAIL - ' . $quantityValidation['message']) . "\n";
    
    // Test status transition validation
    $statusValidation = $paymentConfirmationService->validateStatusTransition($pairedShare, 'sold');
    echo "   Status Transition (to 'sold'): " . ($statusValidation['success'] ? '✅ PASS' : '❌ FAIL - ' . $statusValidation['message']) . "\n";
} else {
    echo "   Skipping further tests due to payment validation failure\n";
}

// Test constraint compatibility by simulating the update
echo "\n🗄️  TESTING DATABASE CONSTRAINT COMPATIBILITY:\n";

try {
    DB::beginTransaction();
    
    // Test 1: Can we set status to 'sold' with is_ready_to_sell = 1?
    echo "   Testing 'sold' status with is_ready_to_sell = 1: ";
    
    $testShare = UserShare::find($pairedShare->id);
    $testShare->status = 'sold';
    $testShare->is_sold = 1;
    // Keep is_ready_to_sell as 1 to test the constraint
    
    $testShare->save();
    echo "✅ PASS\n";
    
    // Test 2: Can we set status to 'completed' with is_ready_to_sell = 1?
    echo "   Testing 'completed' status with is_ready_to_sell = 1: ";
    
    $testShare->status = 'completed';
    $testShare->is_sold = 0;
    
    $testShare->save();
    echo "✅ PASS\n";
    
    // Rollback test changes
    DB::rollBack();
    echo "   ✅ All constraint tests passed - rolled back changes\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ CONSTRAINT TEST FAILED: " . $e->getMessage() . "\n";
    echo "   This indicates the constraint fix may not have been applied correctly.\n";
}

// Test the actual payment confirmation process (simulation)
echo "\n🚀 SIMULATING PAYMENT CONFIRMATION PROCESS:\n";

try {
    // Check if payment is already confirmed
    if ($payment->status === 'conformed') {
        echo "   ⚠️  Payment is already confirmed. Testing if we can find any other unpaid payments...\n";
        
        // Look for other unpaid payments for this seller
        $unpaidPayments = UserSharePayment::whereHas('sharePair', function($query) use ($seller) {
            $query->whereHas('pairedShare', function($subQuery) use ($seller) {
                $subQuery->where('user_id', $seller->id);
            });
        })->where('status', 'paid')->get();
        
        if ($unpaidPayments->count() > 0) {
            echo "   Found " . $unpaidPayments->count() . " unpaid payment(s) for this seller:\n";
            foreach ($unpaidPayments as $unpaidPayment) {
                echo "     - Payment ID: {$unpaidPayment->id}, Amount: " . number_format($unpaidPayment->amount) . "\n";
            }
        } else {
            echo "   No unpaid payments found for this seller.\n";
        }
    } else {
        echo "   Payment status: {$payment->status} (ready for confirmation)\n";
        echo "   Share pair is_paid: " . ($sharePair->is_paid ? 'Yes' : 'No') . "\n";
        echo "   💡 Payment confirmation should now work without constraint violations.\n";
    }
    
} catch (Exception $e) {
    echo "❌ SIMULATION FAILED: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "📋 SUMMARY OF FIXES APPLIED:\n";
echo str_repeat("=", 70) . "\n";

echo "1. ✅ CONTROLLER FIX: Updated paymentApprove method to clear is_ready_to_sell when setting status to 'sold'\n";
echo "2. ✅ SERVICE FIX: Enhanced PaymentConfirmationService with constraint validation methods\n";
echo "3. ✅ CONSTRAINT FIX: Updated chk_ready_to_sell_logic constraint to include 'sold' status\n";
echo "4. ✅ ERROR HANDLING: Improved error messages for constraint-specific failures\n";
echo "5. ✅ VALIDATION: Added pre-validation to prevent constraint violations\n\n";

echo "🎯 EXPECTED OUTCOME:\n";
echo "- Seller 'maddypower' should now be able to confirm payment on trade AB-17584713427\n";
echo "- The 'Payment confirmation failed due to data integrity issues' error should be resolved\n";
echo "- Future payment confirmations should handle constraint requirements automatically\n\n";

echo "💡 NEXT STEPS:\n";
echo "- Ask seller 'maddypower' to try confirming the payment again\n";
echo "- Monitor application logs for any remaining constraint violations\n";
echo "- If issues persist, check for other constraint violations in the database\n\n";

echo "✅ TEST COMPLETED\n";