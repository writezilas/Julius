<?php

namespace App\Console\Commands;

use App\Services\PaymentFailureService;
use Illuminate\Console\Command;

class ManageSuspensions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suspensions:manage 
                            {--process-expired : Process and lift expired suspensions}
                            {--show-stats : Show suspension statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage user suspensions - lift expired suspensions and show statistics';

    /**
     * PaymentFailureService instance
     *
     * @var PaymentFailureService
     */
    protected $paymentFailureService;

    /**
     * Create a new command instance.
     *
     * @return void
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
        if ($this->option('process-expired')) {
            $this->processExpiredSuspensions();
        }
        
        if ($this->option('show-stats')) {
            $this->showSuspensionStatistics();
        }
        
        // If no options provided, run both
        if (!$this->option('process-expired') && !$this->option('show-stats')) {
            $this->processExpiredSuspensions();
            $this->showSuspensionStatistics();
        }

        return 0;
    }

    /**
     * Process expired suspensions
     */
    protected function processExpiredSuspensions()
    {
        $this->info('Processing expired suspensions...');
        
        try {
            $liftedCount = $this->paymentFailureService->processExpiredSuspensions();
            
            if ($liftedCount > 0) {
                $this->info("✓ Lifted {$liftedCount} expired suspension(s)");
            } else {
                $this->info("✓ No expired suspensions to lift");
            }
        } catch (\Exception $e) {
            $this->error("✗ Error processing expired suspensions: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Show suspension statistics
     */
    protected function showSuspensionStatistics()
    {
        $this->info('Fetching suspension statistics...');
        
        try {
            $stats = $this->paymentFailureService->getFailureStatistics();
            $suspendedUsers = $this->paymentFailureService->getSuspendedUsers();
            $approachingUsers = $this->paymentFailureService->getUsersApproachingSuspension();
            
            $this->newLine();
            $this->info('📊 Suspension Statistics');
            $this->line('─────────────────────────────────────────────');
            
            $this->table([
                'Metric',
                'Count'
            ], [
                ['Payment failures today', $stats['total_failures_today']],
                ['Users with failures', $stats['users_with_failures']],
                ['Users approaching suspension (2 failures)', $stats['users_approaching_suspension']],
                ['Currently suspended users', $stats['currently_suspended']],
                ['Suspensions lifted today', $stats['suspensions_lifted_today']],
            ]);

            if ($suspendedUsers->count() > 0) {
                $this->newLine();
                $this->warn("⚠️  Currently Suspended Users ({$suspendedUsers->count()}):");
                $suspensionData = [];
                foreach ($suspendedUsers as $user) {
                    $suspensionData[] = [
                        $user->username,
                        $user->email,
                        $user->suspension_until ? $user->suspension_until->format('Y-m-d H:i:s') : 'N/A',
                        $user->suspension_until ? $user->suspension_until->diffForHumans() : 'N/A'
                    ];
                }
                $this->table(['Username', 'Email', 'Suspended Until', 'Time Remaining'], $suspensionData);
            }

            if ($approachingUsers->count() > 0) {
                $this->newLine();
                $this->warn("⚡ Users Approaching Suspension ({$approachingUsers->count()}):");
                $approachingData = [];
                foreach ($approachingUsers as $failure) {
                    $approachingData[] = [
                        $failure->user->username,
                        $failure->user->email,
                        $failure->consecutive_failures,
                        $failure->last_failure_at ? $failure->last_failure_at->diffForHumans() : 'N/A'
                    ];
                }
                $this->table(['Username', 'Email', 'Failures', 'Last Failure'], $approachingData);
            }
            
        } catch (\Exception $e) {
            $this->error("✗ Error fetching statistics: " . $e->getMessage());
        }
    }
}
