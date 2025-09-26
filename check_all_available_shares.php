<?php

/**
 * Check All Available Shares Across All Trades
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\UserShare;
use App\Models\Trade;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== All Available Shares Analysis ===\n\n";

try {
    $trades = Trade::where('status', '1')->get();
    
    echo "Found {$trades->count()} active trades:\n\n";
    
    foreach ($trades as $trade) {
        echo "🔍 {$trade->name} (ID: {$trade->id}):\n";
        
        $availableShares = checkAvailableSharePerTrade($trade->id);
        echo "- Available shares: {$availableShares}\n";
        
        // Breakdown by type
        $breakdown = UserShare::where('trade_id', $trade->id)
            ->where('status', 'completed')
            ->where('is_ready_to_sell', 1)
            ->where('total_share_count', '>', 0)
            ->whereHas('user', function ($query) {
                $query->whereIn('status', ['active', 'pending', 'fine']);
            })
            ->selectRaw('get_from, COUNT(*) as count, SUM(total_share_count) as total_shares')
            ->groupBy('get_from')
            ->get();
        
        if ($breakdown->count() > 0) {
            echo "- Breakdown by type:\n";
            foreach ($breakdown as $item) {
                echo "  - {$item->get_from}: {$item->total_shares} shares ({$item->count} records)\n";
            }
        } else {
            echo "- No shares available\n";
        }
        
        echo "\n";
    }
    
    // Summary of all user shares ready to sell
    echo "📊 Overall Summary:\n";
    
    $allReadyShares = UserShare::where('status', 'completed')
        ->where('is_ready_to_sell', 1)
        ->where('total_share_count', '>', 0)
        ->whereHas('user', function ($query) {
            $query->whereIn('status', ['active', 'pending', 'fine']);
        })
        ->with(['user', 'trade'])
        ->get();
    
    echo "Total shares ready to sell: {$allReadyShares->sum('total_share_count')}\n";
    echo "From {$allReadyShares->count()} share records\n\n";
    
    echo "By user:\n";
    $byUser = $allReadyShares->groupBy('user.username');
    foreach ($byUser as $username => $shares) {
        $total = $shares->sum('total_share_count');
        $count = $shares->count();
        echo "- {$username}: {$total} shares ({$count} records)\n";
    }
    
    echo "\nBy trade:\n";
    $byTrade = $allReadyShares->groupBy('trade.name');
    foreach ($byTrade as $tradeName => $shares) {
        $total = $shares->sum('total_share_count');
        $count = $shares->count();
        echo "- {$tradeName}: {$total} shares ({$count} records)\n";
    }
    
    echo "\nBy type:\n";
    $byType = $allReadyShares->groupBy('get_from');
    foreach ($byType as $type => $shares) {
        $total = $shares->sum('total_share_count');
        $count = $shares->count();
        echo "- {$type}: {$total} shares ({$count} records)\n";
    }
    
    echo "\n=== Analysis Complete ===\n";
    
} catch (Exception $e) {
    echo "❌ Analysis failed: " . $e->getMessage() . "\n";
    exit(1);
}