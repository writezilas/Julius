<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SecondLevelScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:second-level {--duration=60 : Duration to run in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run scheduled commands every second for a specified duration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $duration = (int) $this->option('duration');
        $this->info("Starting second-level scheduler for {$duration} seconds...");
        
        $startTime = time();
        $endTime = $startTime + $duration;
        
        $commandsSchedule = [
            'sharematured:cron' => ['frequency' => 1, 'lastRun' => 0], // Every second
            'paymentfailedshare:cron' => ['frequency' => 1, 'lastRun' => 0], // Every second
            'unblockTemporaryBlockedUsers:cron' => ['frequency' => 1, 'lastRun' => 0], // Every second
            'update-shares' => ['frequency' => 1, 'lastRun' => 0], // Every second
        ];
        
        while (time() < $endTime) {
            $currentTime = time();
            
            foreach ($commandsSchedule as $command => $schedule) {
                // Check if it's time to run this command
                if (($currentTime - $schedule['lastRun']) >= $schedule['frequency']) {
                    try {
                        $this->info("[" . date('Y-m-d H:i:s') . "] Running: {$command}");
                        
                        // Run the command
                        $exitCode = Artisan::call($command);
                        
                        if ($exitCode === 0) {
                            $this->info("[" . date('Y-m-d H:i:s') . "] ✅ {$command} completed successfully");
                        } else {
                            $this->error("[" . date('Y-m-d H:i:s') . "] ❌ {$command} failed with exit code: {$exitCode}");
                        }
                        
                        // Update last run time
                        $commandsSchedule[$command]['lastRun'] = $currentTime;
                        
                    } catch (\Exception $e) {
                        $this->error("[" . date('Y-m-d H:i:s') . "] ❌ Error running {$command}: " . $e->getMessage());
                        Log::error("SecondLevelScheduler error running {$command}: " . $e->getMessage());
                    }
                }
            }
            
            // Sleep for 1 second before next iteration
            sleep(1);
        }
        
        $this->info("Second-level scheduler completed after {$duration} seconds.");
        return Command::SUCCESS;
    }
}
