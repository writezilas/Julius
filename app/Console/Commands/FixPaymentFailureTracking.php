<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserShare;
use App\Services\PaymentFailureService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FixPaymentFailureTracking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment-failures:fix {--user= : Fix for specific username} {--dry-run : Show what would be fixed without actually fixing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix payment failure tracking for users with failed shares that were not properly counted';

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
        $this->info('🔧 Starting Payment Failure Tracking Fix...');
        $this->newLine();

        $username = $this->option('user');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🚨 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        try {
            // Step 1: Find users with failed shares but low consecutive failures
            $problematicUsers = $this->findProblematicUsers($username);

            if ($problematicUsers->isEmpty()) {
                $this->info('✅ No users found with payment failure tracking issues');
                return 0;
            }

            $this->info("Found {$problematicUsers->count()} user(s) with payment failure tracking issues:");
            $this->newLine();

            // Step 2: Process each problematic user
            foreach ($problematicUsers as $userData) {
                $this->processUser($userData, $dryRun);
            }

            if (!$dryRun) {
                $this->newLine();
                $this->info('✅ Payment failure tracking fix completed successfully!');
            } else {
                $this->newLine();
                $this->warn('💡 Run without --dry-run to apply these fixes');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error during payment failure tracking fix: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Find users with failed shares but inadequate failure tracking
     */
    protected function findProblematicUsers($username = null)
    {
        $query = DB::table('users')
            ->select('users.id', 'users.username', 'users.email', 'users.status', 'users.suspension_until')
            ->leftJoin('user_payment_failures', 'users.id', '=', 'user_payment_failures.user_id')
            ->selectRaw('COALESCE(user_payment_failures.consecutive_failures, 0) as consecutive_failures')
            ->selectRaw('user_payment_failures.suspended_at')
            ->selectRaw('(SELECT COUNT(*) FROM user_shares WHERE user_shares.user_id = users.id AND user_shares.status = "failed" AND user_shares.created_at > COALESCE(user_payment_failures.suspension_lifted_at, "2025-08-24 00:00:00")) as recent_failed_count')
            ->whereRaw('(SELECT COUNT(*) FROM user_shares WHERE user_shares.user_id = users.id AND user_shares.status = "failed" AND user_shares.created_at > COALESCE(user_payment_failures.suspension_lifted_at, "2025-08-24 00:00:00")) >= 3')
            ->where(function($q) {
                $q->where('users.status', '!=', 'suspend')
                  ->orWhereNull('users.suspension_until')
                  ->orWhere('users.suspension_until', '<', now());
            });

        if ($username) {
            $query->where('users.username', $username);
        }

        return $query->get();
    }

    /**
     * Process an individual problematic user
     */
    protected function processUser($userData, $dryRun = false)
    {
        $user = User::find($userData->id);
        
        if (!$user) {
            $this->error("❌ User with ID {$userData->id} not found");
            return;
        }

        $this->info("🔍 Processing user: {$user->username} (ID: {$user->id})");

        // Get failed shares after last suspension lift
        $suspensionLiftDate = $userData->suspended_at ? 
            Carbon::parse($userData->suspended_at)->addHours(12) : 
            Carbon::parse('2025-08-24 00:00:00');

        $failedShares = UserShare::where('user_id', $user->id)
            ->where('status', 'failed')
            ->where('created_at', '>', $suspensionLiftDate)
            ->orderBy('created_at', 'asc')
            ->get();

        $this->line("  📊 Current consecutive failures: {$userData->consecutive_failures}");
        $this->line("  🚫 Failed shares since last suspension: {$failedShares->count()}");
        $this->line("  📅 Checking shares created after: {$suspensionLiftDate}");
        $this->line("  👤 Current status: {$user->status}");

        if ($failedShares->count() >= 3) {
            $this->warn("  ⚠️  This user should be suspended but is not!");
            
            if (!$dryRun) {
                $this->line("  🔧 Applying payment failure tracking fix...");
                
                DB::beginTransaction();
                try {
                    // Reset payment failure tracking if user was previously suspended
                    $failure = $user->getCurrentPaymentFailure();
                    if ($failure->suspended_at) {
                        $this->line("    🔧 Resetting previous suspension tracking to allow new suspension...");
                        $failure->update([
                            'consecutive_failures' => 0,
                            'last_failure_at' => null,
                            'suspended_at' => null,
                            'suspension_lifted_at' => $failure->suspended_at->addHours(12),
                            'failure_reason' => null,
                        ]);
                    }
                    
                    // Process each failed share to build up consecutive failures
                    $processedCount = 0;
                    foreach ($failedShares->take(3) as $share) {
                        $result = $this->paymentFailureService->handlePaymentFailure(
                            $user->id, 
                            "Retroactive processing - Share {$share->ticket_no} failed on {$share->updated_at}"
                        );
                        
                        $processedCount++;
                        $this->line("    ✓ Processed failure {$processedCount}/3 (Share: {$share->ticket_no})");
                        
                        if ($result['suspended']) {
                            $this->warn("    🔒 User suspended after {$processedCount} failures");
                            break;
                        }
                    }
                    
                    DB::commit();
                    
                    $user->refresh();
                    $this->info("  ✅ User {$user->username} status updated to: {$user->status}");
                    if ($user->suspension_until) {
                        $this->info("  ⏰ Suspended until: {$user->suspension_until}");
                    }
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("  ❌ Error processing user {$user->username}: " . $e->getMessage());
                }
            } else {
                $this->line("  💭 Would process {$failedShares->count()} failed shares and suspend user");
            }
        } else {
            $this->info("  ✅ User has insufficient failures to warrant suspension");
        }
        
        $this->newLine();
    }
}
