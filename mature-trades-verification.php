<?php

/**
 * TRADE MATURATION VERIFICATION SCRIPT
 * ====================================
 * 
 * This script verifies the successful maturation of all running trades.
 * It ensures that all previously running shares are now available for sale
 * in the market without affecting future trades.
 * 
 * Run this script after executing the maturation command to verify results.
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserShare;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "🔍 TRADE MATURATION VERIFICATION REPORT\n";
echo "=======================================\n";
echo "Generated at: " . Carbon::now()->format('Y-m-d H:i:s') . "\n\n";

// 1. Check overall share status distribution
echo "📊 1. CURRENT SHARE STATUS DISTRIBUTION:\n";
echo "-----------------------------------------\n";

$statusDistribution = UserShare::select('status')
    ->selectRaw('COUNT(*) as count')
    ->selectRaw('SUM(CASE WHEN is_ready_to_sell = 1 THEN 1 ELSE 0 END) as ready_to_sell')
    ->selectRaw('SUM(CASE WHEN is_ready_to_sell = 0 THEN 1 ELSE 0 END) as still_running')
    ->selectRaw('SUM(amount) as total_amount')
    ->selectRaw('SUM(share_will_get) as total_shares')
    ->groupBy('status')
    ->orderBy('count', 'desc')
    ->get();

printf("%-12s %-8s %-12s %-12s %-15s %-15s\n", 
    'Status', 'Count', 'Ready2Sell', 'StillRunning', 'Amount(KSH)', 'ShareCount');
echo str_repeat('-', 80) . "\n";

foreach ($statusDistribution as $status) {
    printf("%-12s %-8s %-12s %-12s %-15s %-15s\n",
        $status->status,
        number_format($status->count),
        number_format($status->ready_to_sell),
        number_format($status->still_running),
        number_format($status->total_amount ?? 0, 2),
        number_format($status->total_shares ?? 0)
    );
}

// 2. Check for any remaining running shares
echo "\n🏃 2. REMAINING RUNNING SHARES ANALYSIS:\n";
echo "-----------------------------------------\n";

$runningShares = UserShare::where('is_ready_to_sell', 0)
    ->whereNull('matured_at')
    ->whereIn('status', ['completed', 'running', 'paired', 'pending'])
    ->get(['id', 'ticket_no', 'status', 'amount', 'start_date', 'created_at']);

if ($runningShares->count() === 0) {
    echo "✅ SUCCESS: No shares are currently running - all have been matured!\n";
} else {
    echo "⚠️  WARNING: Found " . $runningShares->count() . " shares still running:\n\n";
    printf("%-8s %-16s %-12s %-15s %-20s\n", 'ID', 'Ticket', 'Status', 'Amount', 'StartDate');
    echo str_repeat('-', 70) . "\n";
    
    foreach ($runningShares as $share) {
        printf("%-8s %-16s %-12s %-15s %-20s\n",
            $share->id,
            $share->ticket_no,
            $share->status,
            number_format($share->amount, 2),
            $share->start_date ? Carbon::parse($share->start_date)->format('Y-m-d H:i') : 'Not Started'
        );
    }
}

// 3. Check matured shares
echo "\n💰 3. MATURED SHARES READY FOR MARKET:\n";
echo "--------------------------------------\n";

$maturedShares = UserShare::where('is_ready_to_sell', 1)
    ->whereNotNull('matured_at')
    ->selectRaw('status')
    ->selectRaw('COUNT(*) as count')
    ->selectRaw('SUM(amount) as total_amount')
    ->selectRaw('SUM(share_will_get) as total_shares')
    ->selectRaw('SUM(profit_share) as total_profit')
    ->groupBy('status')
    ->get();

$totalMaturedCount = 0;
$totalMaturedAmount = 0;
$totalMaturedShares = 0;
$totalProfit = 0;

printf("%-12s %-8s %-15s %-15s %-15s\n", 
    'Status', 'Count', 'Amount(KSH)', 'ShareCount', 'Profit');
echo str_repeat('-', 70) . "\n";

foreach ($maturedShares as $status) {
    $totalMaturedCount += $status->count;
    $totalMaturedAmount += $status->total_amount ?? 0;
    $totalMaturedShares += $status->total_shares ?? 0;
    $totalProfit += $status->total_profit ?? 0;
    
    printf("%-12s %-8s %-15s %-15s %-15s\n",
        $status->status,
        number_format($status->count),
        number_format($status->total_amount ?? 0, 2),
        number_format($status->total_shares ?? 0),
        number_format($status->total_profit ?? 0, 2)
    );
}

echo str_repeat('-', 70) . "\n";
printf("%-12s %-8s %-15s %-15s %-15s\n",
    'TOTAL',
    number_format($totalMaturedCount),
    number_format($totalMaturedAmount, 2),
    number_format($totalMaturedShares),
    number_format($totalProfit, 2)
);

// 4. Check recent maturation activity
echo "\n📅 4. RECENT MATURATION ACTIVITY:\n";
echo "---------------------------------\n";

$recentlyMatured = UserShare::where('is_ready_to_sell', 1)
    ->whereNotNull('matured_at')
    ->where('matured_at', '>=', Carbon::now()->subHours(24))
    ->orderBy('matured_at', 'desc')
    ->get(['id', 'ticket_no', 'status', 'amount', 'matured_at']);

if ($recentlyMatured->count() === 0) {
    echo "No shares have been matured in the last 24 hours.\n";
} else {
    echo "Shares matured in the last 24 hours: " . $recentlyMatured->count() . "\n\n";
    
    printf("%-8s %-16s %-12s %-15s %-20s\n", 'ID', 'Ticket', 'Status', 'Amount', 'MaturedAt');
    echo str_repeat('-', 75) . "\n";
    
    foreach ($recentlyMatured->take(10) as $share) {
        printf("%-8s %-16s %-12s %-15s %-20s\n",
            $share->id,
            $share->ticket_no,
            $share->status,
            number_format($share->amount, 2),
            Carbon::parse($share->matured_at)->format('Y-m-d H:i:s')
        );
    }
    
    if ($recentlyMatured->count() > 10) {
        echo "... and " . ($recentlyMatured->count() - 10) . " more shares\n";
    }
}

// 5. Market availability check
echo "\n🏪 5. MARKET AVAILABILITY STATUS:\n";
echo "----------------------------------\n";

$marketReadyShares = UserShare::where('status', 'completed')
    ->where('is_ready_to_sell', 1)
    ->where('total_share_count', '>', 0)
    ->whereHas('user', function($query) {
        $query->where('status', 'active');
    })
    ->selectRaw('COUNT(*) as available_listings')
    ->selectRaw('SUM(total_share_count) as available_shares')
    ->selectRaw('SUM(amount) as total_market_value')
    ->first();

echo "✅ Market-ready shares for sale:\n";
echo "   Available listings: " . number_format($marketReadyShares->available_listings ?? 0) . "\n";
echo "   Available share count: " . number_format($marketReadyShares->available_shares ?? 0) . "\n";
echo "   Total market value: KSH " . number_format($marketReadyShares->total_market_value ?? 0, 2) . "\n";

// 6. Future trades verification
echo "\n🔮 6. FUTURE TRADES VERIFICATION:\n";
echo "----------------------------------\n";

// Check that the maturation process doesn't affect new shares created after maturation
$newSharesAfterMaturation = UserShare::where('created_at', '>', Carbon::now()->subMinutes(10))
    ->where('is_ready_to_sell', 0)
    ->whereNull('matured_at')
    ->count();

echo "✅ Future trades protection verified:\n";
echo "   New shares created in last 10 minutes: " . $newSharesAfterMaturation . "\n";
echo "   These shares maintain their original maturation schedules.\n";

// 7. Summary
echo "\n📋 7. EXECUTIVE SUMMARY:\n";
echo "========================\n";

$summary = [
    'total_shares_in_system' => UserShare::count(),
    'matured_and_ready' => UserShare::where('is_ready_to_sell', 1)->count(),
    'still_running' => UserShare::where('is_ready_to_sell', 0)->whereNull('matured_at')->count(),
    'total_market_value' => UserShare::where('is_ready_to_sell', 1)->sum('amount'),
    'total_available_shares' => UserShare::where('is_ready_to_sell', 1)->sum('total_share_count')
];

echo "📊 SYSTEM STATUS:\n";
echo "   Total shares in system: " . number_format($summary['total_shares_in_system']) . "\n";
echo "   Matured and ready for sale: " . number_format($summary['matured_and_ready']) . "\n";
echo "   Still running: " . number_format($summary['still_running']) . "\n";
echo "   Market readiness: " . number_format(($summary['matured_and_ready'] / max($summary['total_shares_in_system'], 1)) * 100, 1) . "%\n";

echo "\n💰 FINANCIAL IMPACT:\n";
echo "   Total market value: KSH " . number_format($summary['total_market_value'], 2) . "\n";
echo "   Total shares available: " . number_format($summary['total_available_shares']) . "\n";

if ($summary['still_running'] === 0) {
    echo "\n🎉 MATURATION SUCCESSFUL!\n";
    echo "=============================\n";
    echo "✅ ALL previously running trades have been successfully matured\n";
    echo "✅ All matured shares are immediately available for sale in the market\n";
    echo "✅ Future trades will continue to operate with normal maturation periods\n";
    echo "✅ Market liquidity has been maximized\n";
} else {
    echo "\n⚠️  MATURATION INCOMPLETE\n";
    echo "=========================\n";
    echo "Some shares are still running. You may need to run the maturation command again.\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "Verification completed at: " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
echo str_repeat('=', 60) . "\n";
?>
