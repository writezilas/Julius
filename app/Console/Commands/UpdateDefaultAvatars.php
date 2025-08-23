<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class UpdateDefaultAvatars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'avatars:update-defaults {--dry-run : Show what would be updated without actually updating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing users with old default avatars to use the new default avatar';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        // Define old default avatar paths that should be updated
        $oldDefaultAvatars = [
            'assets/images/users/default.png',
            'assets/images/users/avatar-1.jpg',
            'assets/images/users/avatar-2.jpg',
            'assets/images/users/avatar-3.jpg',
            'assets/images/users/avatar-4.jpg',
            'assets/images/users/avatar-5.jpg',
            'assets/images/users/avatar-6.jpg',
            'assets/images/users/avatar-7.jpg',
            'assets/images/users/avatar-8.jpg',
            'assets/images/users/avatar-9.jpg',
            'assets/images/users/avatar-10.jpg',
        ];

        // Also include users with null or empty avatars
        $usersWithOldDefaults = User::where(function($query) use ($oldDefaultAvatars) {
            $query->whereIn('avatar', $oldDefaultAvatars)
                  ->orWhereNull('avatar')
                  ->orWhere('avatar', '');
        })->get();

        if ($usersWithOldDefaults->isEmpty()) {
            $this->info('No users found with old default avatars.');
            return Command::SUCCESS;
        }

        $this->info("Found {$usersWithOldDefaults->count()} users with old default avatars:");
        $this->table(
            ['ID', 'Name', 'Username', 'Email', 'Current Avatar'],
            $usersWithOldDefaults->map(function ($user) {
                return [
                    $user->id,
                    $user->name,
                    $user->username,
                    $user->email,
                    $user->avatar ?: 'NULL'
                ];
            })
        );

        if ($isDryRun) {
            $this->warn('DRY RUN: No changes were made. Remove --dry-run flag to actually update users.');
            return Command::SUCCESS;
        }

        if (!$this->confirm('Do you want to update these users to use the new default avatar (images/default.jpg)?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $updatedCount = 0;
        foreach ($usersWithOldDefaults as $user) {
            $oldAvatar = $user->avatar ?: 'NULL';
            $user->avatar = ''; // Set to empty string so the new default logic will kick in
            $user->save();
            $updatedCount++;
            
            $this->line("Updated user {$user->username} (ID: {$user->id}) from '{$oldAvatar}' to '' (will use new default)");
        }

        $this->info("Successfully updated {$updatedCount} users to use the new default avatar.");
        $this->info("These users will now see 'images/default.jpg' as their profile picture.");

        return Command::SUCCESS;
    }
}
