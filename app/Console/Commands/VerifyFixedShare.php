<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use Illuminate\Console\Command;

class VerifyFixedShare extends Command
{
    protected $signature = 'verify:fixed-share';
    protected $description = 'Verify the fixed buyer share AB-17584266348515';

    public function handle()
    {
        $this->line('🔍 Verifying fixed share AB-17584266348515...');
        
        $share = UserShare::where('ticket_no', 'AB-17584266348515')->first();
        if (!$share) {
            $this->error('Share not found!');
            return 1;
        }

        $pairing = $share->pairedShares->first();
        $sellerShare = $pairing->pairedShare;

        $this->line('');
        $this->info('BUYER SHARE (AB-17584266348515):');
        $this->line('- Status: ' . $share->status);
        $this->line('- Timer paused: ' . ($share->timer_paused ? 'YES' : 'NO'));
        $this->line('- Has payments: ' . ($share->payments()->exists() ? 'YES' : 'NO'));

        $this->line('');
        $this->info('PAIRING STATUS:');
        $this->line('- Pairing ID: ' . $pairing->id);
        $this->line('- Is paid: ' . $pairing->is_paid);
        $this->line('- Share amount: ' . $pairing->share);

        $this->line('');
        $this->info('SELLER SHARE (Danny):');
        $this->line('- Ticket: ' . $sellerShare->ticket_no);
        $this->line('- Owner: ' . $sellerShare->user->name);
        $this->line('- Total available: ' . $sellerShare->total_share_count);
        $this->line('- Hold quantity: ' . $sellerShare->hold_quantity);

        $this->line('');
        $this->info('VERIFICATION RESULT:');
        if ($share->status === 'paired' && $pairing->is_paid === 0 && $sellerShare->hold_quantity >= $pairing->share) {
            $this->info('✅ SUCCESS: Trade is properly restored!');
            $this->line('   - Buyer shows as paired (awaiting payment confirmation)');
            $this->line('   - Seller shares are properly reserved in hold quantity');
            $this->line('   - Pairing shows awaiting confirmation (is_paid = 0)');
        } else {
            $this->error('❌ Issue found in restoration');
        }

        return 0;
    }
}