<?php
/**
 * Diagnostic and Fix Script for Trade AB-17584713427 Payment Confirmation Issue
 * 
 * This script investigates the specific error where seller "maddypower" 
 * cannot confirm payment on trade "AB-17584713427"
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

echo "🔍 INVESTIGATING TRADE AB-17584713427 PAYMENT ISSUE\n";
echo str_repeat("=", 70) . "\n\n";

$tradeTicket = 'AB-17584713427';
$sellerUsername = 'maddypower';

// Find the seller user
$seller = User::where('username', $sellerUsername)->first();
if (!$seller) {
    echo "❌ ERROR: Seller '$sellerUsername' not found!\n";
    exit(1);
}

echo "👤 SELLER INFORMATION:\n";
echo "   Name: {$seller->name}\n";
echo "   Username: {$seller->username}\n";
echo "   User ID: {$seller->id}\n\n";

// Find the share with this ticket number
$share = UserShare::where('ticket_no', $tradeTicket)->first();
if (!$share) {
    echo "❌ ERROR: Share with ticket '$tradeTicket' not found!\n";
    exit(1);
}

echo "📊 SHARE INFORMATION:\n";
echo "   Share ID: {$share->id}\n";
echo "   Ticket: {$share->ticket_no}\n";
echo "   Owner: {$share->user->name} (ID: {$share->user_id})\n";
echo "   Status: {$share->status}\n";
echo "   Total Share Count: " . number_format($share->total_share_count) . "\n";
echo "   Hold Quantity: " . number_format($share->hold_quantity) . "\n";
echo "   Sold Quantity: " . number_format($share->sold_quantity) . "\n";
echo "   Share Will Get: " . number_format($share->share_will_get) . "\n";
echo "   Created: {$share->created_at}\n\n";

// Check if this is a seller share (owned by maddypower)
if ($share->user_id == $seller->id) {
    echo "✅ This is a seller share owned by $sellerUsername\n\n";
    $isSellerShare = true;
    $sellerShare = $share;
    
    // Find all pairs where this share is the seller (paired_user_share_id)
    $pairs = UserSharePair::where('paired_user_share_id', $share->id)->get();
} else {
    echo "❌ This share is NOT owned by $sellerUsername\n";
    echo "   Looking for seller shares...\n\n";
    
    $sellerShares = UserShare::where('user_id', $seller->id)->get();
    echo "🔍 Found " . $sellerShares->count() . " shares owned by $sellerUsername:\n";
    
    foreach ($sellerShares as $sellerShare) {
        echo "   - {$sellerShare->ticket_no} (Status: {$sellerShare->status}, Shares: " . number_format($sellerShare->total_share_count) . ")\n";
    }
    
    // Look for pairs involving this trade ticket
    $pairs = UserSharePair::whereHas('pairedShare', function($query) use ($tradeTicket) {
        $query->where('ticket_no', $tradeTicket);
    })->orWhereHas('pairedUserShare', function($query) use ($tradeTicket) {
        $query->where('ticket_no', $tradeTicket);
    })->get();
    
    echo "\n🔗 Found " . $pairs->count() . " pair(s) related to this trade:\n";
}

if ($pairs->isEmpty()) {
    echo "❌ No pairs found for this trade!\n";
    exit(1);
}

echo "🔗 PAIRING ANALYSIS:\n";
echo str_repeat("-", 50) . "\n";

foreach ($pairs as $index => $pair) {
    echo "PAIR #" . ($index + 1) . ":\n";
    echo "   Pair ID: {$pair->id}\n";
    echo "   Buyer Share ID: {$pair->user_share_id}\n";
    echo "   Seller Share ID: {$pair->paired_user_share_id}\n";
    echo "   Share Amount: " . number_format($pair->share) . "\n";
    echo "   Is Paid: " . ($pair->is_paid ? 'Yes' : 'No') . "\n";
    echo "   Created: {$pair->created_at}\n";
    
    $buyerShare = UserShare::find($pair->user_share_id);
    $sellerShare = UserShare::find($pair->paired_user_share_id);
    $buyer = $buyerShare ? $buyerShare->user : null;
    $seller = $sellerShare ? $sellerShare->user : null;
    
    if ($buyer) {
        echo "   Buyer: {$buyer->name} ({$buyer->username})\n";
        echo "   Buyer Share: {$buyerShare->ticket_no} (Status: {$buyerShare->status})\n";
    }
    
    if ($seller) {
        echo "   Seller: {$seller->name} ({$seller->username})\n";
        echo "   Seller Share: {$sellerShare->ticket_no} (Status: {$sellerShare->status})\n";
    }
    
    // Find payment records for this pair
    $payments = UserSharePayment::where('user_share_pair_id', $pair->id)->get();
    echo "   Payment Records: " . $payments->count() . "\n";
    
    foreach ($payments as $paymentIndex => $payment) {
        echo "     Payment #" . ($paymentIndex + 1) . ":\n";
        echo "       ID: {$payment->id}\n";
        echo "       Amount: " . number_format($payment->amount) . "\n";
        echo "       Status: {$payment->status}\n";
        echo "       Sender: {$payment->sender_id}\n";
        echo "       Receiver: {$payment->receiver_id}\n";
        echo "       Created: {$payment->created_at}\n";
        
        // Test payment confirmation with our new service
        if ($payment->status === 'paid') {
            echo "       🔧 TESTING PAYMENT CONFIRMATION:\n";
            $paymentConfirmationService = new PaymentConfirmationService();
            $validation = $paymentConfirmationService->validatePaymentConfirmation($payment->id);
            
            if ($validation['success']) {
                echo "       ✅ Payment validation: PASSED\n";
                
                $quantityValidation = $paymentConfirmationService->validateSellerQuantity(
                    $validation['pairedShare'], 
                    $validation['sharePair']
                );
                
                if ($quantityValidation['success']) {
                    echo "       ✅ Quantity validation: PASSED\n";
                    echo "       💡 This payment should be confirmable!\n";
                } else {
                    echo "       ❌ Quantity validation: FAILED\n";
                    echo "       ❌ Error: {$quantityValidation['message']}\n";
                    echo "       ❌ Code: {$quantityValidation['error_code']}\n";
                }
            } else {
                echo "       ❌ Payment validation: FAILED\n";
                echo "       ❌ Error: {$validation['message']}\n";
                echo "       ❌ Code: {$validation['error_code']}\n";
            }
        }
    }
    
    echo "\n" . str_repeat("-", 30) . "\n\n";
}

// Data integrity checks
echo "🔍 DATA INTEGRITY CHECKS:\n";
echo str_repeat("-", 50) . "\n";

$issues = [];

foreach ($pairs as $pair) {
    $buyerShare = UserShare::find($pair->user_share_id);
    $sellerShare = UserShare::find($pair->paired_user_share_id);
    
    // Check for missing related records
    if (!$buyerShare) {
        $issues[] = "Missing buyer share for pair ID {$pair->id}";
    }
    
    if (!$sellerShare) {
        $issues[] = "Missing seller share for pair ID {$pair->id}";
    }
    
    if ($buyerShare && $sellerShare) {
        // Check for quantity issues
        if ($sellerShare->hold_quantity < $pair->share && !$pair->is_paid) {
            $issues[] = "Insufficient hold quantity in seller share {$sellerShare->ticket_no}: has " . 
                       number_format($sellerShare->hold_quantity) . ", needs " . number_format($pair->share);
        }
        
        // Check for negative shares
        if ($pair->share <= 0) {
            $issues[] = "Invalid share amount in pair {$pair->id}: {$pair->share}";
        }
        
        // Check for orphaned payments
        $payments = UserSharePayment::where('user_share_pair_id', $pair->id)
                                   ->where('status', 'paid')
                                   ->count();
        
        if ($payments > 0 && !$pair->is_paid) {
            $issues[] = "Pair {$pair->id} has paid payments but is_paid = 0";
        }
    }
}

if (empty($issues)) {
    echo "✅ No data integrity issues found!\n";
} else {
    echo "❌ Found " . count($issues) . " issue(s):\n\n";
    foreach ($issues as $index => $issue) {
        echo ($index + 1) . ". {$issue}\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "📋 SUMMARY AND RECOMMENDATIONS:\n";
echo str_repeat("=", 70) . "\n";

echo "1. ✅ Updated PaymentConfirmationService with robust error handling\n";
echo "2. ✅ Enhanced UserSharePaymentController with better validation\n";
echo "3. ✅ Added comprehensive logging for debugging future issues\n";
echo "4. ✅ Implemented specific error messages for different failure scenarios\n";
echo "5. ✅ Added concurrent access protection with model refresh\n\n";

if (!empty($issues)) {
    echo "🔧 IMMEDIATE FIXES NEEDED:\n";
    echo "The issues found above need to be resolved for this specific trade.\n";
    echo "Consider running data repair scripts or manual database updates.\n\n";
}

echo "💡 PREVENTION MEASURES IMPLEMENTED:\n";
echo "- Better validation before processing payments\n";
echo "- Atomic operations with proper transaction handling\n";
echo "- Graceful error handling that doesn't break the UI\n";
echo "- Enhanced logging for easier debugging\n";
echo "- Service-based architecture for better maintainability\n\n";

echo "✅ DIAGNOSTIC COMPLETED\n";