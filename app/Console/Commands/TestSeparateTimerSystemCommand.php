<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use App\Services\EnhancedTimerManagementService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestSeparateTimerSystemCommand extends Command
{
    protected $signature = 'timers:test-separate-system 
                            {--ticket= : Test specific ticket number}
                            {--dry-run : Show analysis without making changes}';

    protected $description = 'Test and demonstrate the new separate timer system for bought vs sold shares';

    public function handle()
    {
        $this->info('🧪 TESTING SEPARATE TIMER SYSTEM');
        $this->info(str_repeat('=', 60));
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $specificTicket = $this->option('ticket');

        if ($isDryRun) {
            $this->warn('🧪 DRY RUN MODE - Analysis only');
            $this->newLine();
        }

        try {
            $enhancedTimerService = new EnhancedTimerManagementService();

            // Find shares to analyze
            $query = UserShare::where('get_from', 'purchase')
                ->whereIn('status', ['completed', 'paired'])
                ->with(['user', 'trade']);

            if ($specificTicket) {
                $query->where('ticket_no', $specificTicket);
            }

            $shares = $query->get();

            if ($shares->isEmpty()) {
                $this->info('✅ No purchased shares found to test.');
                return 0;
            }

            $this->info("📊 Found {$shares->count()} purchased share(s) to analyze:");
            $this->newLine();

            foreach ($shares as $share) {
                $this->analyzeShareTimers($share, $enhancedTimerService, $isDryRun);
                $this->newLine();
            }

            if (!$isDryRun) {
                $this->info('🎉 Separate timer system test completed!');
                $this->info('💡 Check the logs for detailed timer operations.');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }

    private function analyzeShareTimers(UserShare $share, EnhancedTimerManagementService $timerService, bool $isDryRun)
    {
        $this->line("🔍 ANALYZING SHARE: {$share->ticket_no}");
        $this->line("   User: {$share->user->username} ({$share->user->name})");
        $this->line("   Status: {$share->status}");
        $this->line("   Investment: KSH " . number_format($share->amount, 2));
        $this->line("   Period: {$share->period} days");
        
        // Analyze payment timer (buying phase)
        $this->line("\n   📱 PAYMENT TIMER ANALYSIS (Buying Phase):");
        $paymentTimerInfo = $timerService->getPaymentTimerInfo($share);
        
        $this->line("      Created: " . ($share->created_at ? $share->created_at->format('Y-m-d H:i:s') : 'N/A'));
        $this->line("      Payment Deadline: " . ($share->payment_deadline_minutes ?? 0) . " minutes");
        $this->line("      Payment Timer Paused: " . ($share->payment_timer_paused ? 'YES' : 'NO'));
        if ($share->payment_timer_paused_at) {
            $this->line("      Payment Paused At: " . $share->payment_timer_paused_at);
        }
        $this->line("      Payment Timer Expired: " . ($paymentTimerInfo['is_expired'] ? 'YES' : 'NO'));

        // Analyze selling timer (investment maturity phase)
        $this->line("\n   📈 SELLING TIMER ANALYSIS (Investment Maturity):");
        $sellingTimerInfo = $timerService->getSellingTimerInfo($share);
        
        $this->line("      Selling Started At: " . ($share->selling_started_at ?? 'NOT STARTED'));
        $this->line("      Selling Timer Paused: " . ($share->selling_timer_paused ? 'YES' : 'NO'));
        if ($share->selling_timer_paused_at) {
            $this->line("      Selling Paused At: " . $share->selling_timer_paused_at);
        }
        $this->line("      Investment Matured: " . ($sellingTimerInfo['is_mature'] ? 'YES' : 'NO'));
        $this->line("      Is Ready To Sell: " . ($share->is_ready_to_sell ? 'YES' : 'NO'));

        // Legacy timer analysis
        $this->line("\n   🕰️  LEGACY TIMER ANALYSIS:");
        $this->line("      Start Date: " . ($share->start_date ?? 'N/A'));
        $this->line("      Legacy Timer Paused: " . ($share->timer_paused ? 'YES' : 'NO'));
        if ($share->timer_paused_at) {
            $this->line("      Legacy Paused At: " . $share->timer_paused_at);
        }
        $this->line("      Matured At: " . ($share->matured_at ?? 'NOT MATURED'));

        // Recommendations
        $this->line("\n   💡 RECOMMENDATIONS:");
        
        if ($share->status === 'completed' && !$share->selling_started_at) {
            $this->warn("      ⚠️  Should start selling timer for investment maturity");
            
            if (!$isDryRun) {
                $this->line("      🔄 Starting selling timer...");
                $timerService->startSellingTimer($share, 'Testing separate timer system');
                $this->info("      ✅ Selling timer started successfully!");
            }
        }

        if ($share->selling_started_at && !$share->is_ready_to_sell) {
            if ($sellingTimerInfo['is_mature']) {
                $this->warn("      ⚠️  Investment should be matured (selling timer complete)");
            } else {
                $completionPercentage = $sellingTimerInfo['completion_percentage'] ?? 0;
                $this->line("      📊 Investment progress: " . number_format($completionPercentage, 1) . "%");
            }
        }

        if ($share->timer_paused && $share->status === 'completed') {
            $this->warn("      ⚠️  Legacy timer pause state should be cleared");
        }
    }
}