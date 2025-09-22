<?php
/**
 * Fix Script for Trade AB-17584713427 Payment Confirmation Issue
 * 
 * This script fixes the specific data inconsistency where payment ID 68 is paid
 * but the share pair (ID: 119) is not marked as is_paid = 1
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
use Illuminate\Support\Facades\Log;

echo "🔧 FIXING TRADE AB-17584713427 PAYMENT CONFIRMATION ISSUE\n";
echo str_repeat("=", 70) . "\n\n";

try {
    DB::beginTransaction();

    // Find the specific payment that should be confirmable
    $payment = UserSharePayment::find(68);
    
    if (!$payment) {
        echo "❌ ERROR: Payment ID 68 not found!\n";
        exit(1);
    }
    
    echo "📋 PAYMENT DETAILS:\n";
    echo "   Payment ID: {$payment->id}\n";
    echo "   Amount: " . number_format($payment->amount) . "\n";
    echo "   Status: {$payment->status}\n";
    echo "   Share Pair ID: {$payment->user_share_pair_id}\n";
    echo "   Sender: {$payment->sender_id}\n";
    echo "   Receiver: {$payment->receiver_id}\n\n";
    
    // Find the share pair
    $sharePair = UserSharePair::find($payment->user_share_pair_id);
    
    if (!$sharePair) {
        echo "❌ ERROR: Share Pair ID {$payment->user_share_pair_id} not found!\n";
        exit(1);
    }
    
    echo "📋 SHARE PAIR DETAILS:\n";
    echo "   Pair ID: {$sharePair->id}\n";
    echo "   Share Amount: " . number_format($sharePair->share) . "\n";
    echo "   Is Paid: " . ($sharePair->is_paid ? 'Yes' : 'No') . "\n";
    echo "   Buyer Share ID: {$sharePair->user_share_id}\n";
    echo "   Seller Share ID: {$sharePair->paired_user_share_id}\n\n";
    
    // Validate using our new service
    $paymentConfirmationService = new PaymentConfirmationService();
    $validation = $paymentConfirmationService->validatePaymentConfirmation($payment->id);
    
    if (!$validation['success']) {
        echo "❌ ERROR: Payment validation failed: {$validation['message']}\n";
        exit(1);
    }
    
    echo "✅ Payment validation: PASSED\n";
    
    $quantityValidation = $paymentConfirmationService->validateSellerQuantity(
        $validation['pairedShare'], 
        $validation['sharePair']
    );
    
    if (!$quantityValidation['success']) {
        echo "❌ ERROR: Quantity validation failed: {$quantityValidation['message']}\n";
        exit(1);
    }
    
    echo "✅ Quantity validation: PASSED\n";
    echo "💡 This payment is confirmable! Proceeding with fix...\n\n";
    
    // Extract validated objects
    $userShare = $validation['userShare'];
    $pairedShare = $validation['pairedShare'];
    
    echo "🔧 SIMULATING PAYMENT CONFIRMATION:\n";
    echo "   Setting payment status to 'conformed'...\n";
    echo "   Setting share pair is_paid to 1...\n";
    echo "   Updating buyer share total_share_count from " . number_format($userShare->total_share_count) . 
         " to " . number_format($userShare->total_share_count + $sharePair->share) . "\n";
    echo "   Updating seller share hold_quantity from " . number_format($pairedShare->hold_quantity) . 
         " to " . number_format($pairedShare->hold_quantity - $sharePair->share) . "\n";
    echo "   Updating seller share sold_quantity from " . number_format($pairedShare->sold_quantity) . 
         " to " . number_format($pairedShare->sold_quantity + $sharePair->share) . "\n\n";
    
    // Ask for confirmation before making changes
    echo "⚠️  WARNING: This will make actual changes to the database!\n";
    echo "Do you want to proceed with the fix? (y/N): ";
    
    // In a web environment, we'll skip the interactive part and just log what would be done
    if (php_sapi_name() === 'cli') {
        $handle = fopen("php://stdin", "r");
        $confirmation = strtolower(trim(fgets($handle)));
        fclose($handle);
    } else {
        $confirmation = 'n'; // Default to no for web execution
        echo "n (auto-selected for web execution)\n";
    }
    
    if ($confirmation === 'y' || $confirmation === 'yes') {
        echo "🚀 APPLYING FIX...\n\n";
        
        // Update payment status
        $payment->status = 'conformed';
        $payment->note_by_receiver = 'Fixed by automated repair script';
        $payment->save();
        echo "✅ Payment status updated to 'conformed'\n";
        
        // Update share pair
        $sharePair->is_paid = 1;
        $sharePair->save();
        echo "✅ Share pair marked as paid\n";
        
        // Update buyer share
        $userShare->increment('total_share_count', $sharePair->share);
        echo "✅ Buyer share total_share_count updated\n";
        
        // Update seller share
        $pairedShare->decrement('hold_quantity', $sharePair->share);
        $pairedShare->increment('sold_quantity', $sharePair->share);
        echo "✅ Seller share quantities updated\n";
        
        // Check if buyer share should be marked as completed
        if ($userShare->share_will_get == $userShare->total_share_count) {
            $userShare->status = 'completed';
            $userShare->start_date = now()->format("Y/m/d H:i:s");
            
            // Handle timer management
            $paymentConfirmationService->manageTimers($userShare);
            
            $userShare->save();
            echo "✅ Buyer share marked as completed\n";
        }
        
        // Check if seller share should be updated
        if ($pairedShare->total_share_count == 0 && $pairedShare->hold_quantity == 0 && $pairedShare->sold_quantity > 0) {
            $pairedShare->status = 'sold';
            $pairedShare->is_sold = 1;
            $pairedShare->save();
            echo "✅ Seller share marked as sold\n";
        } elseif ($pairedShare->status !== 'sold') {
            $pairedShare->status = 'completed';
            $pairedShare->save();
            echo "✅ Seller share marked as completed\n";
        }
        
        // Create logs
        $paymentConfirmationService->createPaymentLogs($payment);
        echo "✅ Payment logs created\n";
        
        // Send notifications
        $paymentConfirmationService->sendPaymentNotification($payment);
        echo "✅ Payment notification sent\n";
        
        DB::commit();
        echo "\n🎉 FIX COMPLETED SUCCESSFULLY!\n";
        echo "Trade AB-17584713427 payment confirmation has been fixed.\n";
        echo "Seller 'maddypower' should now be able to see this payment as confirmed.\n";
        
    } else {
        DB::rollBack();
        echo "❌ FIX CANCELLED by user.\n";
        echo "No changes were made to the database.\n";
    }
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ ERROR during fix: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    exit(1);
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "✅ SCRIPT COMPLETED\n";