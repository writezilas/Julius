<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserPaymentFailure;
use App\Services\PaymentFailureService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FixDannyPaymentFailures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:danny-payment-failures 
                           {--simulate : Simulate the failed trades without actually applying suspension}
                           {--reset-only : Only reset Danny\'s current state to correct values}
                           {--test-fix : Test that the fix is working by simulating new failures}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix Danny\'s missing payment failure tracking from his second set of 3 consecutive failed trades';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Starting Danny payment failure fix...');
        
        // Find Danny
        $danny = User::where('username', 'Danny')->first();
        if (!$danny) {
            $this->error('❌ Danny not found in the database');
            return self::FAILURE;
        }
        
        $this->info("✅ Found Danny - User ID: {$danny->id}, Username: {$danny->username}");
        
        $paymentFailureService = new PaymentFailureService();
        
        if ($this->option('reset-only')) {
            return $this->resetDannyState($danny);
        }
        
        if ($this->option('test-fix')) {
            return $this->testFix($danny, $paymentFailureService);
        }
        
        return $this->fixDannyFailures($danny, $paymentFailureService);
    }
    
    /**
     * Reset Danny's state to the correct values
     */
    private function resetDannyState(User $danny): int
    {
        $this->info('🔄 Resetting Danny\'s payment failure state...');
        
        try {
            DB::beginTransaction();
            
            $paymentFailure = $danny->getCurrentPaymentFailure();
            $this->info("Current state - Consecutive failures: {$paymentFailure->consecutive_failures}, Level: {$paymentFailure->suspension_level}");
            
            // Danny should have 3 consecutive failures from his second round
            $paymentFailure->update([
                'consecutive_failures' => 3,
                'last_failure_at' => now(),
                'failure_reason' => 'Corrected - 3 missed payments from trades AB-17565619086930, AB-17565639392798, AB-17565639815064',
                'suspended_at' => null, // Clear suspended_at so he can be suspended again
                'suspension_lifted_at' => null // Clear lifted status
            ]);
            
            // Reset user status to active so suspension can be triggered
            $danny->update([
                'status' => 'active',
                'suspension_until' => null,
                'suspension_reason' => null
            ]);
            
            DB::commit();
            
            $paymentFailure->refresh();
            $this->info("✅ Reset complete - Consecutive failures: {$paymentFailure->consecutive_failures}, Level: {$paymentFailure->suspension_level}");
            $this->info("Danny should now be suspended on his next failure or when shouldSuspend() is called");
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error resetting Danny\'s state: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
    
    /**
     * Test that the fix is working properly
     */
    private function testFix(User $danny, PaymentFailureService $paymentFailureService): int
    {
        $this->info('🧪 Testing that the fix works...');
        
        $paymentFailure = $danny->getCurrentPaymentFailure();
        $initialFailures = $paymentFailure->consecutive_failures;
        $initialLevel = $paymentFailure->suspension_level;
        
        $this->info("Initial state - Failures: {$initialFailures}, Level: {$initialLevel}");
        
        // Simulate a single payment failure
        $this->info('Simulating a payment failure...');
        
        $result = $paymentFailureService->handlePaymentFailure(
            $danny->id, 
            'Test failure - validating fix works'
        );
        
        $this->info('Result: ' . json_encode($result, JSON_PRETTY_PRINT));
        
        if ($result['suspended'] && $result['consecutive_failures'] >= 3) {
            $this->info("✅ FIX VALIDATION SUCCESSFUL!");
            $this->info("- User was suspended after reaching {$result['consecutive_failures']} failures");
            $this->info("- Suspension level: {$result['suspension_level']}");
            $this->info("- Duration: {$result['suspension_duration_hours']} hours");
            
            return self::SUCCESS;
        } else {
            $this->warn("⚠️ Test result may need review:");
            $this->info("- Consecutive failures: {$result['consecutive_failures']}");
            $this->info("- Suspended: " . ($result['suspended'] ? 'YES' : 'NO'));
            
            if ($result['consecutive_failures'] < 3) {
                $this->info("This is expected if Danny had less than 2 failures before the test");
                return self::SUCCESS;
            }
            
            return self::FAILURE;
        }
    }
    
    /**
     * Fix Danny's missing payment failures by simulating them
     */
    private function fixDannyFailures(User $danny, PaymentFailureService $paymentFailureService): int
    {
        $this->info('🔧 Fixing Danny\'s missing payment failure tracking...');
        
        $paymentFailure = $danny->getCurrentPaymentFailure();
        $this->info("Current state - Consecutive failures: {$paymentFailure->consecutive_failures}, Level: {$paymentFailure->suspension_level}");
        
        // The three trades that should have triggered suspension
        $failedTrades = [
            'AB-17565619086930',
            'AB-17565639392798', 
            'AB-17565639815064'
        ];
        
        $simulate = $this->option('simulate');
        
        if (!$simulate && !$this->confirm('This will apply the missing payment failures to Danny. Continue?')) {
            $this->info('Operation cancelled.');
            return self::SUCCESS;
        }
        
        try {
            DB::beginTransaction();
            
            // First, clear any existing suspension state so we can re-trigger
            if (!$simulate) {
                $paymentFailure->update([
                    'suspended_at' => null,
                    'suspension_lifted_at' => null
                ]);
                
                $danny->update([
                    'status' => 'active',
                    'suspension_until' => null,
                    'suspension_reason' => null
                ]);
            }
            
            $this->info($simulate ? 'SIMULATING' : 'APPLYING' . ' the 3 missing payment failures...');
            
            foreach ($failedTrades as $index => $tradeId) {
                $this->info("Processing trade #{$tradeId}...");
                
                if ($simulate) {
                    // Just increment the failure count to see what would happen
                    $currentFailures = $paymentFailure->consecutive_failures + $index + 1;
                    $this->info("  Would be failure #{$currentFailures}");
                    
                    if ($currentFailures >= 3) {
                        $wouldSuspend = $paymentFailure->shouldSuspend();
                        $this->info("  Would trigger suspension: " . ($wouldSuspend ? 'YES' : 'NO'));
                    }
                } else {
                    // Actually process the failure
                    $result = $paymentFailureService->handlePaymentFailure(
                        $danny->id, 
                        "Missed payment for trade {$tradeId} (retroactive fix)"
                    );
                    
                    $this->info("  Failure #{$result['consecutive_failures']} recorded");
                    
                    if ($result['suspended']) {
                        $this->info("  🚨 USER SUSPENDED! Level {$result['suspension_level']}, Duration: {$result['suspension_duration_hours']}h");
                        break; // Stop processing once suspended
                    }
                    
                    // Refresh the payment failure record
                    $paymentFailure->refresh();
                }
            }
            
            if ($simulate) {
                DB::rollBack(); // Don't save simulation changes
                $this->info('✅ Simulation complete - no changes were made');
            } else {
                DB::commit();
                $this->info('✅ Fix applied successfully!');
                
                // Show final state
                $paymentFailure->refresh();
                $danny->refresh();
                
                $this->info("Final state:");
                $this->info("- Consecutive failures: {$paymentFailure->consecutive_failures}");
                $this->info("- Suspension level: {$paymentFailure->suspension_level}");  
                $this->info("- User status: {$danny->status}");
                $this->info("- Suspension until: " . ($danny->suspension_until ? $danny->suspension_until->format('Y-m-d H:i:s') : 'None'));
            }
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error fixing Danny\'s failures: ' . $e->getMessage());
            Log::error('Danny payment failure fix error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }
}
