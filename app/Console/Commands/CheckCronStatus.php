<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckCronStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if cron jobs are working and identify any issues with payment expiry processing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 CRON STATUS CHECK');
        $this->newLine();

        // Check for expired payments that should have been processed
        $expiredShares = UserShare::whereStatus('paired')
            ->where('balance', 0)
            ->get()
            ->filter(function ($share) {
                $deadlineMinutes = $share->payment_deadline_minutes ?? 60;
                $timeoutTime = Carbon::parse($share->created_at)->addMinutes($deadlineMinutes);
                return $timeoutTime < Carbon::now();
            });

        if ($expiredShares->isEmpty()) {
            $this->info('✅ No expired payments found - cron is working correctly');
        } else {
            $this->error("❌ Found {$expiredShares->count()} expired payments that should have been processed!");
            $this->newLine();
            
            $this->warn('⚠️ These trades are overdue and need immediate attention:');
            foreach ($expiredShares as $share) {
                $deadlineMinutes = $share->payment_deadline_minutes ?? 60;
                $timeoutTime = Carbon::parse($share->created_at)->addMinutes($deadlineMinutes);
                $minutesOverdue = Carbon::now()->diffInMinutes($timeoutTime);
                
                $this->line("  📋 {$share->ticket_no} - User: {$share->user_id}, Overdue: {$minutesOverdue} minutes");
            }
            
            $this->newLine();
            $this->warn('💡 Run the following command to fix these immediately:');
            $this->line('   php artisan paymentfailedshare:cron');
            $this->newLine();
            $this->warn('🔧 Or use the enhanced processor:');
            $this->line('   php artisan payments:process-expired');
        }

        // Check recent log entries for cron execution
        $this->info('📊 RECENT ACTIVITY:');
        
        // Get the last few failed shares to see if processing is happening
        $recentFailedShares = UserShare::where('status', 'failed')
            ->where('updated_at', '>=', Carbon::now()->subHours(24))
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
            
        if ($recentFailedShares->count() > 0) {
            $this->info("✅ {$recentFailedShares->count()} payments were marked as failed in the last 24 hours:");
            foreach ($recentFailedShares as $share) {
                $this->line("  📋 {$share->ticket_no} failed at: {$share->updated_at}");
            }
        } else {
            $this->warn('⚠️ No payments have been marked as failed in the last 24 hours');
        }

        // Check if MicroScheduler might be running
        $this->newLine();
        $this->info('🤖 AUTOMATION STATUS:');
        $this->line('To ensure continuous processing, make sure one of these is running:');
        $this->line('  1. MicroScheduler: php artisan schedule:micro');
        $this->line('  2. System cron job calling: php artisan paymentfailedshare:cron');
        $this->line('  3. Laravel scheduler: php artisan schedule:run (every minute)');

        return $expiredShares->isEmpty() ? 0 : 1;
    }
}