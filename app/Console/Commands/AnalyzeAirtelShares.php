<?php

namespace App\Console\Commands;

use App\Models\Trade;
use App\Models\UserShare;
use App\Models\UserSharePair;
use Illuminate\Console\Command;

class AnalyzeAirtelShares extends Command
{
    protected $signature = 'analyze:airtel-shares';
    protected $description = 'Analyze and fix Airtel share inconsistencies';

    public function handle()
    {
        $this->line('🔍 Analyzing Airtel shares for inconsistencies...');
        
        // Get the Airtel trade
        $airtelTrade = Trade::where('name', 'like', '%Airtel%')->first();
        if (!$airtelTrade) {
            $this->error('Airtel trade not found!');
            return 1;
        }

        $this->info('AIRTEL TRADE INFO:');
        $this->line('- Trade ID: ' . $airtelTrade->id);
        $this->line('- Trade Name: ' . $airtelTrade->name);
        $this->line('- Price per share: ' . $airtelTrade->price);
        $this->line('');

        // Get all Airtel shares
        $airtelShares = UserShare::where('trade_id', $airtelTrade->id)->get();

        $this->info('AIRTEL SHARES SUMMARY:');
        $this->line('- Total Airtel share records: ' . $airtelShares->count());
        $this->line('');

        // Group by status
        $byStatus = $airtelShares->groupBy('status');
        foreach ($byStatus as $status => $shares) {
            $this->line('- ' . ucfirst($status) . ' shares: ' . $shares->count());
        }
        $this->line('');

        // Calculate totals
        $totalSharesIssued = $airtelShares->sum('share_will_get');
        $totalCurrentShares = $airtelShares->sum('total_share_count');
        $totalHoldShares = $airtelShares->sum('hold_quantity');
        $totalSoldShares = $airtelShares->sum('sold_quantity');

        $this->info('SHARE QUANTITY ANALYSIS:');
        $this->line('- Total shares originally issued (share_will_get): ' . number_format($totalSharesIssued));
        $this->line('- Current available shares (total_share_count): ' . number_format($totalCurrentShares));
        $this->line('- Shares on hold (hold_quantity): ' . number_format($totalHoldShares));
        $this->line('- Sold shares (sold_quantity): ' . number_format($totalSoldShares));
        $this->line('- Total accounted shares: ' . number_format($totalCurrentShares + $totalHoldShares + $totalSoldShares));
        $this->line('');

        // Check for discrepancies
        $discrepancy = $totalSharesIssued - ($totalCurrentShares + $totalHoldShares + $totalSoldShares);
        $this->info('CONSISTENCY CHECK:');
        $this->line('- Expected total shares: ' . number_format($totalSharesIssued));
        $this->line('- Actual accounted shares: ' . number_format($totalCurrentShares + $totalHoldShares + $totalSoldShares));
        $this->line('- Discrepancy: ' . number_format($discrepancy));

        if ($discrepancy != 0) {
            $this->warn('⚠️  INCONSISTENCY DETECTED!');
            $this->line('');
            
            // Analyze specific inconsistencies
            $this->analyzeDetailedInconsistencies($airtelShares);
        } else {
            $this->info('✅ Shares are consistent!');
        }

        return 0;
    }

    private function analyzeDetailedInconsistencies($airtelShares)
    {
        $this->info('DETAILED ANALYSIS:');
        
        // Check each share for issues
        $problematicShares = [];
        
        foreach ($airtelShares as $share) {
            $issues = [];
            
            // Check if available + hold + sold equals share_will_get + profit
            $expectedTotal = $share->share_will_get + ($share->profit_share ?? 0);
            $actualTotal = $share->total_share_count + $share->hold_quantity + $share->sold_quantity;
            
            if ($expectedTotal != $actualTotal) {
                $issues[] = "Share mismatch: Expected {$expectedTotal}, Actual {$actualTotal}";
            }
            
            // Check for negative values
            if ($share->total_share_count < 0) {
                $issues[] = "Negative available shares: {$share->total_share_count}";
            }
            if ($share->hold_quantity < 0) {
                $issues[] = "Negative hold quantity: {$share->hold_quantity}";
            }
            if ($share->sold_quantity < 0) {
                $issues[] = "Negative sold quantity: {$share->sold_quantity}";
            }
            
            if (!empty($issues)) {
                $problematicShares[] = [
                    'share' => $share,
                    'issues' => $issues
                ];
            }
        }
        
        if (empty($problematicShares)) {
            $this->info('✅ No specific share-level inconsistencies found');
        } else {
            $this->warn('Found ' . count($problematicShares) . ' shares with issues:');
            foreach ($problematicShares as $problem) {
                $share = $problem['share'];
                $this->line('');
                $this->line('Share: ' . $share->ticket_no . ' (' . $share->user->name . ')');
                $this->line('Status: ' . $share->status);
                $this->line('Available: ' . $share->total_share_count);
                $this->line('Hold: ' . $share->hold_quantity);
                $this->line('Sold: ' . $share->sold_quantity);
                $this->line('Share will get: ' . $share->share_will_get);
                $this->line('Profit: ' . ($share->profit_share ?? 0));
                foreach ($problem['issues'] as $issue) {
                    $this->error('  - ' . $issue);
                }
            }
        }
        
        // Check pairing consistency
        $this->line('');
        $this->info('PAIRING CONSISTENCY CHECK:');
        $this->checkPairingConsistency();
    }
    
    private function checkPairingConsistency()
    {
        // Get all Airtel-related pairings
        $airtelTrade = Trade::where('name', 'like', '%Airtel%')->first();
        $airtelBuyerShares = UserShare::where('trade_id', $airtelTrade->id)
            ->whereIn('status', ['paired', 'failed', 'completed'])
            ->get();
            
        $pairingIssues = 0;
        
        foreach ($airtelBuyerShares as $buyerShare) {
            $pairings = $buyerShare->pairedShares;
            
            foreach ($pairings as $pairing) {
                $sellerShare = $pairing->pairedShare;
                
                // Check if seller has enough hold quantity for this pairing
                if ($pairing->is_paid == 0 && $pairing->share > 0) {
                    // This pairing should have shares in hold
                    if ($sellerShare->hold_quantity < $pairing->share) {
                        $this->error('Pairing inconsistency: Buyer ' . $buyerShare->ticket_no . 
                                   ' paired with Seller ' . $sellerShare->ticket_no . 
                                   ' for ' . $pairing->share . ' shares, but seller only has ' . 
                                   $sellerShare->hold_quantity . ' in hold');
                        $pairingIssues++;
                    }
                }
            }
        }
        
        if ($pairingIssues == 0) {
            $this->info('✅ All pairings are consistent');
        } else {
            $this->warn('Found ' . $pairingIssues . ' pairing inconsistencies');
        }
    }
}