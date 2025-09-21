<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use App\Services\PaymentVerificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecoverIncorrectlyFailedTradesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'trades:recover-failed {--tickets=* : Specific ticket numbers to recover} {--dry-run : Show what would be recovered without making changes} {--all : Check all failed trades}';

    /**
     * The console command description.
     */
    protected $description = 'Recover trades that were incorrectly marked as failed when they had payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Recovering Incorrectly Failed Trades with Payment Priority');
        $this->newLine();
        
        $tickets = $this->option('tickets');
        $dryRun = $this->option('dry-run');
        $checkAll = $this->option('all');
        
        if (empty($tickets) && !$checkAll) {
            // Default to the specific problematic tickets
            $tickets = ['AB-17584301792936', 'AB-17584301917046'];
            $this->info('🎯 Focusing on specific problematic tickets: ' . implode(', ', $tickets));
        } elseif ($checkAll) {
            $tickets = null;
            $this->info('🔍 Checking ALL failed trades');
        } else {
            $this->info('🎯 Checking specific tickets: ' . implode(', ', $tickets));
        }
        
        if ($dryRun) {
            $this->warn('🧪 DRY RUN MODE - No changes will be made');
        }
        
        $this->newLine();
        
        try {
            $verificationService = new PaymentVerificationService();
            
            // First, analyze what needs recovery
            $this->info('📊 Analyzing failed trades for payment evidence...');
            $recoveryStats = $verificationService->recoverIncorrectlyFailedTrades($tickets);
            
            $this->displayAnalysisResults($recoveryStats);
            
            if ($recoveryStats['incorrectly_failed'] === 0) {
                $this->info('✅ No incorrectly failed trades found!');
                return 0;
            }
            
            // Show specific details for each incorrectly failed trade
            $this->displayDetailedRecoveryInfo($recoveryStats);
            
            if (!$dryRun) {
                if (!$this->confirm('Do you want to restore these incorrectly failed trades?')) {
                    $this->info('❌ Recovery cancelled by user');
                    return 1;
                }
                
                $this->info('🔧 Restoring trades...');
                
                DB::beginTransaction();
                
                try {
                    $restoreStats = $verificationService->restoreIncorrectlyFailedTrades($tickets, false);
                    
                    DB::commit();
                    
                    $this->info("✅ Successfully restored {$restoreStats['actually_restored']} trades!");
                    $this->displayRestorationResults($restoreStats);
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error('❌ Error during restoration: ' . $e->getMessage());
                    Log::error('Trade restoration failed: ' . $e->getMessage());
                    return 1;
                }
            } else {
                $this->info('🧪 DRY RUN completed - Run without --dry-run to apply changes');
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Command failed: ' . $e->getMessage());
            Log::error('RecoverIncorrectlyFailedTradesCommand failed: ' . $e->getMessage());
            return 1;
        }
    }
    
    private function displayAnalysisResults(array $stats)
    {
        $this->info('📊 ANALYSIS RESULTS:');
        $this->line("   • Total failed trades examined: {$stats['total_examined']}");
        $this->line("   • Incorrectly failed trades found: {$stats['incorrectly_failed']}");
        
        if ($stats['incorrectly_failed'] > 0) {
            $this->error("   🚨 Found {$stats['incorrectly_failed']} trades that were incorrectly marked as failed!");
        }
        
        $this->newLine();
    }
    
    private function displayDetailedRecoveryInfo(array $stats)
    {
        $this->info('🔍 DETAILED RECOVERY INFORMATION:');
        $this->newLine();
        
        foreach ($stats['recovered_trades'] as $trade) {
            $this->line("📋 Trade: {$trade['ticket_no']}");
            $this->line("   • User ID: {$trade['user_id']}");
            $this->line("   • Amount: {$trade['amount']}");
            $this->line("   • Recovery Reasons:");
            foreach ($trade['recovery_reasons'] as $reason) {
                $this->line("     ✅ {$reason}");
            }
            $this->newLine();
        }
    }
    
    private function displayRestorationResults(array $stats)
    {
        $this->newLine();
        $this->info('📊 RESTORATION SUMMARY:');
        $this->line("   • Trades analyzed: {$stats['total_examined']}");
        $this->line("   • Incorrectly failed: {$stats['incorrectly_failed']}");
        $this->line("   • Successfully restored: {$stats['actually_restored']}");
        
        $this->newLine();
        $this->info('🎉 Trade recovery completed successfully!');
        $this->line('These trades are now restored and will be processed normally.');
        
        // Show specific recovery reasons
        if (!empty($stats['recovery_reasons'])) {
            $this->newLine();
            $this->info('🔧 Recovery Details:');
            foreach ($stats['recovery_reasons'] as $recovery) {
                $this->line("   • {$recovery['ticket_no']}: " . implode(', ', $recovery['reasons']));
            }
        }
    }
}