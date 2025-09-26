<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Traditional minute-based scheduling (commented out for second-level scheduling)
        // $schedule->command('sharematured:cron')->everyTwoMinutes()->timezone(env('APP_TIMEZONE'))->sendOutputTo(storage_path()."/logs/cron.log", true);
        // $schedule->command('paymentfailedshare:cron')->everyTwoMinutes()->timezone(env('APP_TIMEZONE'))->sendOutputTo(storage_path()."/logs/paymentfailedforshare.log", true);
        // $schedule->command('unblockTemporaryBlockedUsers:cron')->everyTwoMinutes()->timezone(env('APP_TIMEZONE'))->sendOutputTo(storage_path()."/logs/unblockTemporaryBlockedUsers.log", true);
        // $schedule->command('update-shares')->everyMinute()->timezone(env('APP_TIMEZONE'))->sendOutputTo(storage_path()."/logs/update-shares.log", true);
        
        // Second-level scheduling: Run micro-scheduler every minute to handle second-level intervals
        $schedule->command('schedule:micro --duration=60 --interval=1')
                 ->everyMinute()
                 ->timezone(env('APP_TIMEZONE'))
                 ->sendOutputTo(storage_path()."/logs/micro-scheduler.log", true)
                 ->description('Micro-scheduler for second-level command execution');
                 
        // Alternative: Individual second-level commands (if needed)
        // These will run every minute but execute multiple times within that minute
        $schedule->command('sharematured:cron')->everyMinute()->timezone(env('APP_TIMEZONE'))->sendOutputTo(storage_path()."/logs/cron.log", true);
        $schedule->command('paymentfailedshare:cron')->everyMinute()->timezone(env('APP_TIMEZONE'))->sendOutputTo(storage_path()."/logs/paymentfailedforshare.log", true);
        $schedule->command('unblockTemporaryBlockedUsers:cron')->everyMinute()->timezone(env('APP_TIMEZONE'))->sendOutputTo(storage_path()."/logs/unblockTemporaryBlockedUsers.log", true);
        $schedule->command('update-shares')->everyMinute()->timezone(env('APP_TIMEZONE'))->sendOutputTo(storage_path()."/logs/update-shares.log", true);
        
        // Suspension management tasks
        $schedule->command('suspension:manage --lift-expired')
                 ->everyMinute()
                 ->timezone(env('APP_TIMEZONE'))
                 ->sendOutputTo(storage_path()."/logs/suspension-management.log", true)
                 ->description('Process expired user suspensions');
        
        // Reset suspension levels for users with good payment history (daily)
        $schedule->command('suspension:manage --reset-levels')
                 ->daily()
                 ->timezone(env('APP_TIMEZONE'))
                 ->sendOutputTo(storage_path()."/logs/suspension-level-resets.log", true)
                 ->description('Reset suspension levels for users with good payment history');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
