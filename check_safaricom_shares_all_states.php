<?php

/**
 * Check All Safaricom Shares in Different States
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\UserShare;
use App\Models\Trade;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Safaricom Shares Complete Analysis ===\n\n";

try {
    $safaricom = Trade::find(1);
    if (!$safaricom) {
        echo "❌ Safaricom trade not found\n";
        exit(1);
    }
    
    echo "🔍 Analyzing all Safaricom shares ({$safaricom->name}, ID: {$safaricom->id}):\n\n";
    
    // Get ALL Safaricom shares regardless of status
    $allSafaricomShares = UserShare::where('trade_id', 1)
        ->with(['user'])
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "Found {$allSafaricomShares->count()} total Safaricom shares:\n\n";
    
    // Group by status
    echo "📊 By Status:\n";
    $byStatus = $allSafaricomShares->groupBy('status');
    foreach ($byStatus as $status => $shares) {
        $total = $shares->sum('total_share_count');
        $count = $shares->count();
        echo "- {$status}: {$total} shares ({$count} records)\n";
    }
    
    echo "\n📊 By Get From:\n";
    $byGetFrom = $allSafaricomShares->groupBy('get_from');
    foreach ($byGetFrom as $getFrom => $shares) {
        $total = $shares->sum('total_share_count');
        $count = $shares->count();
        echo "- {$getFrom}: {$total} shares ({$count} records)\n";
    }
    
    echo "\n📊 By Ready to Sell:\n";
    $byReadyToSell = $allSafaricomShares->groupBy('is_ready_to_sell');
    foreach ($byReadyToSell as $ready => $shares) {
        $total = $shares->sum('total_share_count');
        $count = $shares->count();
        $readyText = $ready ? 'Ready' : 'Not Ready';
        echo "- {$readyText}: {$total} shares ({$count} records)\n";
    }
    
    echo "\n📋 Detailed Breakdown:\n";
    echo "┌─────┬──────────────┬─────────────┬────────────┬─────────────┬──────────────┬─────────────────┬─────────┬──────────────┐\n";
    echo "│ ID  │ Ticket       │ User        │ Status     │ Get From   │ Start Date   │ Total Count     │ Ready   │ User Status  │\n";
    echo "├─────┼──────────────┼─────────────┼────────────┼─────────────┼──────────────┼─────────────────┼─────────┼──────────────┤\n";
    
    foreach ($allSafaricomShares as $share) {
        $userName = $share->user ? $share->user->username : 'N/A';
        $userStatus = $share->user ? $share->user->status : 'N/A';
        $startDate = $share->start_date ? \Carbon\Carbon::parse($share->start_date)->format('M d H:i') : 'None';
        $ready = $share->is_ready_to_sell ? 'Yes' : 'No';
        
        printf("│ %-3d │ %-12s │ %-11s │ %-10s │ %-11s │ %-12s │ %-15s │ %-7s │ %-12s │\n", 
            $share->id,
            substr($share->ticket_no ?: 'N/A', 0, 12),
            substr($userName, 0, 11),
            substr($share->status, 0, 10),
            substr($share->get_from ?: 'N/A', 0, 11),
            substr($startDate, 0, 12),
            $share->total_share_count ?: 0,
            $ready,
            substr($userStatus, 0, 12)
        );
    }
    
    echo "└─────┴──────────────┴─────────────┴────────────┴─────────────┴──────────────┴─────────────────┴─────────┴──────────────┘\n";
    
    // Analyze potential issues
    echo "\n🔍 Potential Issues Analysis:\n\n";
    
    // Check for shares that could be ready but aren't
    $completedNotReady = $allSafaricomShares->where('status', 'completed')
        ->where('is_ready_to_sell', 0);
    
    if ($completedNotReady->count() > 0) {
        echo "⚠️  Found {$completedNotReady->count()} completed shares not ready to sell:\n";
        foreach ($completedNotReady as $share) {
            $userName = $share->user ? $share->user->username : 'N/A';
            $startDate = $share->start_date ? \Carbon\Carbon::parse($share->start_date)->format('Y-m-d H:i:s') : 'None';
            $timeAgo = $share->start_date ? \Carbon\Carbon::parse($share->start_date)->diffForHumans() : 'No start date';
            echo "  - ID {$share->id} ({$userName}): Started {$timeAgo}, Start date: {$startDate}\n";
        }
        echo "\n";
    }
    
    // Check for inactive users with shares
    $inactiveUserShares = $allSafaricomShares->filter(function($share) {
        return $share->user && !in_array($share->user->status, ['active', 'pending', 'fine']);
    });
    
    if ($inactiveUserShares->count() > 0) {
        echo "⚠️  Found {$inactiveUserShares->count()} shares from inactive users:\n";
        foreach ($inactiveUserShares as $share) {
            $userName = $share->user ? $share->user->username : 'N/A';
            $userStatus = $share->user ? $share->user->status : 'N/A';
            echo "  - ID {$share->id} ({$userName}, status: {$userStatus}): {$share->total_share_count} shares\n";
        }
        echo "\n";
    }
    
    // Check for shares with zero count
    $zeroShares = $allSafaricomShares->where('total_share_count', 0);
    if ($zeroShares->count() > 0) {
        echo "ℹ️  Found {$zeroShares->count()} shares with zero count (already sold out)\n\n";
    }
    
    // Summary of what should be available
    echo "✅ Summary - What should be available for trading:\n";
    $shouldBeAvailable = $allSafaricomShares->filter(function($share) {
        return $share->status === 'completed' && 
               $share->is_ready_to_sell == 1 && 
               $share->total_share_count > 0 &&
               $share->user && 
               in_array($share->user->status, ['active', 'pending', 'fine']);
    });
    
    $totalAvailable = $shouldBeAvailable->sum('total_share_count');
    echo "- Total available: {$totalAvailable} shares\n";
    echo "- From {$shouldBeAvailable->count()} records\n";
    
    if ($shouldBeAvailable->count() > 0) {
        echo "- Breakdown:\n";
        $byType = $shouldBeAvailable->groupBy('get_from');
        foreach ($byType as $type => $shares) {
            $total = $shares->sum('total_share_count');
            echo "  - {$type}: {$total} shares\n";
        }
    }
    
    echo "\n=== Analysis Complete ===\n";
    
} catch (Exception $e) {
    echo "❌ Analysis failed: " . $e->getMessage() . "\n";
    exit(1);
}