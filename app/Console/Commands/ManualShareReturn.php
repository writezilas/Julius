<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserShare;
use App\Models\UserSharePair;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ManualShareReturn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shares:manual-return {tickets?* : Specific ticket numbers to return shares for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually return shares to wallets for failed trades';

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
        $tickets = $this->argument('tickets');
        
        if (empty($tickets)) {
            $tickets = ['AB-17564479284995', 'AB-17564591752453', 'AB-17564602415878'];
            $this->info('Processing known failed trades: ' . implode(', ', $tickets));
        }

        $this->info('🔧 Starting manual share return process...');
        
        $processedCount = 0;
        $totalReturned = 0;
        
        foreach ($tickets as $ticketNumber) {
            $this->info("\n📋 Processing ticket: {$ticketNumber}");
            
            $share = UserShare::where('ticket_no', $ticketNumber)->first();
            
            if (!$share) {
                $this->error("❌ Share not found for ticket: {$ticketNumber}");
                continue;
            }
            
            if ($share->status !== 'failed') {
                $this->error("❌ Share status is '{$share->status}' - expected 'failed'. Skipping.");
                continue;
            }
            
            $this->displayShareInfo($share);
            
            $returnedShares = $this->processShareReturn($share);
            $totalReturned += $returnedShares;
            
            if ($returnedShares > 0) {
                $processedCount++;
            }
        }
        
        $this->info("\n✅ Processing complete!");
        $this->info("📊 Fixed {$processedCount} shares");
        $this->info("📈 Total shares returned: {$totalReturned}");
        
        return 0;
    }
    
    private function displayShareInfo($share)
    {
        $this->table(['Field', 'Value'], [
            ['Ticket', $share->ticket_no],
            ['Status', $share->status],
            ['User ID', $share->user_id],
            ['Created', $share->created_at],
            ['Paired Count', $share->pairedShares->count()],
        ]);
        
        if ($share->pairedShares->count() > 0) {
            $this->info("📝 Paired Shares Details:");
            foreach ($share->pairedShares as $paired) {
                $pairedShare = UserShare::find($paired->paired_user_share_id);
                if ($pairedShare) {
                    $this->info("  🔗 Share ID {$pairedShare->id} (Ticket: {$pairedShare->ticket_no})");
                    $this->info("      User: {$pairedShare->user_id}, Status: {$pairedShare->status}");
                    $this->info("      Hold: {$pairedShare->hold_quantity}, Total: {$pairedShare->total_share_count}, Sold: {$pairedShare->sold_quantity}");
                    $this->info("      Shares to return: {$paired->share}");
                }
            }
        }
    }
    
    private function processShareReturn($share)
    {
        $totalReturned = 0;
        
        try {
            DB::beginTransaction();
            
            foreach ($share->pairedShares as $pairedShare) {
                $userShare = UserShare::findOrFail($pairedShare->paired_user_share_id);
                
                $this->info("\n🔄 Processing paired share ID: {$userShare->id} (Ticket: {$userShare->ticket_no})");
                $this->info("   Before - Hold: {$userShare->hold_quantity}, Total: {$userShare->total_share_count}");
                
                $sharesToReturn = $pairedShare->share;
                
                // Check if we need to return shares (they might already be returned)
                $expectedHold = $userShare->hold_quantity + $sharesToReturn;
                $expectedTotal = $userShare->total_share_count - $sharesToReturn;
                
                // If hold_quantity is less than expected, shares need to be returned
                if ($userShare->hold_quantity < $sharesToReturn) {
                    $this->info("   ⚠️  Current hold ({$userShare->hold_quantity}) is less than shares to return ({$sharesToReturn})");
                    $this->info("   🔧 This indicates shares were not returned properly when trade failed");
                    
                    // Force return shares by adjusting both hold_quantity and total_share_count
                    // We'll add to total_share_count since these shares should be available
                    $userShare->total_share_count += $sharesToReturn;
                    
                    // Set hold_quantity to 0 since these shares should no longer be on hold
                    // (they were supposed to be released when the paired trade failed)
                    if ($userShare->hold_quantity >= $sharesToReturn) {
                        $userShare->hold_quantity -= $sharesToReturn;
                    } else {
                        // If hold_quantity is insufficient, just set it to 0
                        // This can happen if the hold was already partially released
                        $userShare->hold_quantity = 0;
                    }
                    
                    $userShare->save();
                    
                    $this->info("   ✅ Returned {$sharesToReturn} shares");
                    $this->info("   After  - Hold: {$userShare->hold_quantity}, Total: {$userShare->total_share_count}");
                    
                    $totalReturned += $sharesToReturn;
                    
                    Log::info("Manually returned {$sharesToReturn} shares to wallet for share ID: {$userShare->id} (ticket: {$userShare->ticket_no}) from failed trade {$share->ticket_no}");
                    
                } else {
                    $this->info("   ✅ Shares appear to already be properly handled (hold_quantity >= shares_to_return)");
                }
            }
            
            DB::commit();
            
            if ($totalReturned > 0) {
                $this->info("✅ Successfully returned {$totalReturned} shares for ticket {$share->ticket_no}");
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error processing share return for {$share->ticket_no}: " . $e->getMessage());
            Log::error("Error in ManualShareReturn for share {$share->id}: " . $e->getMessage());
        }
        
        return $totalReturned;
    }
}
