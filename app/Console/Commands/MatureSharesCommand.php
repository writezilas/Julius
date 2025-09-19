<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use Carbon\Carbon;

class MatureSharesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shares:mature {--status=* : Share statuses to mature (default: completed)} {--force : Force maturation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mature all running/completed shares and make them available for sale in the market';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Share Maturation Process Started');
        $this->info('=====================================');

        // Get status filters from option or use default
        $statusFilters = $this->option('status') ?: ['completed'];
        
        // Build query for shares that need to be matured
        $query = UserShare::whereIn('status', $statusFilters)
            ->where('is_ready_to_sell', 0) // Only shares not ready to sell yet
            ->whereNull('matured_at'); // Only shares not already matured

        // Get count for confirmation
        $sharesToMatureCount = $query->count();

        if ($sharesToMatureCount === 0) {
            $this->warn('⚠️  No shares found that need maturation.');
            $this->info('All shares with status [' . implode(', ', $statusFilters) . '] are already matured and ready for sale.');
            return 0;
        }

        // Show summary
        $this->info("📊 Found {$sharesToMatureCount} share(s) to mature with status: [" . implode(', ', $statusFilters) . "]");
        
        // Get sample shares to show
        $sampleShares = $query->limit(5)->get(['id', 'ticket_no', 'amount', 'status', 'is_ready_to_sell', 'matured_at']);
        
        $this->info("\n📋 Sample shares to be updated:");
        $this->table(
            ['ID', 'Ticket No', 'Amount', 'Status', 'Ready to Sell', 'Matured At'],
            $sampleShares->map(function ($share) {
                return [
                    $share->id,
                    $share->ticket_no,
                    number_format($share->amount, 2),
                    $share->status,
                    $share->is_ready_to_sell ? 'Yes' : 'No',
                    $share->matured_at ?? 'NULL'
                ];
            })
        );

        if ($sharesToMatureCount > 5) {
            $this->info("... and " . ($sharesToMatureCount - 5) . " more shares");
        }

        // Confirmation (unless force flag is used)
        if (!$this->option('force')) {
            if (!$this->confirm("\n❓ Do you want to mature these {$sharesToMatureCount} share(s) and make them available for sale?")) {
                $this->info('❌ Operation cancelled by user.');
                return 0;
            }
        }

        // Perform the update
        $this->info("\n⏳ Processing maturation...");
        
        $now = Carbon::now();
        $updatedCount = $query->update([
            'is_ready_to_sell' => 1,
            'matured_at' => $now,
            'updated_at' => $now
        ]);

        // Show results
        $this->info("\n✅ Successfully matured {$updatedCount} share(s)!");
        $this->info("🕐 Maturation timestamp: {$now->format('Y-m-d H:i:s')}");
        
        // Verify the changes
        $this->info("\n🔍 Verification:");
        $readyToSellCount = UserShare::whereIn('status', $statusFilters)
            ->where('is_ready_to_sell', 1)
            ->whereNotNull('matured_at')
            ->count();
            
        $this->info("📈 Total shares now ready for sale with status [" . implode(', ', $statusFilters) . "]: {$readyToSellCount}");
        
        // Show updated sample
        if ($updatedCount > 0) {
            $updatedSample = UserShare::whereIn('status', $statusFilters)
                ->where('is_ready_to_sell', 1)
                ->whereNotNull('matured_at')
                ->limit(3)
                ->get(['id', 'ticket_no', 'amount', 'status', 'is_ready_to_sell', 'matured_at']);
                
            $this->info("\n📋 Sample of updated shares:");
            $this->table(
                ['ID', 'Ticket No', 'Amount', 'Status', 'Ready to Sell', 'Matured At'],
                $updatedSample->map(function ($share) {
                    return [
                        $share->id,
                        $share->ticket_no,
                        number_format($share->amount, 2),
                        $share->status,
                        $share->is_ready_to_sell ? 'Yes' : 'No',
                        $share->matured_at ? (is_string($share->matured_at) ? $share->matured_at : $share->matured_at->format('Y-m-d H:i:s')) : 'NULL'
                    ];
                })
            );
        }

        $this->info("\n🎉 Share maturation process completed successfully!");
        $this->info("💰 All matured shares are now available for sale in the market.");

        return 0;
    }
}
