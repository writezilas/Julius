<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use Illuminate\Support\Facades\DB;

class FixFailedShares extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shares:fix-failed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix shares that were incorrectly marked as failed but have valid pairing records';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting to fix incorrectly failed shares...');
        
        // Find all shares that have 'failed' status but valid pairing records
        $affectedShares = DB::table('user_shares')
            ->join('user_share_pairs', 'user_shares.id', '=', 'user_share_pairs.user_share_id')
            ->where('user_shares.status', 'failed')
            ->select('user_shares.id', 'user_shares.trade_id', 'user_shares.status', 'user_shares.created_at')
            ->get();

        if ($affectedShares->count() == 0) {
            $this->info('No affected shares found.');
            return 0;
        }

        $this->info('Found ' . $affectedShares->count() . ' shares with failed status but valid pairing records.');

        foreach ($affectedShares as $affectedShare) {
            $share = UserShare::find($affectedShare->id);
            
            if (!$share) {
                $this->warn('Share ID ' . $affectedShare->id . ' not found, skipping...');
                continue;
            }

            // Check if there are any payment records (if yes, the failure might be legitimate)
            $hasPaymentRecords = DB::table('user_share_payments')
                ->where('user_share_id', $share->id)
                ->exists();

            if ($hasPaymentRecords) {
                $this->warn('Share ID ' . $share->id . ' has payment records, skipping (failure might be legitimate)...');
                continue;
            }

            // Update status back to paired
            $share->status = 'paired';
            $share->save();
            
            $this->info('Updated Share ID ' . $share->id . ' (Ticket: ' . $share->ticket_no . ') from failed to paired status.');
        }

        $this->info('Fix complete!');
        return 0;
    }
}
