<?php
require_once 'vendor/autoload.php';

use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$appTimezone = get_app_timezone();
$now = Carbon::now($appTimezone);
$isMarketOpen = is_market_open();
$nextOpen = get_next_market_open_time();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Countdown Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-box { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .countdown { background: #e3f2fd; padding: 20px; text-align: center; border-radius: 10px; margin: 20px 0; }
        .time-display { font-size: 24px; font-weight: bold; color: #1976d2; }
    </style>
</head>
<body>
    <h1>Countdown Timer Debug</h1>
    
    <div class="debug-box">
        <h3>Server Information</h3>
        <p><strong>Server Time (UTC):</strong> <?= now()->utc()->format('Y-m-d H:i:s') ?> UTC</p>
        <p><strong>Server Time (Local):</strong> <?= $now->format('Y-m-d H:i:s') ?> (<?= $appTimezone ?>)</p>
        <p><strong>Market Status:</strong> <?= $isMarketOpen ? 'OPEN' : 'CLOSED' ?></p>
        <?php if ($nextOpen): ?>
        <p><strong>Next Open (Local):</strong> <?= $nextOpen->format('Y-m-d H:i:s') ?> (<?= $appTimezone ?>)</p>
        <p><strong>Next Open (UTC):</strong> <?= $nextOpen->utc()->format('Y-m-d H:i:s') ?> UTC</p>
        <p><strong>Old JS Format:</strong> <?= $nextOpen->utc() ?></p>
        <p><strong>New ISO Format:</strong> <?= $nextOpen->utc()->toISOString() ?></p>
        <p><strong>Time Until (seconds):</strong> <?= $nextOpen->utc()->timestamp - now()->utc()->timestamp ?></p>
        <?php endif; ?>
    </div>
    
    <div class="debug-box">
        <h3>Browser Information</h3>
        <p><strong>Browser Time:</strong> <span id="browser-time"></span></p>
        <p><strong>Browser Timezone:</strong> <span id="browser-timezone"></span></p>
        <p><strong>Browser UTC Offset:</strong> <span id="browser-offset"></span></p>
    </div>
    
    <?php if ($nextOpen): ?>
    <div class="countdown">
        <h3>Countdown Timer Test</h3>
        <div class="time-display" id="countdown-display">Loading...</div>
        <p>Target Time: <?= $nextOpen->utc()->format('Y-m-d H:i:s') ?> UTC</p>
        <p>Parsed JS Time: <span id="parsed-time"></span></p>
        <p>Distance: <span id="distance-display">...</span></p>
    </div>
    <?php endif; ?>
    
    <div class="debug-box">
        <h3>Debug Log</h3>
        <div id="debug-log" style="font-family: monospace; background: white; padding: 10px; border: 1px solid #ddd; height: 200px; overflow-y: auto;"></div>
    </div>
    
    <script>
        // Display browser info
        const now = new Date();
        document.getElementById('browser-time').textContent = now.toString();
        document.getElementById('browser-timezone').textContent = Intl.DateTimeFormat().resolvedOptions().timeZone;
        document.getElementById('browser-offset').textContent = now.getTimezoneOffset() + ' minutes';
        
        const log = (message) => {
            const timestamp = new Date().toISOString();
            const logDiv = document.getElementById('debug-log');
            logDiv.innerHTML += `[${timestamp}] ${message}<br>`;
            logDiv.scrollTop = logDiv.scrollHeight;
        };
        
        <?php if ($nextOpen): ?>
        // Test countdown with new ISO format
        const targetTime = '<?= $nextOpen->utc()->toISOString() ?>';
        log(`Target time from PHP (ISO): ${targetTime}`);
        log(`Old format was: <?= $nextOpen->utc() ?>`);
        
        // Parse the target time
        let countDownDate;
        try {
            countDownDate = new Date(targetTime).getTime();
            document.getElementById('parsed-time').textContent = new Date(countDownDate).toString();
            log(`Parsed countdown date: ${new Date(countDownDate)}`);
            log(`Countdown timestamp: ${countDownDate}`);
        } catch (error) {
            log(`Error parsing date: ${error}`);
            countDownDate = new Date().getTime() + 60000; // 1 minute from now as fallback
        }
        
        const updateCountdown = () => {
            const now = new Date().getTime();
            const distance = countDownDate - now;
            
            document.getElementById('distance-display').textContent = distance + ' ms';
            
            if (distance < 0) {
                document.getElementById('countdown-display').innerHTML = '⏰ TIME REACHED!';
                log(`Countdown reached zero! Distance: ${distance}ms`);
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('countdown-display').innerHTML = 
                `${days}d ${hours}h ${minutes}m ${seconds}s`;
        };
        
        // Update every second
        updateCountdown();
        const interval = setInterval(updateCountdown, 1000);
        
        log('Countdown timer started');
        <?php else: ?>
        log('No next market time available - market may be 24/7');
        <?php endif; ?>
    </script>
</body>
</html>