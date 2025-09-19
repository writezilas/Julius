<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use Illuminate\Console\Command;
use Carbon\Carbon;

class TestPaymentExpiryLogic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:payment-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the payment expiry logic to ensure it works with individual share payment deadlines';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🧪 Testing Payment Expiry Logic...');
        $this->newLine();

        // Test with different payment deadline scenarios
        $testCases = [
            ['deadline_minutes' => 3, 'created_minutes_ago' => 5], // Should expire
            ['deadline_minutes' => 60, 'created_minutes_ago' => 5], // Should not expire
            ['deadline_minutes' => 30, 'created_minutes_ago' => 35], // Should expire
            ['deadline_minutes' => 15, 'created_minutes_ago' => 10], // Should not expire
        ];

        foreach ($testCases as $index => $testCase) {
            $this->info("Test Case " . ($index + 1) . ":");
            $this->line("  ⏱️ Deadline: {$testCase['deadline_minutes']} minutes");
            $this->line("  📅 Created: {$testCase['created_minutes_ago']} minutes ago");
            
            // Simulate the logic from updatePaymentFailedShareStatus
            $createdTime = Carbon::now()->subMinutes($testCase['created_minutes_ago']);
            $timeoutTime = $createdTime->addMinutes($testCase['deadline_minutes']);
            $isExpired = $timeoutTime < Carbon::now();
            
            if ($isExpired) {
                $overdue = Carbon::now()->diffInMinutes($timeoutTime);
                $this->error("  ❌ EXPIRED (overdue by {$overdue} minutes)");
            } else {
                $remaining = $timeoutTime->diffInMinutes(Carbon::now());
                $this->info("  ✅ NOT EXPIRED ({$remaining} minutes remaining)");
            }
            
            $this->newLine();
        }

        // Test with real data from database
        $this->info('🔍 Checking current paired shares in database...');
        $pairedShares = UserShare::whereStatus('paired')->get();
        
        if ($pairedShares->isEmpty()) {
            $this->info('✅ No paired shares found in database');
        } else {
            $this->info("Found {$pairedShares->count()} paired share(s):");
            
            foreach ($pairedShares as $share) {
                $deadlineMinutes = $share->payment_deadline_minutes ?? 60;
                $timeoutTime = Carbon::parse($share->created_at)->addMinutes($deadlineMinutes);
                $isExpired = $timeoutTime < Carbon::now();
                
                $this->line("  📝 {$share->ticket_no} (User: {$share->user_id}):");
                $this->line("    📅 Created: {$share->created_at}");
                $this->line("    ⏱️ Deadline: {$deadlineMinutes} minutes");
                $this->line("    ⌛ Timeout: {$timeoutTime}");
                
                if ($isExpired) {
                    $overdue = Carbon::now()->diffInMinutes($timeoutTime);
                    $this->error("    ❌ EXPIRED (overdue by {$overdue} minutes) - Should be failed!");
                } else {
                    $remaining = $timeoutTime->diffInMinutes(Carbon::now());
                    $this->info("    ✅ Active ({$remaining} minutes remaining)");
                }
                
                $this->newLine();
            }
        }

        return 0;
    }
}
