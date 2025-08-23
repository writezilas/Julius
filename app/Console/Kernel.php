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
        $schedule->command('sharematured:cron')->everyTwoMinutes()->timezone(env('APP_TIMEZONE'))->sendOutputTo(storage_path()."/logs/cron.log", true);
        $schedule->command('paymentfailedshare:cron')->everyTwoMinutes()->timezone(env('APP_TIMEZONE'))->sendOutputTo(storage_path()."/logs/paymentfailedforshare.log", true);
    $schedule->command('unblockTemporaryBlockedUsers:cron')->everyTwoMinutes()->timezone(env('APP_TIMEZONE'))->sendOutputTo(storage_path()."/logs/unblockTemporaryBlockedUsers.log", true);
        $schedule->command('update-shares')->everyMinute()->timezone(env('APP_TIMEZONE'))->sendOutputTo(storage_path()."/logs/update-shares.log", true);
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
