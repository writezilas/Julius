<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PaymentFailureService;
use Illuminate\Support\Facades\Log;

class ManageUserSuspensions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suspension:manage 
                           {--lift-expired : Process and lift expired suspensions}
                           {--reset-levels : Reset suspension levels for users with good payment history}
                           {--stats : Show suspension statistics}
                           {--all : Run all suspension management tasks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage user suspensions: lift expired suspensions, reset levels, and show statistics';

    /**
     * PaymentFailureService instance
     *
     * @var PaymentFailureService
     */
    protected $paymentFailureService;

    /**
     * Create a new command instance.
     *
     * @param PaymentFailureService $paymentFailureService
     */
    public function __construct(PaymentFailureService $paymentFailureService)
    {
        parent::__construct();
        $this->paymentFailureService = $paymentFailureService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $startTime = now();
        
        $this->info("🔄 Starting suspension management at {$startTime->format('Y-m-d H:i:s')}");

        try {
            $liftExpired = $this->option('lift-expired') || $this->option('all');
            $resetLevels = $this->option('reset-levels') || $this->option('all');
            $showStats = $this->option('stats') || $this->option('all');

            $results = [
                'lifted_suspensions' => 0,
                'reset_levels' => 0,
                'stats' => null
            ];

            // Process expired suspensions
            if ($liftExpired) {
                $this->info("⏰ Processing expired suspensions...");
                $results['lifted_suspensions'] = $this->paymentFailureService->processExpiredSuspensions();
                
                if ($results['lifted_suspensions'] > 0) {
                    $this->info("✅ Lifted {$results['lifted_suspensions']} expired suspensions");
                } else {
                    $this->info("ℹ️  No expired suspensions found");
                }
            }

            // Process suspension level resets
            if ($resetLevels) {
                $this->info("📉 Processing suspension level resets...");
                $results['reset_levels'] = $this->paymentFailureService->processSuspensionLevelResets();
                
                if ($results['reset_levels'] > 0) {
                    $this->info("✅ Reset suspension levels for {$results['reset_levels']} users");
                } else {
                    $this->info("ℹ️  No suspension levels to reset");
                }
            }

            // Show statistics
            if ($showStats) {
                $this->info("📊 Gathering suspension statistics...");
                $results['stats'] = $this->paymentFailureService->getFailureStatistics();
                $this->displayStatistics($results['stats']);
            }

            $endTime = now();
            $duration = $startTime->diffInSeconds($endTime);
            
            $this->info("✅ Suspension management completed in {$duration}s");

            // Log the results
            Log::info('Suspension management completed', [
                'duration' => $duration,
                'lifted_suspensions' => $results['lifted_suspensions'],
                'reset_levels' => $results['reset_levels'],
                'timestamp' => $endTime->toDateTimeString()
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error during suspension management: " . $e->getMessage());
            Log::error('Suspension management error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return self::FAILURE;
        }
    }

    /**
     * Display suspension statistics in a formatted table
     *
     * @param array $stats
     * @return void
     */
    private function displayStatistics(array $stats)
    {
        $this->info("\n📈 Suspension Statistics:");
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total failures today', $stats['total_failures_today']],
                ['Users with failures', $stats['users_with_failures']],
                ['Users approaching suspension (2 failures)', $stats['users_approaching_suspension']],
                ['Currently suspended users', $stats['currently_suspended']],
                ['Suspensions lifted today', $stats['suspensions_lifted_today']],
                ['Level 1 suspensions (6h)', $stats['level_1_suspensions']],
                ['Level 2 suspensions (24h)', $stats['level_2_suspensions']],
                ['Level 3+ suspensions (72h)', $stats['level_3_plus_suspensions']],
            ]
        );

        // Show approaching suspension users
        $approachingUsers = $this->paymentFailureService->getUsersApproachingSuspension();
        if ($approachingUsers->count() > 0) {
            $this->warn("\n⚠️  Users approaching suspension:");
            $userData = $approachingUsers->map(function($failure) {
                return [
                    'User' => $failure->user->username ?? 'Unknown',
                    'Failures' => $failure->consecutive_failures,
                    'Last Failure' => $failure->last_failure_at ? $failure->last_failure_at->format('Y-m-d H:i:s') : 'Never'
                ];
            })->toArray();
            
            $this->table(['User', 'Failures', 'Last Failure'], $userData);
        }

        // Show currently suspended users
        $suspendedUsers = $this->paymentFailureService->getSuspendedUsers();
        if ($suspendedUsers->count() > 0) {
            $this->warn("\n🚫 Currently suspended users:");
            $suspendedData = $suspendedUsers->map(function($user) {
                $paymentFailure = $user->paymentFailures->first();
                return [
                    'User' => $user->username,
                    'Level' => $paymentFailure ? $paymentFailure->suspension_level : 'Unknown',
                    'Duration' => $paymentFailure ? $paymentFailure->suspension_duration_hours . 'h' : 'Unknown',
                    'Expires' => $user->suspension_until ? $user->suspension_until->format('Y-m-d H:i:s') : 'Unknown'
                ];
            })->toArray();
            
            $this->table(['User', 'Level', 'Duration', 'Expires'], $suspendedData);
        }
    }
}
