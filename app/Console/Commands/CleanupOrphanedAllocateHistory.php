<?php

namespace App\Console\Commands;

use App\Models\AllocateShareHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupOrphanedAllocateHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:orphaned-allocate-history {--dry-run : Show what would be cleaned without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned allocate_share_histories records that reference non-existent user_shares';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🧹 Cleaning up orphaned AllocateShareHistory records...');
        $this->newLine();

        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🚨 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        try {
            // Find orphaned records
            $orphanedRecords = DB::table('allocate_share_histories as ash')
                ->leftJoin('user_shares as us', 'ash.user_share_id', '=', 'us.id')
                ->whereNull('us.id')
                ->select('ash.id', 'ash.user_share_id', 'ash.shares', 'ash.created_at')
                ->get();

            if ($orphanedRecords->isEmpty()) {
                $this->info('✅ No orphaned records found. Database is clean!');
                return 0;
            }

            $this->info("Found {$orphanedRecords->count()} orphaned record(s):");
            $this->newLine();

            foreach ($orphanedRecords as $record) {
                $this->line("📝 AllocateHistory ID: {$record->id}");
                $this->line("  🔗 References non-existent UserShare ID: {$record->user_share_id}");
                $this->line("  📊 Shares: {$record->shares}");
                $this->line("  📅 Created: {$record->created_at}");
                $this->newLine();
            }

            if (!$dryRun) {
                // Delete orphaned records
                $deleted = DB::table('allocate_share_histories')
                    ->leftJoin('user_shares', 'allocate_share_histories.user_share_id', '=', 'user_shares.id')
                    ->whereNull('user_shares.id')
                    ->delete();

                $this->info("✅ Successfully cleaned up {$deleted} orphaned record(s)!");
                
                // Log the cleanup
                \Log::info("Cleaned up {$deleted} orphaned AllocateShareHistory records");
            } else {
                $this->warn("💡 Run without --dry-run to clean up these {$orphanedRecords->count()} orphaned records");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error during cleanup: ' . $e->getMessage());
            \Log::error('Error cleaning up orphaned AllocateShareHistory records: ' . $e->getMessage());
            return 1;
        }
    }
}
