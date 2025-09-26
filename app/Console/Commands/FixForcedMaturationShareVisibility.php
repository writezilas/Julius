<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixForcedMaturationShareVisibility extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:forced-maturation-visibility {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix shares that have pending payment confirmations but are not visible in sold shares view due to forced maturation';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Scanning for shares with pending payment confirmations that are not visible...');
        
        // Find shares that have pending payments but wrong status/get_from combination
        $problematicShares = UserShare::whereHas('pairedWithThis', function($query) {
                $query->where('is_paid', 0)
                      ->whereHas('payment', function($paymentQuery) {
                          $paymentQuery->where('status', 'paid');
                      });
            })
            ->where(function($query) {
                // Shares that won't show in sold shares view due to wrong get_from/status combination
                $query->where('get_from', 'purchase')
                      ->whereIn('status', ['completed']);
            })
            ->with(['pairedWithThis' => function($query) {
                $query->where('is_paid', 0)
                      ->with('payment');
            }])
            ->get();

        if ($problematicShares->isEmpty()) {
            $this->info('✅ No problematic shares found. All shares with pending payments are properly visible.');
            return 0;
        }

        $this->info("📋 Found {$problematicShares->count()} shares that need fixing:");
        
        $fixedCount = 0;
        foreach ($problematicShares as $share) {
            $pendingPayments = $share->pairedWithThis->where('is_paid', 0)->count();
            $totalAmount = $share->pairedWithThis->where('is_paid', 0)->sum('share');
            
            $this->line("  • Share {$share->ticket_no} (ID: {$share->id}) - User: {$share->user_id}");
            $this->line("    Status: {$share->status} | get_from: {$share->get_from}");
            $this->line("    Pending payments: {$pendingPayments} | Total amount: {$totalAmount}");
            
            if (!$this->option('dry-run')) {
                try {
                    DB::beginTransaction();
                    
                    // Fix the share to make it visible in sold shares view
                    $share->update([
                        'status' => 'paired',
                        'get_from' => 'allocated-by-admin'
                    ]);
                    
                    DB::commit();
                    $fixedCount++;
                    
                    $this->line("    ✅ Fixed - Share now visible in sold shares view");
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("    ❌ Failed to fix: {$e->getMessage()}");
                }
            } else {
                $this->line("    🔧 Would fix: status -> 'paired', get_from -> 'allocated-by-admin'");
            }
            
            $this->line("");
        }
        
        if ($this->option('dry-run')) {
            $this->warn("🔍 DRY RUN MODE: No changes were made. Run without --dry-run to apply fixes.");
            $this->info("Command to apply fixes: php artisan fix:forced-maturation-visibility");
        } else {
            $this->info("✅ Fixed {$fixedCount}/{$problematicShares->count()} shares successfully.");
        }
        
        // Additional recommendations
        $this->line("");
        $this->info("💡 Prevention Recommendations:");
        $this->line("  1. When forcing share maturation, ensure proper status transitions");
        $this->line("  2. Run this command after bulk maturation operations");
        $this->line("  3. Consider adding this to scheduled tasks for automatic fixing");
        
        return 0;
    }
}