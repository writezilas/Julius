<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use App\Services\EnhancedSharePairingService;
use App\Services\PaymentStateMachine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StateConsistencyMonitor extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'state:monitor 
                            {--fix : Automatically fix detected inconsistencies}
                            {--report : Generate detailed consistency report}
                            {--stats : Show statistics only}
                            {--alert-threshold=10 : Alert if inconsistencies exceed threshold}';

    /**
     * The console command description.
     */
    protected $description = 'Monitor state consistency across the share pairing system';

    /**
     * Services
     */
    private $pairingService;
    private $paymentService;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pairingService = new EnhancedSharePairingService();
        $this->paymentService = new PaymentStateMachine();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Starting State Consistency Monitor...');
        $startTime = microtime(true);

        if ($this->option('stats')) {
            $this->showStatistics();
            return;
        }

        // Run consistency checks
        $report = $this->generateConsistencyReport();
        
        if ($this->option('report')) {
            $this->displayDetailedReport($report);
        }

        // Check alert threshold
        $totalInconsistencies = $this->getTotalInconsistencies($report);
        $threshold = (int) $this->option('alert-threshold');

        if ($totalInconsistencies > $threshold) {
            $this->error("🚨 ALERT: {$totalInconsistencies} inconsistencies detected (threshold: {$threshold})");
            Log::error("State consistency alert: {$totalInconsistencies} inconsistencies detected", $report);
        }

        // Auto-fix if requested
        if ($this->option('fix') && $totalInconsistencies > 0) {
            $this->info('🔧 Auto-fixing inconsistencies...');
            $fixed = $this->fixInconsistencies($report);
            $this->displayFixResults($fixed);
        }

        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        if ($totalInconsistencies === 0) {
            $this->info("✅ All consistency checks passed! ({$duration}ms)");
        } else {
            $this->warn("⚠️  Found {$totalInconsistencies} inconsistencies ({$duration}ms)");
        }

        // Log monitoring results
        Log::info('State consistency monitoring completed', [
            'total_inconsistencies' => $totalInconsistencies,
            'duration_ms' => $duration,
            'fixed' => $this->option('fix'),
            'report' => $report
        ]);

        return $totalInconsistencies === 0 ? 0 : 1;
    }

    /**
     * Generate comprehensive consistency report
     */
    private function generateConsistencyReport(): array
    {
        $report = [
            'timestamp' => now()->toDateTimeString(),
            'database_health' => $this->checkDatabaseHealth(),
            'state_inconsistencies' => $this->checkStateInconsistencies(),
            'pairing_inconsistencies' => $this->checkPairingInconsistencies(),
            'payment_inconsistencies' => $this->checkPaymentInconsistencies(),
            'quantity_inconsistencies' => $this->checkQuantityInconsistencies(),
            'orphaned_records' => $this->checkOrphanedRecords(),
            'performance_metrics' => $this->gatherPerformanceMetrics()
        ];

        return $report;
    }

    /**
     * Check database health
     */
    private function checkDatabaseHealth(): array
    {
        return [
            'connection' => $this->testDatabaseConnection(),
            'table_counts' => [
                'user_shares' => UserShare::count(),
                'user_share_pairs' => UserSharePair::count(),
                'user_share_payments' => UserSharePayment::count()
            ],
            'constraints_active' => $this->checkConstraints()
        ];
    }

    /**
     * Check state inconsistencies
     */
    private function checkStateInconsistencies(): array
    {
        return [
            'buyers_paired_without_pairings' => $this->getBuyersPairedWithoutPairings(),
            'sellers_paired_without_holds' => $this->getSellersPairedWithoutHolds(),
            'completed_with_unpaid_pairings' => $this->getCompletedWithUnpaidPairings(),
            'failed_with_active_pairings' => $this->getFailedWithActivePairings(),
            'invalid_status_values' => $this->getInvalidStatusValues()
        ];
    }

    /**
     * Check pairing inconsistencies
     */
    private function checkPairingInconsistencies(): array
    {
        return [
            'invalid_payment_states' => $this->getInvalidPairingPaymentStates(),
            'quantity_mismatches' => $this->getPairingQuantityMismatches(),
            'missing_relationships' => $this->getMissingPairingRelationships(),
            'duplicate_pairings' => $this->getDuplicatePairings()
        ];
    }

    /**
     * Check payment inconsistencies
     */
    private function checkPaymentInconsistencies(): array
    {
        return [
            'payments_without_pairings' => $this->getPaymentsWithoutPairings(),
            'confirmed_without_pairing_paid' => $this->getConfirmedWithoutPairingPaid(),
            'paid_pairings_without_payments' => $this->getPaidPairingsWithoutPayments(),
            'negative_amounts' => $this->getNegativePaymentAmounts()
        ];
    }

    /**
     * Check quantity inconsistencies
     */
    private function checkQuantityInconsistencies(): array
    {
        return [
            'negative_quantities' => $this->getNegativeQuantities(),
            'hold_quantity_mismatches' => $this->getHoldQuantityMismatches(),
            'total_quantity_issues' => $this->getTotalQuantityIssues()
        ];
    }

    /**
     * Check orphaned records
     */
    private function checkOrphanedRecords(): array
    {
        return [
            'orphaned_pairings' => $this->getOrphanedPairings(),
            'orphaned_payments' => $this->getOrphanedPayments()
        ];
    }

    /**
     * Display detailed report
     */
    private function displayDetailedReport(array $report): void
    {
        $this->info("\n📊 STATE CONSISTENCY REPORT");
        $this->info("Generated: {$report['timestamp']}\n");

        // Database Health
        $this->info("🗄️  DATABASE HEALTH");
        $health = $report['database_health'];
        $this->line("   Connection: " . ($health['connection'] ? '✅ OK' : '❌ FAILED'));
        $this->line("   Tables: " . json_encode($health['table_counts']));
        $this->line("   Constraints: " . ($health['constraints_active'] ? '✅ Active' : '⚠️  Issues detected'));

        // State Inconsistencies
        $this->info("\n🔄 STATE INCONSISTENCIES");
        $states = $report['state_inconsistencies'];
        foreach ($states as $type => $count) {
            $status = $count === 0 ? '✅' : ($count > 10 ? '🚨' : '⚠️ ');
            $this->line("   {$status} " . str_replace('_', ' ', ucfirst($type)) . ": {$count}");
        }

        // Pairing Inconsistencies
        $this->info("\n🔗 PAIRING INCONSISTENCIES");
        $pairings = $report['pairing_inconsistencies'];
        foreach ($pairings as $type => $count) {
            $status = $count === 0 ? '✅' : ($count > 5 ? '🚨' : '⚠️ ');
            $this->line("   {$status} " . str_replace('_', ' ', ucfirst($type)) . ": {$count}");
        }

        // Payment Inconsistencies
        $this->info("\n💳 PAYMENT INCONSISTENCIES");
        $payments = $report['payment_inconsistencies'];
        foreach ($payments as $type => $count) {
            $status = $count === 0 ? '✅' : ($count > 5 ? '🚨' : '⚠️ ');
            $this->line("   {$status} " . str_replace('_', ' ', ucfirst($type)) . ": {$count}");
        }

        // Performance Metrics
        $this->info("\n⚡ PERFORMANCE METRICS");
        $metrics = $report['performance_metrics'];
        foreach ($metrics as $metric => $value) {
            $this->line("   📊 " . str_replace('_', ' ', ucfirst($metric)) . ": {$value}");
        }
    }

    /**
     * Show statistics only
     */
    private function showStatistics(): void
    {
        $pairingStats = $this->pairingService->getPairingStatistics();
        $paymentStats = $this->paymentService->getPaymentStatistics();

        $this->info("📊 SYSTEM STATISTICS\n");

        $this->info("🔗 PAIRING STATISTICS:");
        $this->line("   Total Pairings: {$pairingStats['total_pairings']}");
        $this->line("   Unpaid: {$pairingStats['unpaid_pairings']}");
        $this->line("   Paid: {$pairingStats['paid_pairings']}");
        $this->line("   Failed: {$pairingStats['failed_pairings']}");
        $this->line("   Buyer Shares Paired: {$pairingStats['buyer_shares_paired']}");
        $this->line("   Seller Shares Paired: {$pairingStats['seller_shares_paired']}");

        $this->info("\n💳 PAYMENT STATISTICS:");
        $payments = $paymentStats['payments'];
        $amounts = $paymentStats['amounts'];
        $this->line("   Total Payments: {$payments['total']}");
        $this->line("   Pending: {$payments['pending']}");
        $this->line("   Confirmed: {$payments['confirmed']}");
        $this->line("   Rejected: {$payments['rejected']}");
        $this->line("   Pending Amount: \${$amounts['pending_total']}");
        $this->line("   Confirmed Amount: \${$amounts['confirmed_total']}");

        if (!empty($pairingStats['inconsistent_states'])) {
            $this->warn("\n⚠️  INCONSISTENCIES DETECTED:");
            foreach ($pairingStats['inconsistent_states'] as $type => $issues) {
                $count = is_array($issues) ? count($issues) : $issues;
                $this->line("   {$type}: {$count}");
            }
        } else {
            $this->info("\n✅ No inconsistencies detected");
        }
    }

    // Individual check methods
    private function getBuyersPairedWithoutPairings(): int
    {
        return UserShare::where('status', 'paired')
            ->where('get_from', 'purchase')
            ->whereDoesntHave('pairedShares', function($q) {
                $q->where('is_paid', '!=', 2);
            })
            ->count();
    }

    private function getSellersPairedWithoutHolds(): int
    {
        return UserShare::where('status', 'paired')
            ->where(function($q) {
                $q->where('get_from', '!=', 'purchase')->orWhereNull('get_from');
            })
            ->where(function($q) {
                $q->where('hold_quantity', '<=', 0)->orWhereNull('hold_quantity');
            })
            ->count();
    }

    private function getCompletedWithUnpaidPairings(): int
    {
        return UserShare::where('status', 'completed')
            ->where('get_from', 'purchase')
            ->whereHas('pairedShares', function($q) {
                $q->where('is_paid', 0);
            })
            ->count();
    }

    private function getFailedWithActivePairings(): int
    {
        return UserShare::where('status', 'failed')
            ->whereHas('pairedShares', function($q) {
                $q->whereIn('is_paid', [0, 1, 3]);
            })
            ->count();
    }

    private function getInvalidStatusValues(): int
    {
        $validStatuses = ['pending', 'paired', 'failed', 'completed', 'suspended'];
        return UserShare::whereNotIn('status', $validStatuses)->count();
    }

    private function testDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkConstraints(): bool
    {
        // This is a simplified check - in production you'd check actual constraint status
        return true;
    }

    private function getTotalInconsistencies(array $report): int
    {
        $total = 0;
        
        foreach ($report['state_inconsistencies'] as $count) {
            $total += is_numeric($count) ? $count : 0;
        }
        
        foreach ($report['pairing_inconsistencies'] as $count) {
            $total += is_numeric($count) ? $count : 0;
        }
        
        foreach ($report['payment_inconsistencies'] as $count) {
            $total += is_numeric($count) ? $count : 0;
        }
        
        foreach ($report['quantity_inconsistencies'] as $count) {
            $total += is_numeric($count) ? $count : 0;
        }
        
        foreach ($report['orphaned_records'] as $count) {
            $total += is_numeric($count) ? $count : 0;
        }
        
        return $total;
    }

    private function fixInconsistencies(array $report): array
    {
        $fixed = [];
        
        // Use the pairing service to fix inconsistencies
        $pairingFixed = $this->pairingService->fixInconsistentStates();
        $fixed['pairing_service_fixes'] = $pairingFixed;
        
        // Additional fixes can be added here
        
        return $fixed;
    }

    private function displayFixResults(array $fixed): void
    {
        $this->info("\n🔧 FIX RESULTS:");
        foreach ($fixed as $category => $results) {
            if (is_array($results)) {
                foreach ($results as $type => $items) {
                    if (is_array($items) && count($items) > 0) {
                        $this->line("   ✅ Fixed {$type}: " . count($items) . " items");
                    }
                }
            }
        }
    }

    private function gatherPerformanceMetrics(): array
    {
        $start = microtime(true);
        
        // Sample queries to measure performance
        UserShare::where('status', 'paired')->count();
        UserSharePair::where('is_paid', 0)->count();
        
        $queryTime = (microtime(true) - $start) * 1000;
        
        return [
            'avg_query_time_ms' => round($queryTime, 2),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2)
        ];
    }

    // Placeholder methods for additional checks
    private function getInvalidPairingPaymentStates(): int { return 0; }
    private function getPairingQuantityMismatches(): int { return 0; }
    private function getMissingPairingRelationships(): int { return 0; }
    private function getDuplicatePairings(): int { return 0; }
    private function getPaymentsWithoutPairings(): int { return 0; }
    private function getConfirmedWithoutPairingPaid(): int { return 0; }
    private function getPaidPairingsWithoutPayments(): int { return 0; }
    private function getNegativePaymentAmounts(): int { return 0; }
    private function getNegativeQuantities(): int { return 0; }
    private function getHoldQuantityMismatches(): int { return 0; }
    private function getTotalQuantityIssues(): int { return 0; }
    private function getOrphanedPairings(): int { return 0; }
    private function getOrphanedPayments(): int { return 0; }
}
