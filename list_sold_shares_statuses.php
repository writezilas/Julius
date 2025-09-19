<?php

/**
 * LIST ALL STATUS TYPES FOR SOLD SHARES PAGE
 * ==========================================
 * 
 * This script lists all possible statuses that can appear in the sold shares page,
 * along with their descriptions, CSS classes, and conditions.
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use App\Services\ShareStatusService;
use Illuminate\Support\Facades\DB;

echo "🔍 SOLD SHARES PAGE - ALL POSSIBLE STATUS TYPES\n";
echo str_repeat("=", 60) . "\n\n";

// 1. Database-level statuses
echo "📊 1. DATABASE-LEVEL STATUSES (enum values):\n";
echo str_repeat("-", 40) . "\n";

$column = DB::select('SHOW COLUMNS FROM user_shares WHERE Field = "status"')[0] ?? null;
if ($column) {
    // Parse enum values
    preg_match_all("/'([^']+)'/", $column->Type, $matches);
    $enumValues = $matches[1] ?? [];
    
    foreach ($enumValues as $status) {
        $description = match($status) {
            'pending' => 'Initial state, waiting for pairing',
            'pairing' => 'System is finding pairs for the share',
            'paired' => 'Paired with buyers/sellers',
            'completed' => 'Transaction completed, may be in maturation',
            'failed' => 'Transaction failed',
            'sold' => 'Share has been completely sold'
        };
        echo "   • {$status}: {$description}\n";
    }
}

echo "\n";

// 2. ShareStatusService statuses (what users see)
echo "🎯 2. USER-VISIBLE STATUSES (ShareStatusService):\n";
echo str_repeat("-", 40) . "\n";

// Initialize the service
$shareStatusService = new ShareStatusService();

// Define all possible status scenarios with their conditions
$statusScenarios = [
    [
        'status' => 'Sold',
        'class' => 'bg-dark',
        'description' => 'Share has been completely sold',
        'conditions' => [
            'Database status = "sold"',
            'OR total_share_count = 0 AND hold_quantity = 0 AND sold_quantity > 0'
        ]
    ],
    [
        'status' => 'Failed',
        'class' => 'bg-danger',
        'description' => 'Share transaction failed',
        'conditions' => [
            'Database status = "failed"'
        ]
    ],
    [
        'status' => 'Payment Submitted',
        'class' => 'bg-info',
        'description' => 'Payment submitted, awaiting seller confirmation',
        'conditions' => [
            'Status = "paired"',
            'Share get_from = "purchase" (buyer perspective)',
            'Has payments awaiting confirmation'
        ]
    ],
    [
        'status' => 'Confirm Payment',
        'class' => 'bg-warning',
        'description' => 'Payment received - confirm to complete transaction',
        'conditions' => [
            'Status = "paired"',
            'Share get_from != "purchase" (seller perspective)',
            'Has payments awaiting confirmation'
        ]
    ],
    [
        'status' => 'Paired',
        'class' => 'bg-warning',
        'description' => 'Share is paired and waiting for payments',
        'conditions' => [
            'Status = "paired"',
            'No payments awaiting confirmation'
        ]
    ],
    [
        'status' => 'Completed',
        'class' => 'bg-success',
        'description' => 'Share transaction completed successfully',
        'conditions' => [
            'Context = "bought"',
            'Share is matured OR has real-time maturity',
            'get_from = "allocated-by-admin", "refferal-bonus", or "purchase"'
        ]
    ],
    [
        'status' => 'Available',
        'class' => 'bg-info',
        'description' => 'Available for purchase in market',
        'conditions' => [
            'Share is matured',
            'No active pairings OR all payments failed',
            'Context = "sold"'
        ]
    ],
    [
        'status' => 'Mixed Payments',
        'class' => 'bg-primary',
        'description' => 'Payments in various stages',
        'conditions' => [
            'Share is matured',
            'Has multiple payment states: paid, unpaid, awaiting confirmation'
        ]
    ],
    [
        'status' => 'Partially Paid',
        'class' => 'bg-primary',
        'description' => 'Some buyers have paid, others pending',
        'conditions' => [
            'Share is matured',
            'Has both paid and unpaid pairs'
        ]
    ],
    [
        'status' => 'Partially Sold',
        'class' => 'bg-success',
        'description' => 'Some shares sold, others available',
        'conditions' => [
            'Share is matured',
            'All active pairs are paid',
            'total_share_count > 0 OR hold_quantity > 0'
        ]
    ],
    [
        'status' => 'Waiting for Payment',
        'class' => 'bg-warning',
        'description' => 'Buyers paired but payments pending',
        'conditions' => [
            'Share is matured',
            'Has unpaid pairs only'
        ]
    ],
    [
        'status' => 'Running',
        'class' => 'bg-info',
        'description' => 'Share is active and running in maturation period',
        'conditions' => [
            'Status = "completed"',
            'is_ready_to_sell = 0',
            'Context = "sold" or auto-detected as sold'
        ]
    ],
    [
        'status' => 'Processing',
        'class' => 'bg-secondary',
        'description' => 'Share is being processed (fallback)',
        'conditions' => [
            'Mixed state that doesn\'t match other criteria'
        ]
    ],
    [
        'status' => 'Pending',
        'class' => 'bg-secondary',
        'description' => 'Share is pending processing (fallback)',
        'conditions' => [
            'Default when no other conditions match'
        ]
    ]
];

foreach ($statusScenarios as $scenario) {
    echo "   🏷️  {$scenario['status']}\n";
    echo "       Class: {$scenario['class']}\n";
    echo "       Description: {$scenario['description']}\n";
    echo "       Conditions:\n";
    foreach ($scenario['conditions'] as $condition) {
        echo "         - {$condition}\n";
    }
    echo "\n";
}

// 3. Time remaining states
echo "⏰ 3. TIME REMAINING STATES:\n";
echo str_repeat("-", 40) . "\n";

$timeStates = [
    [
        'text' => 'Share Matured',
        'class' => 'countdown-timer matured',
        'color' => '#27ae60',
        'condition' => 'is_ready_to_sell = 1'
    ],
    [
        'text' => 'Payment received - confirm to complete',
        'class' => 'countdown-timer payment-received',
        'color' => '#17a2b8',
        'condition' => 'Status = "paired" with awaiting confirmation'
    ],
    [
        'text' => 'Waiting for payments',
        'class' => 'countdown-timer waiting',
        'color' => '#f39c12',
        'condition' => 'Status = "paired" without awaiting confirmation'
    ],
    [
        'text' => 'Waiting for pairing',
        'class' => 'countdown-timer waiting',
        'color' => '#3498db',
        'condition' => 'Status = "pending"'
    ],
    [
        'text' => 'Finding pairs',
        'class' => 'countdown-timer waiting',
        'color' => '#3498db',
        'condition' => 'Status = "pairing"'
    ],
    [
        'text' => 'Transaction failed',
        'class' => 'countdown-timer failed',
        'color' => '#e74c3c',
        'condition' => 'Status = "failed"'
    ],
    [
        'text' => 'timer-active (JavaScript countdown)',
        'class' => 'countdown-timer',
        'color' => '#3498db',
        'condition' => 'Status = "completed" with start_date and period'
    ],
    [
        'text' => 'Status unknown',
        'class' => 'countdown-timer',
        'color' => '#95a5a6',
        'condition' => 'Fallback for unknown states'
    ]
];

foreach ($timeStates as $state) {
    echo "   ⏰ {$state['text']}\n";
    echo "      Class: {$state['class']}\n";
    echo "      Color: {$state['color']}\n";
    echo "      Condition: {$state['condition']}\n\n";
}

// 4. Current status distribution in the system
echo "📈 4. CURRENT STATUS DISTRIBUTION IN DATABASE:\n";
echo str_repeat("-", 40) . "\n";

$statusDistribution = DB::table('user_shares')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->groupBy('status')
    ->orderBy('count', 'desc')
    ->get();

foreach ($statusDistribution as $status) {
    echo "   • {$status->status}: {$status->count} shares\n";
}

echo "\n";

// 5. Context-specific behavior
echo "🎯 5. CONTEXT-SPECIFIC STATUS BEHAVIOR:\n";
echo str_repeat("-", 40) . "\n";

echo "   📋 BOUGHT SHARES CONTEXT ('bought'):\n";
echo "      • Admin-allocated shares: Show as 'Completed'\n";
echo "      • Referral bonus shares: Show as 'Completed'\n";
echo "      • Purchased shares: Show as 'Completed' (buyer perspective)\n";
echo "      • Focus: Transaction success from buyer's viewpoint\n\n";

echo "   💰 SOLD SHARES CONTEXT ('sold'):\n";
echo "      • Shows maturation and selling progress\n";
echo "      • Displays pairing and payment status\n";
echo "      • Includes countdown timers for running shares\n";
echo "      • Focus: Selling progress and revenue generation\n\n";

// 6. Status flow diagram
echo "🔄 6. TYPICAL STATUS FLOW:\n";
echo str_repeat("-", 40) . "\n";

echo "   📈 BUYING FLOW:\n";
echo "      pending → pairing → paired → completed\n";
echo "                                      ↓\n";
echo "                               (maturation period)\n";
echo "                                      ↓\n";
echo "                               is_ready_to_sell = 1\n\n";

echo "   💱 SELLING FLOW:\n";
echo "      completed (ready_to_sell=1) → paired → sold\n";
echo "                    ↓                ↑\n";
echo "              'Available'     'Waiting for Payment'\n";
echo "                    ↓                ↑\n";
echo "              'Partially Sold' → 'Sold'\n\n";

// 7. Examples with real data
echo "📋 7. EXAMPLES FROM CURRENT DATABASE:\n";
echo str_repeat("-", 40) . "\n";

$sampleShares = UserShare::with('trade')
    ->limit(5)
    ->get();

if ($sampleShares->count() > 0) {
    foreach ($sampleShares as $share) {
        $statusInfo = $shareStatusService->getShareStatus($share, 'sold');
        $timeInfo = $shareStatusService->getTimeRemaining($share);
        
        echo "   Share ID {$share->id} ({$share->ticket_no}):\n";
        echo "      Database Status: {$share->status}\n";
        echo "      User-Visible Status: {$statusInfo['status']} ({$statusInfo['class']})\n";
        echo "      Time Display: {$timeInfo['text']}\n";
        echo "      Ready to Sell: " . ($share->is_ready_to_sell ? 'Yes' : 'No') . "\n";
        echo "      Get From: {$share->get_from}\n\n";
    }
} else {
    echo "   No shares found in database for examples.\n\n";
}

echo str_repeat("=", 60) . "\n";
echo "✅ SUMMARY:\n";
echo "   • Database has " . count($enumValues ?? []) . " enum status values\n";
echo "   • ShareStatusService provides " . count($statusScenarios) . " user-visible statuses\n";
echo "   • Time display has " . count($timeStates) . " possible states\n";
echo "   • Status behavior changes based on context (bought vs sold)\n";
echo "   • Real-time maturity checking provides dynamic status updates\n";
echo str_repeat("=", 60) . "\n";

echo "\n🔗 For detailed implementation, check:\n";
echo "   • app/Services/ShareStatusService.php\n";
echo "   • resources/views/user-panel/sold-shares.blade.php\n";
echo "   • app/Http/Controllers/HomeController.php (soldShares method)\n";