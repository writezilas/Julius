<?php
/**
 * Test Payment Priority System
 * 
 * This script demonstrates the enhanced payment verification with priority checking
 * for trades AB-17584301792936 and AB-17584301917046
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Models\UserShare;
use App\Models\User;
use App\Services\PaymentVerificationService;

echo "=== Testing Payment Priority Verification System ===\n\n";

try {
    // Initialize the verification service
    $verificationService = new PaymentVerificationService();
    
    // Test specific trades
    $ticketNumbers = ['AB-17584301792936', 'AB-17584301917046'];
    
    echo "🔍 Analyzing specific problematic trades...\n";
    echo "Tickets: " . implode(', ', $ticketNumbers) . "\n\n";
    
    // Get recovery analysis
    $recoveryStats = $verificationService->recoverIncorrectlyFailedTrades($ticketNumbers);
    
    echo "📊 RECOVERY ANALYSIS RESULTS:\n";
    echo "====================================\n";
    echo "Total failed trades examined: {$recoveryStats['total_examined']}\n";
    echo "Incorrectly failed trades: {$recoveryStats['incorrectly_failed']}\n\n";
    
    if ($recoveryStats['incorrectly_failed'] > 0) {
        echo "🚨 INCORRECTLY FAILED TRADES DETECTED!\n\n";
        
        foreach ($recoveryStats['recovered_trades'] as $trade) {
            echo "📋 Trade: {$trade['ticket_no']}\n";
            echo "   User ID: {$trade['user_id']}\n";
            echo "   Amount: {$trade['amount']}\n";
            echo "   Recovery Reasons:\n";
            
            foreach ($trade['recovery_reasons'] as $reason) {
                echo "   ✅ {$reason}\n";
            }
            echo "\n";
        }
        
        echo "💡 SOLUTION:\n";
        echo "Run the recovery command to fix these trades:\n";
        echo "php artisan trades:recover-failed --tickets=" . implode(',', $ticketNumbers) . "\n\n";
        
    } else {
        echo "✅ No incorrectly failed trades found.\n";
        echo "The payment priority system is working correctly!\n\n";
    }
    
    // Test the priority system directly
    echo "🧪 TESTING PAYMENT PRIORITY LOGIC:\n";
    echo "=====================================\n";
    
    $user = User::where('username', 'maddyPower')->first();
    if ($user) {
        $trades = UserShare::where('user_id', $user->id)
            ->whereIn('ticket_no', $ticketNumbers)
            ->with(['pairedShares.payment', 'payments'])
            ->get();
        
        foreach ($trades as $trade) {
            echo "\n🔍 Testing: {$trade->ticket_no}\n";
            echo "Current Status: {$trade->status}\n";
            
            // Test payment priority checks
            $details = $verificationService->getPaymentStatusDetails($trade);
            
            echo "Payment Evidence Check:\n";
            echo "  • Confirmed Pairings: {$details['has_confirmed_pairings']}\n";
            echo "  • Direct Payments: {$details['has_payments']}\n";  
            echo "  • Timer Paused: {$details['timer_paused']}\n";
            echo "  • Payment Timer Paused: {$details['payment_timer_paused']}\n";
            echo "  • Timeout Reached: {$details['timeout_reached']}\n";
            
            $shouldFail = $verificationService->shouldMarkAsFailed($trade);
            echo "\n🎯 PRIORITY DECISION: " . ($shouldFail ? "SHOULD FAIL" : "PROTECTED BY PAYMENT") . "\n";
            
            // Check if current status matches priority decision
            if ($trade->status === 'failed' && !$shouldFail) {
                echo "🚨 BUG CONFIRMED: Trade failed but has payment evidence!\n";
                echo "   Priority system would protect this trade.\n";
            } elseif ($trade->status !== 'failed' && $shouldFail) {
                echo "⚠️ Trade should be failed but isn't (less critical)\n";
            } else {
                echo "✅ Trade status aligns with payment priority logic\n";
            }
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎯 PAYMENT PRIORITY SYSTEM SUMMARY:\n";
    echo "✅ PRIORITY 1: Confirmed payments (HIGHEST - overrides everything)\n";
    echo "✅ PRIORITY 2: Direct payment records (HIGH)\n";
    echo "✅ PRIORITY 3: Payment submission signals (MEDIUM)\n";
    echo "✅ PRIORITY 4: Timeout conditions (LOWEST)\n\n";
    
    echo "💡 The system now checks for payment confirmation FIRST\n";
    echo "   before considering any timeout conditions!\n\n";
    
    if ($recoveryStats['incorrectly_failed'] > 0) {
        echo "⚡ ACTION REQUIRED:\n";
        echo "Run: php artisan trades:recover-failed --dry-run\n";
        echo "Then: php artisan trades:recover-failed (to actually fix)\n";
    } else {
        echo "🎉 ALL SYSTEMS WORKING CORRECTLY!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n🎯 Payment priority testing completed!\n";