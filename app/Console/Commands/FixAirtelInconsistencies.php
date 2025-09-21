<?php

namespace App\Console\Commands;

use App\Models\Trade;
use App\Models\UserShare;
use App\Models\UserSharePair;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAirtelInconsistencies extends Command
{
    protected $signature = 'fix:airtel-inconsistencies';
    protected $description = 'Fix Airtel share calculation inconsistencies caused by previous bugs';

    public function handle()
    {
        $this->line('🔧 Fixing Airtel share calculation inconsistencies...');
        
        try {
            DB::beginTransaction();
            
            // Step 1: Analyze current state
            $this->analyzeCurrentState();
            
            // Step 2: Fix failed buyer shares
            $this->fixFailedBuyerShares();
            
            // Step 3: Fix seller share hold quantities
            $this->fixSellerHoldQuantities();
            
            // Step 4: Recalculate and verify
            $this->verifyFixes();
            
            DB::commit();
            $this->info('✅ Airtel share inconsistencies fixed successfully!');
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function analyzeCurrentState()
    {
        $this->info('📊 Current Airtel Shares Analysis:');
        
        $airtelTrade = Trade::where('name', 'like', '%Airtel%')->first();
        $airtelShares = UserShare::where('trade_id', $airtelTrade->id)->get();

        $totalIssued = $airtelShares->sum('share_will_get');
        $totalAvailable = $airtelShares->sum('total_share_count');
        $totalHold = $airtelShares->sum('hold_quantity');
        $totalSold = $airtelShares->sum('sold_quantity');
        $totalAccounted = $totalAvailable + $totalHold + $totalSold;

        $this->line('- Total issued: ' . number_format($totalIssued));
        $this->line('- Available: ' . number_format($totalAvailable));
        $this->line('- Hold: ' . number_format($totalHold));
        $this->line('- Sold: ' . number_format($totalSold));
        $this->line('- Total accounted: ' . number_format($totalAccounted));
        $this->line('- Missing: ' . number_format($totalIssued - $totalAccounted));
        $this->line('');
    }

    private function fixFailedBuyerShares()
    {
        $this->info('🔍 Fixing failed buyer shares...');
        
        $airtelTrade = Trade::where('name', 'like', '%Airtel%')->first();
        
        // Get failed buyer shares that have zero quantities but expected shares
        $failedBuyerShares = UserShare::where('trade_id', $airtelTrade->id)
            ->where('status', 'failed')
            ->where('share_will_get', '>', 0)
            ->where('total_share_count', 0)
            ->where('hold_quantity', 0)
            ->where('sold_quantity', 0)
            ->get();

        foreach ($failedBuyerShares as $failedShare) {
            $this->line('Processing failed share: ' . $failedShare->ticket_no);
            
            // Get the pairings for this failed share
            $pairings = $failedShare->pairedShares;
            
            foreach ($pairings as $pairing) {
                $sellerShare = $pairing->pairedShare;
                
                if ($pairing->is_paid == 0 && $pairing->share > 0) {
                    // This pairing was never paid, shares should be returned to seller
                    $this->line('  - Returning ' . $pairing->share . ' shares to seller ' . $sellerShare->ticket_no);
                    
                    // Move shares from seller's hold back to available
                    if ($sellerShare->hold_quantity >= $pairing->share) {
                        $sellerShare->hold_quantity -= $pairing->share;
                        $sellerShare->total_share_count += $pairing->share;
                        $sellerShare->save();
                        
                        // Mark pairing as failed
                        $pairing->is_paid = 2; // 2 = failed
                        $pairing->save();
                        
                        $this->info('    ✅ Returned ' . $pairing->share . ' shares to seller');
                    } else {
                        $this->warn('    ⚠️  Seller hold quantity insufficient: has ' . $sellerShare->hold_quantity . ', needs ' . $pairing->share);
                    }
                } elseif ($pairing->is_paid == 1) {
                    // This pairing was paid but buyer failed - buyer should get the shares
                    $allocatedShares = $pairing->share;
                    $failedShare->total_share_count += $allocatedShares;
                    $failedShare->status = 'completed'; // Change from failed to completed since payment was made
                    $failedShare->save();
                    
                    $this->info('    ✅ Buyer had paid - allocated ' . $allocatedShares . ' shares and changed status to completed');
                }
            }
        }
    }

    private function fixSellerHoldQuantities()
    {
        $this->info('🔍 Recalculating seller hold quantities...');
        
        $airtelTrade = Trade::where('name', 'like', '%Airtel%')->first();
        
        // Get all seller shares
        $sellerShares = UserShare::where('trade_id', $airtelTrade->id)
            ->where('status', 'ready to sell')
            ->get();

        foreach ($sellerShares as $sellerShare) {
            $this->line('Recalculating hold for seller: ' . $sellerShare->ticket_no);
            
            // Calculate required hold quantity based on active unpaid pairings
            $requiredHold = UserSharePair::where('paired_user_share_id', $sellerShare->id)
                ->where('is_paid', 0)
                ->whereHas('userShare', function($query) {
                    $query->where('status', 'paired'); // Only count active paired buyers
                })
                ->sum('share');
            
            $currentHold = $sellerShare->hold_quantity;
            $currentAvailable = $sellerShare->total_share_count;
            
            $this->line('  Current hold: ' . number_format($currentHold));
            $this->line('  Required hold: ' . number_format($requiredHold));
            $this->line('  Current available: ' . number_format($currentAvailable));
            
            if ($requiredHold != $currentHold) {
                $difference = $requiredHold - $currentHold;
                
                if ($difference > 0) {
                    // Need more in hold
                    if ($currentAvailable >= $difference) {
                        $sellerShare->hold_quantity = $requiredHold;
                        $sellerShare->total_share_count = $currentAvailable - $difference;
                        $sellerShare->save();
                        $this->info('    ✅ Moved ' . number_format($difference) . ' shares to hold');
                    } else {
                        $this->warn('    ⚠️  Insufficient available shares to move to hold');
                    }
                } else {
                    // Need less in hold (move some back to available)
                    $toMove = abs($difference);
                    $sellerShare->hold_quantity = $requiredHold;
                    $sellerShare->total_share_count = $currentAvailable + $toMove;
                    $sellerShare->save();
                    $this->info('    ✅ Moved ' . number_format($toMove) . ' shares back to available');
                }
            } else {
                $this->line('    ✅ Hold quantity is correct');
            }
        }
    }

    private function verifyFixes()
    {
        $this->info('🔍 Verifying fixes...');
        
        $airtelTrade = Trade::where('name', 'like', '%Airtel%')->first();
        $airtelShares = UserShare::where('trade_id', $airtelTrade->id)->get();

        // Recalculate totals
        $totalIssued = $airtelShares->sum('share_will_get');
        $totalAvailable = $airtelShares->sum('total_share_count');
        $totalHold = $airtelShares->sum('hold_quantity');
        $totalSold = $airtelShares->sum('sold_quantity');
        $totalAccounted = $totalAvailable + $totalHold + $totalSold;
        $discrepancy = $totalIssued - $totalAccounted;

        $this->line('');
        $this->info('📊 FINAL VERIFICATION:');
        $this->line('- Total issued: ' . number_format($totalIssued));
        $this->line('- Available: ' . number_format($totalAvailable));
        $this->line('- Hold: ' . number_format($totalHold));
        $this->line('- Sold: ' . number_format($totalSold));
        $this->line('- Total accounted: ' . number_format($totalAccounted));
        $this->line('- Discrepancy: ' . number_format($discrepancy));

        if ($discrepancy == 0) {
            $this->info('✅ Perfect! All shares are now accounted for.');
        } else {
            $this->warn('⚠️  Still have a discrepancy of ' . number_format($discrepancy) . ' shares');
            
            // If there's still a discrepancy, it might be due to profit shares not being accounted for
            $totalProfitShares = $airtelShares->sum('profit_share');
            if ($totalProfitShares > 0) {
                $this->line('- Total profit shares: ' . number_format($totalProfitShares));
                $adjustedTotal = $totalAccounted + $totalProfitShares;
                $adjustedDiscrepancy = $totalIssued - $adjustedTotal;
                $this->line('- Adjusted total (with profit): ' . number_format($adjustedTotal));
                $this->line('- Adjusted discrepancy: ' . number_format($adjustedDiscrepancy));
            }
        }

        // Check pairing consistency
        $this->checkPairingConsistency();
    }

    private function checkPairingConsistency()
    {
        $this->info('🔍 Checking pairing consistency...');
        
        $airtelTrade = Trade::where('name', 'like', '%Airtel%')->first();
        $issues = 0;
        
        $activePairings = UserSharePair::whereHas('userShare', function($query) use ($airtelTrade) {
            $query->where('trade_id', $airtelTrade->id)
                  ->where('status', 'paired');
        })->where('is_paid', 0)->get();

        foreach ($activePairings as $pairing) {
            $buyerShare = $pairing->userShare;
            $sellerShare = $pairing->pairedShare;
            
            if ($sellerShare->hold_quantity < $pairing->share) {
                $this->error('  Pairing issue: ' . $buyerShare->ticket_no . ' -> ' . 
                           $sellerShare->ticket_no . ' (needs ' . $pairing->share . 
                           ', seller has ' . $sellerShare->hold_quantity . ' in hold)');
                $issues++;
            }
        }

        if ($issues == 0) {
            $this->info('✅ All pairings are consistent');
        } else {
            $this->warn('⚠️  Found ' . $issues . ' pairing consistency issues');
        }
    }
}