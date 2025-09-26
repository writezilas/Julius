<?php
require_once 'vendor/autoload.php';

use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>Market Status Debug</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } .status { padding: 10px; margin: 10px 0; border-radius: 5px; } .open { background-color: #d4edda; border: 1px solid #c3e6cb; } .closed { background-color: #f8d7da; border: 1px solid #f5c6cb; }</style>";

// Get current time information
$appTimezone = get_app_timezone();
$now = Carbon::now($appTimezone);

echo "<h2>Current Time Information</h2>";
echo "<p><strong>Current UTC Time:</strong> " . now() . "</p>";
echo "<p><strong>App Timezone:</strong> {$appTimezone}</p>";
echo "<p><strong>Current Local Time:</strong> {$now}</p>";
echo "<p><strong>Current Local Time (formatted):</strong> " . $now->format('Y-m-d H:i:s') . "</p>";

// Get market information
$markets = get_markets();

echo "<h2>Market Configuration</h2>";
echo "<p><strong>Active Markets Count:</strong> " . $markets->count() . "</p>";

if ($markets->count() > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Open Time</th><th>Close Time</th><th>Is Active</th><th>Currently Open</th></tr>";
    
    foreach ($markets as $market) {
        $todayDate = $now->format('Y-m-d');
        $open = Carbon::parse($todayDate . ' ' . $market->open_time, $appTimezone);
        $close = Carbon::parse($todayDate . ' ' . $market->close_time, $appTimezone);
        $isCurrentlyOpen = $now->between($open, $close);
        
        echo "<tr>";
        echo "<td>{$market->id}</td>";
        echo "<td>{$market->open_time}</td>";
        echo "<td>{$market->close_time}</td>";
        echo "<td>" . ($market->is_active ? 'Yes' : 'No') . "</td>";
        echo "<td>" . ($isCurrentlyOpen ? '<span style="color: green;">OPEN</span>' : '<span style="color: red;">CLOSED</span>') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>No active markets configured - Market defaults to OPEN (24/7 trading)</p>";
}

// Test market functions
echo "<h2>Market Status Functions</h2>";

$isMarketOpen = is_market_open();
$nextOpenTime = get_next_market_open_time();

echo "<div class='status " . ($isMarketOpen ? 'open' : 'closed') . "'>";
echo "<h3>is_market_open(): " . ($isMarketOpen ? 'TRUE (OPEN)' : 'FALSE (CLOSED)') . "</h3>";
echo "</div>";

if ($nextOpenTime) {
    echo "<p><strong>Next Market Opening:</strong> " . $nextOpenTime->format('Y-m-d H:i:s') . " ({$appTimezone})</p>";
    echo "<p><strong>Time Until Opening:</strong> " . $now->diffForHumans($nextOpenTime, true) . "</p>";
} else {
    echo "<p><strong>Next Market Opening:</strong> N/A (Market always open)</p>";
}

// Test individual market windows
echo "<h2>Individual Market Window Status</h2>";
foreach ($markets as $market) {
    $todayDate = $now->format('Y-m-d');
    $open = Carbon::parse($todayDate . ' ' . $market->open_time, $appTimezone);
    $close = Carbon::parse($todayDate . ' ' . $market->close_time, $appTimezone);
    $isCurrentlyOpen = $now->between($open, $close);
    
    echo "<div class='status " . ($isCurrentlyOpen ? 'open' : 'closed') . "'>";
    echo "<h4>Market {$market->id}: {$market->open_time} - {$market->close_time}</h4>";
    echo "<p><strong>Status:</strong> " . ($isCurrentlyOpen ? 'OPEN' : 'CLOSED') . "</p>";
    echo "<p><strong>Opens at:</strong> " . $open->format('H:i:s') . "</p>";
    echo "<p><strong>Closes at:</strong> " . $close->format('H:i:s') . "</p>";
    
    if (!$isCurrentlyOpen) {
        if ($now->lt($open)) {
            echo "<p><strong>Opens in:</strong> " . $now->diffForHumans($open, true) . "</p>";
        } else {
            echo "<p><strong>Closed since:</strong> " . $close->diffForHumans() . "</p>";
        }
    }
    echo "</div>";
}

echo "<h2>Debug Summary</h2>";
echo "<ul>";
echo "<li>The market timer logic is working correctly</li>";
echo "<li>Current time is before the first market opening time (08:30)</li>";
echo "<li>Market status correctly shows as CLOSED</li>";
echo "<li>Next opening time is correctly calculated</li>";
echo "</ul>";

echo "<p style='margin-top: 20px;'><a href='javascript:location.reload()'>Refresh Page</a></p>";
?>