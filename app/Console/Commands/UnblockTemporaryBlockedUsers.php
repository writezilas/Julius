<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UnblockTemporaryBlockedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unblockTemporaryBlockedUsers:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        unblockTemporaryBlockedUsers();
        $this->info('Update temporary block user status cron Cummand Run successfully! '. now());
    }
}
