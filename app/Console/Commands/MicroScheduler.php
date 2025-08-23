<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class MicroScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:micro {--duration=60 : Duration to run in seconds} {--interval=1 : Interval between runs in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run scheduled commands at micro-second precision intervals';

    /**
     * Commands to run and their configurations
     *
     * @var array
     */
    protected $scheduledCommands = [
        'sharematured:cron' => [
            'interval' => 1, // seconds
            'lastRun' => 0,
            'enabled' => true
        ],
        'paymentfailedshare:cron' => [
            'interval' => 1, // seconds
            'lastRun' => 0,
            'enabled' => true
        ],
        'unblockTemporaryBlockedUsers:cron' => [
            'interval' => 1, // seconds
            'lastRun' => 0,
            'enabled' => true
        ],
        'update-shares' => [
            'interval' => 1, // seconds
            'lastRun' => 0,
            'enabled' => true
        ]
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $duration = (int) $this->option('duration');
        $defaultInterval = (float) $this->option('interval');
        
        $this->info("🚀 Starting micro-scheduler for {$duration} seconds with {$defaultInterval}s intervals...");
        $this->info("Commands to run:");
        
        foreach ($this->scheduledCommands as $command => $config) {
            if ($config['enabled']) {
                $this->info("  • {$command} (every {$config['interval']}s)");
            }
        }
        
        $this->newLine();
        
        $startTime = microtime(true);
        $endTime = $startTime + $duration;
        $runCount = 0;
        
        // Performance tracking
        $executionTimes = [];
        $errorCount = 0;
        $successCount = 0;
        
        while (microtime(true) < $endTime) {
            $currentTime = microtime(true);
            $timestamp = date('Y-m-d H:i:s');
            
            foreach ($this->scheduledCommands as $command => $config) {
                if (!$config['enabled']) {
                    continue;
                }
                
                // Check if it's time to run this command
                if (($currentTime - $config['lastRun']) >= $config['interval']) {
                    $commandStartTime = microtime(true);
                    
                    try {
                        $this->line("⏰ [{$timestamp}] Executing: <fg=cyan>{$command}</>");
                        
                        // Capture output
                        $exitCode = Artisan::call($command);
                        $output = Artisan::output();
                        
                        $executionTime = microtime(true) - $commandStartTime;
                        $executionTimes[] = $executionTime;
                        
                        if ($exitCode === 0) {
                            $this->line("✅ [{$timestamp}] <fg=green>SUCCESS</> {$command} (took " . round($executionTime * 1000, 2) . "ms)");
                            $successCount++;
                            
                            // Log output if not empty
                            if (!empty(trim($output))) {
                                $this->line("   📝 Output: " . trim($output));
                            }
                        } else {
                            $this->line("❌ [{$timestamp}] <fg=red>FAILED</> {$command} with exit code: {$exitCode}");
                            $errorCount++;
                        }
                        
                    } catch (\Exception $e) {
                        $this->error("💥 [{$timestamp}] ERROR in {$command}: " . $e->getMessage());
                        Log::error("MicroScheduler error running {$command}: " . $e->getMessage(), [
                            'exception' => $e,
                            'command' => $command,
                            'timestamp' => $timestamp
                        ]);
                        $errorCount++;
                    }
                    
                    // Update last run time
                    $this->scheduledCommands[$command]['lastRun'] = $currentTime;
                    $runCount++;
                }
            }
            
            // Sleep to maintain interval precision
            usleep(100000); // 100ms = 0.1 second
        }
        
        // Final statistics
        $totalTime = microtime(true) - $startTime;
        $avgExecutionTime = !empty($executionTimes) ? array_sum($executionTimes) / count($executionTimes) : 0;
        
        $this->newLine();
        $this->info('📊 <fg=yellow>EXECUTION SUMMARY</>');
        $this->info("═══════════════════════════════");
        $this->info("Duration: " . round($totalTime, 2) . " seconds");
        $this->info("Total executions: {$runCount}");
        $this->info("Successful: <fg=green>{$successCount}</>");
        $this->info("Failed: <fg=red>{$errorCount}</>");
        $this->info("Average execution time: " . round($avgExecutionTime * 1000, 2) . "ms");
        $this->info("Commands per second: " . round($runCount / $totalTime, 2));
        
        if ($errorCount === 0) {
            $this->info("🎉 <fg=green>All commands completed successfully!</>");
        } else {
            $this->warn("⚠️  {$errorCount} command(s) failed. Check logs for details.");
        }
        
        return Command::SUCCESS;
    }
}
