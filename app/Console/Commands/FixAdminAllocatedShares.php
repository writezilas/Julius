<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use Carbon\Carbon;

class FixAdminAllocatedShares extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shares:fix-admin-allocated {--dry-run : Show what would be fixed without making changes} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix admin-allocated shares to use proper countdown timer logic instead of immediate maturation';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Scanning for incorrectly configured admin-allocated shares...');
        $this->info('========================================================');

        // Find admin-allocated shares that were incorrectly marked as immediately ready to sell
        $query = UserShare::where('get_from', 'allocated-by-admin')
            ->where('status', 'completed')
            ->where('is_ready_to_sell', 1)
            ->whereNotNull('matured_at');

        $sharesCount = $query->count();

        if ($sharesCount === 0) {
            $this->info('✅ All admin-allocated shares are properly configured with countdown timers.');
            return 0;
        }

        $shares = $query->get();

        $this->warn("⚠️  Found {$sharesCount} admin-allocated share(s) incorrectly marked as immediately ready to sell:");
        $this->newLine();

        // Show the shares that need fixing
        $tableData = [];
        foreach ($shares as $share) {
            $tableData[] = [
                $share->id,
                $share->ticket_no,
                $share->user_id,
                number_format($share->amount, 2),
                $share->status,
                $share->is_ready_to_sell ? 'Yes' : 'No',
                $share->matured_at ?? 'NULL',
                $share->created_at->format('Y-m-d H:i:s')
            ];
        }

        $this->table([
            'ID', 'Ticket No', 'User ID', 'Amount', 'Status', 'Ready to Sell', 'Matured At', 'Created At'
        ], $tableData);

        if ($this->option('dry-run')) {
            $this->info('🔍 DRY RUN MODE: No changes will be made.');
            $this->info("These {$sharesCount} shares would be updated to:");
            $this->info('  - is_ready_to_sell: 0 (to start countdown timer)');
            $this->info('  - matured_at: NULL (timer will handle maturation)');
            $this->info('  - Status: Running with countdown timer based on period');
            return 0;
        }

        // Confirmation (unless force flag is used)
        if (!$this->option('force')) {
            if (!$this->confirm("Do you want to fix these {$sharesCount} admin-allocated share(s)?")) {
                $this->info('❌ Operation cancelled by user.');
                return 0;
            }
        }

        // Fix the shares
        $this->info('⏳ Resetting admin-allocated shares to use proper countdown timers...');
        
        $now = Carbon::now();
        $updatedCount = $query->update([
            'is_ready_to_sell' => 0,
            'matured_at' => null,
            'updated_at' => $now
        ]);

        // Show results
        $this->info("✅ Successfully reset {$updatedCount} admin-allocated share(s)!");
        $this->info("⏰ Shares will now show countdown timers based on their period");
        
        // Verify the fix
        $this->info('🔍 Verification:');
        $runningCount = UserShare::where('get_from', 'allocated-by-admin')
            ->where('status', 'completed')
            ->where('is_ready_to_sell', 0)
            ->whereNull('matured_at')
            ->count();

        $immediateCount = UserShare::where('get_from', 'allocated-by-admin')
            ->where('status', 'completed')
            ->where('is_ready_to_sell', 1)
            ->whereNotNull('matured_at')
            ->count();

        $this->info("📊 Admin-allocated shares status:");
        $this->info("   - Running with timers: {$runningCount}");
        $this->info("   - Still marked as immediate: {$immediateCount}");
        
        if ($immediateCount === 0) {
            $this->info('✅ All admin-allocated shares now use proper countdown timers!');
        } else {
            $this->warn("⚠️  {$immediateCount} admin-allocated shares still marked as immediate.");
        }

        $this->info('⏰ Fixed shares will show countdown timers and mature naturally based on their period.');

        return 0;
    }
}
