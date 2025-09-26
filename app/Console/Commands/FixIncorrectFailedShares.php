<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use Illuminate\Console\Command;

class FixIncorrectFailedShares extends Command
{
    protected $signature = 'fix:incorrect-failed-shares';
    protected $description = 'Fix shares that were incorrectly marked as failed despite having payments';

    public function handle()
    {
        $this->line('🔍 Searching for shares that were incorrectly marked as failed...');
        
        // Find shares that are failed but have payments and paused timers
        $incorrectlyFailedShares = UserShare::where('status', 'failed')
            ->where('timer_paused', true)
            ->whereHas('payments')
            ->get();
            
        $this->info('Found ' . $incorrectlyFailedShares->count() . ' incorrectly failed shares');
        
        foreach ($incorrectlyFailedShares as $share) {
            $this->line('Processing share: ' . $share->ticket_no);
            
            // Restore buyer share to paired status
            $share->status = 'paired';
            $share->save();
            
            // Reset pairing payment status to awaiting confirmation
            $pairings = $share->pairedShares;
            foreach ($pairings as $pairing) {
                if ($pairing->is_paid != 1) { // Only reset if not already confirmed
                    $pairing->is_paid = 0; // Back to awaiting confirmation
                    $pairing->save();
                    
                    // Move shares back from seller's available to hold quantity
                    $sellerShare = $pairing->pairedShare;
                    if ($sellerShare && $pairing->share > 0) {
                        // Check if shares were already returned to seller
                        if ($sellerShare->total_share_count >= $pairing->share) {
                            $sellerShare->decrement('total_share_count', $pairing->share);
                            $sellerShare->increment('hold_quantity', $pairing->share);
                            $this->info('  ✅ Moved ' . $pairing->share . ' shares back to hold for seller ' . $sellerShare->user->name);
                        } else {
                            $this->warn('  ⚠️  Seller does not have enough available shares to move to hold. Current: ' . $sellerShare->total_share_count . ', needed: ' . $pairing->share);
                        }
                    }
                }
            }
            
            $this->info('  ✅ Fixed share ' . $share->ticket_no . ' - status changed from failed to paired');
        }
        
        if ($incorrectlyFailedShares->count() == 0) {
            $this->info('✅ No incorrectly failed shares found - system is consistent!');
        } else {
            $this->info('✅ Fixed ' . $incorrectlyFailedShares->count() . ' incorrectly failed shares');
        }
        
        return 0;
    }
}