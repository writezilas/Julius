<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PaymentFailedSharesCorn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paymentfailedshare:cron';

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
        try {
            $this->line('🔍 Starting payment expiry check at: ' . now());
            
            updatePaymentFailedShareStatus();
            
            $this->info('✅ Payment failed cron command completed successfully! '. now());
            \Log::info('Payment failed cron executed successfully', [
                'timestamp' => now(),
                'command' => 'paymentfailedshare:cron'
            ]);
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error in payment failed cron: ' . $e->getMessage());
            \Log::error('Payment failed cron error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()
            ]);
            
            return 1;
        }
    }
}
