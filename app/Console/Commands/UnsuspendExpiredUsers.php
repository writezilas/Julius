<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UnsuspendExpiredUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:unsuspend-expired {--dry-run : Show what would be unsuspended without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically unsuspend users whose suspension period has expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired user suspensions...');
        
        // Find suspended users whose suspension time has expired
        $expiredSuspensions = User::where('status', 'suspend')
            ->where('suspension_until', '<', now())
            ->whereNotNull('suspension_until')
            ->get();
        
        if ($expiredSuspensions->isEmpty()) {
            $this->info('No expired suspensions found.');
            return 0;
        }
        
        $this->info("Found {$expiredSuspensions->count()} expired suspension(s):");
        
        // Show details of users to be unsuspended
        $table = [];
        foreach ($expiredSuspensions as $user) {
            $table[] = [
                'ID' => $user->id,
                'Username' => $user->username,
                'Email' => $user->email,
                'Suspended Until' => $user->suspension_until->format('Y-m-d H:i:s'),
                'Expired' => $user->suspension_until->diffForHumans()
            ];
        }
        
        $this->table(['ID', 'Username', 'Email', 'Suspended Until', 'Expired'], $table);
        
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN: No changes were made. Remove --dry-run to actually unsuspend these users.');
            return 0;
        }
        
        // Ask for confirmation unless in quiet mode
        if (!$this->option('quiet') && !$this->confirm('Do you want to unsuspend these users?', true)) {
            $this->info('Operation cancelled.');
            return 1;
        }
        
        // Unsuspend the users
        $unsuspendedCount = 0;
        foreach ($expiredSuspensions as $user) {
            try {
                $user->update([
                    'status' => 'fine',
                    'suspension_until' => null
                ]);
                
                $this->line("✓ Unsuspended: {$user->username} ({$user->email})");
                $unsuspendedCount++;
            } catch (\Exception $e) {
                $this->error("✗ Failed to unsuspend {$user->username}: {$e->getMessage()}");
            }
        }
        
        $this->info("Successfully unsuspended {$unsuspendedCount} user(s).");
        
        return 0;
    }
}
