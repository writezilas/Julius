<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use App\Models\UserSharePayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResumeStuckTimersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timers:resume-stuck 
                            {--dry-run : Show what would be updated without making changes} 
                            {--force : Skip confirmation prompts}
                            {--ticket= : Resume timer for specific ticket only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resume timers for shares that are stuck in paused state after payment confirmation';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔄 RESUME STUCK TIMERS COMMAND');
        $this->info('================================');
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');
        $specificTicket = $this->option('ticket');

        // Find shares with paused timers that have confirmed payments
        $query = UserShare::where('timer_paused', 1)
            ->whereIn('status', ['completed', 'paired'])
            ->where('is_ready_to_sell', 0)
            ->whereNotNull('timer_paused_at');

        // Filter by specific ticket if provided
        if ($specificTicket) {
            $query->where('ticket_no', $specificTicket);
            $this->info("🎯 Filtering by ticket: {$specificTicket}");
        }

        $stuckShares = $query->get();

        if ($stuckShares->isEmpty()) {
            $this->info('✅ No stuck timers found. All shares are processing normally.');
            return 0;
        }

        $this->info("📋 Found {$stuckShares->count()} share(s) with stuck timers:");
        $this->showStuckShares($stuckShares);

        // Analyze each share to determine if it should be resumed
        $sharesToResume = collect();
        $sharesWithIssues = collect();

        foreach ($stuckShares as $share) {
            $analysis = $this->analyzeStuckShare($share);
            
            if ($analysis['should_resume']) {
                $sharesToResume->push([
                    'share' => $share,
                    'analysis' => $analysis
                ]);
            } else {
                $sharesWithIssues->push([
                    'share' => $share,
                    'analysis' => $analysis
                ]);
            }
        }

        if ($sharesToResume->isEmpty()) {
            $this->warn('⚠️  No shares are eligible for timer resumption.');
            if ($sharesWithIssues->isNotEmpty()) {
                $this->error('❌ Found shares with issues:');
                foreach ($sharesWithIssues as $item) {
                    $share = $item['share'];
                    $analysis = $item['analysis'];
                    $this->error("   {$share->ticket_no}: {$analysis['reason']}");
                }
            }
            return 0;
        }

        $this->info("\n✅ Shares eligible for timer resumption: {$sharesToResume->count()}");

        if ($isDryRun) {
            $this->warn("\n🧪 DRY RUN MODE - No changes will be made");
            $this->showResumePreview($sharesToResume);
            return 0;
        }

        // Confirmation
        if (!$isForce) {
            $this->warn("\n⚠️  WARNING: This will resume timers for stuck shares!");
            if (!$this->confirm("Continue with resuming {$sharesToResume->count()} timer(s)?")) {
                $this->info('❌ Operation cancelled by user.');
                return 0;
            }
        }

        // Execute timer resumption
        return $this->executeTimerResumption($sharesToResume);
    }

    /**
     * Display stuck shares information
     */
    private function showStuckShares($shares)
    {
        $headers = ['Ticket No', 'Status', 'Paused Since', 'Duration Paused', 'User'];
        $rows = [];

        foreach ($shares as $share) {
            $pausedAt = $share->timer_paused_at ? Carbon::parse($share->timer_paused_at) : null;
            $duration = $pausedAt ? $pausedAt->diffForHumans() : 'Unknown';
            
            $rows[] = [
                $share->ticket_no,
                $share->status,
                $pausedAt ? $pausedAt->format('Y-m-d H:i:s') : 'Unknown',
                $duration,
                $share->user->username ?? 'Unknown'
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Analyze a stuck share to determine if timer should be resumed
     */
    private function analyzeStuckShare(UserShare $share): array
    {
        // Check if share has confirmed payments
        $confirmedPayments = UserSharePayment::where('user_share_id', $share->id)
            ->where('status', 'conformed')
            ->count();

        // Check if all required payments are confirmed
        $totalPairs = $share->pairedShares()->count();
        $paidPairs = $share->pairedShares()->where('is_paid', 1)->count();

        // Share should resume timer if:
        // 1. Has confirmed payments, OR
        // 2. All pairs are paid, OR  
        // 3. Is admin-allocated (no payment required)
        $shouldResume = false;
        $reason = '';

        if ($share->get_from === 'allocated-by-admin') {
            $shouldResume = true;
            $reason = 'Admin-allocated share - no payment confirmation required';
        } elseif ($confirmedPayments > 0) {
            $shouldResume = true;
            $reason = "Has {$confirmedPayments} confirmed payment(s)";
        } elseif ($totalPairs > 0 && $paidPairs === $totalPairs) {
            $shouldResume = true;
            $reason = "All {$totalPairs} payment pair(s) are marked as paid";
        } elseif ($totalPairs === 0) {
            $shouldResume = false;
            $reason = 'No payment pairs found - unable to determine payment status';
        } else {
            $shouldResume = false;
            $reason = "Only {$paidPairs}/{$totalPairs} pairs are paid";
        }

        return [
            'should_resume' => $shouldResume,
            'reason' => $reason,
            'confirmed_payments' => $confirmedPayments,
            'paid_pairs' => $paidPairs,
            'total_pairs' => $totalPairs
        ];
    }

    /**
     * Show preview of what would be resumed
     */
    private function showResumePreview($sharesToResume)
    {
        $this->info("\n📋 Shares that would have timers resumed:");
        
        $headers = ['Ticket No', 'Reason', 'Paused Duration'];
        $rows = [];

        foreach ($sharesToResume as $item) {
            $share = $item['share'];
            $analysis = $item['analysis'];
            
            $pausedAt = Carbon::parse($share->timer_paused_at);
            $pausedDuration = $pausedAt->diffInSeconds(Carbon::now());
            
            $rows[] = [
                $share->ticket_no,
                $analysis['reason'],
                gmdate('H:i:s', $pausedDuration)
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Execute timer resumption process
     */
    private function executeTimerResumption($sharesToResume)
    {
        $this->info("\n⏳ Resuming timers...");

        $successCount = 0;
        $errorCount = 0;

        DB::beginTransaction();

        try {
            foreach ($sharesToResume as $item) {
                $share = $item['share'];
                $analysis = $item['analysis'];

                try {
                    // Calculate total paused duration
                    $pausedAt = Carbon::parse($share->timer_paused_at);
                    $pausedDurationSeconds = $pausedAt->diffInSeconds(Carbon::now());
                    
                    // Update the share
                    $share->update([
                        'timer_paused' => 0,
                        'timer_paused_at' => null,
                        'paused_duration_seconds' => $share->paused_duration_seconds + $pausedDurationSeconds
                    ]);

                    $successCount++;

                    Log::info("Timer resumed for share: {$share->ticket_no}", [
                        'share_id' => $share->id,
                        'ticket_no' => $share->ticket_no,
                        'paused_duration_added' => $pausedDurationSeconds,
                        'reason' => $analysis['reason']
                    ]);

                    $this->info("   ✅ {$share->ticket_no}: Timer resumed (paused for " . gmdate('H:i:s', $pausedDurationSeconds) . ")");

                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error("Failed to resume timer for share {$share->id}: " . $e->getMessage());
                    $this->error("   ❌ {$share->ticket_no}: Failed - " . $e->getMessage());
                }
            }

            DB::commit();

            $this->info("\n🎉 TIMER RESUMPTION COMPLETED!");
            $this->info("   Successfully resumed: {$successCount} timer(s)");
            
            if ($errorCount > 0) {
                $this->error("   Failed to resume: {$errorCount} timer(s)");
            }

            // Run maturation check for resumed shares
            $this->info("\n🔄 Running maturation check for resumed shares...");
            \Artisan::call('sharematured:cron');
            $this->info("✅ Maturation check completed");

            return $successCount > 0 ? 0 : 1;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\n❌ Transaction failed: " . $e->getMessage());
            Log::error("Timer resumption process failed: " . $e->getMessage());
            return 1;
        }
    }
}