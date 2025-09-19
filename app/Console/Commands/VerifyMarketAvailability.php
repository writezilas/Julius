<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use App\Models\User;
use App\Models\Trade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class VerifyMarketAvailability extends Command
{
    protected $signature = 'shares:verify-market-availability {--fix : Fix any issues found}';
    protected $description = 'Verify that users only see others shares in market, not their own';

    public function handle()
    {
        $this->info('🔍 VERIFYING MARKET AVAILABILITY FOR ALL USERS');
        $this->info('=============================================');
        $this->newLine();

        $fix = $this->option('fix');
        
        if ($fix) {
            $this->warn('⚙️  FIX MODE ENABLED - Issues will be automatically resolved');
            $this->newLine();
        }

        // Get all active users
        $users = User::whereIn('status', ['active'])->get();
        
        if ($users->isEmpty()) {
            $this->error('No active users found!');
            return 1;
        }

        // Get all active trades
        $trades = Trade::where('status', '1')->get();
        
        if ($trades->isEmpty()) {
            $this->error('No active trades found!');
            return 1;
        }

        $issues = 0;
        
        foreach ($trades as $trade) {
            $this->line("📊 Checking Trade: {$trade->name} (ID: {$trade->id})");
            $this->line(str_repeat('-', 50));
            
            // Get all available shares for this trade
            $allShares = UserShare::where('trade_id', $trade->id)
                ->where('status', 'completed')
                ->where('is_ready_to_sell', 1)
                ->where('total_share_count', '>', 0)
                ->with('user')
                ->get();
            
            if ($allShares->isEmpty()) {
                $this->warn("  ⚠️  No shares available for trade {$trade->name}");
                continue;
            }
            
            $this->line("  Total available shares in market: " . number_format($allShares->sum('total_share_count')));
            
            foreach ($users as $user) {
                // Test what this user should see
                $userVisibleShares = UserShare::where('trade_id', $trade->id)
                    ->where('status', 'completed')
                    ->where('is_ready_to_sell', 1)
                    ->where('total_share_count', '>', 0)
                    ->where('user_id', '!=', $user->id) // Exclude user's own shares
                    ->whereHas('user', function($query) {
                        $query->whereIn('status', ['active']);
                    })
                    ->sum('total_share_count');
                
                // Test using helper function
                Auth::login($user);
                $helperResult = checkAvailableSharePerTrade($trade->id);
                Auth::logout();
                
                $userOwnShares = $allShares->where('user_id', $user->id)->sum('total_share_count');
                
                $this->line("  👤 {$user->name} (ID: {$user->id}):");
                $this->line("    Own shares: " . number_format($userOwnShares));
                $this->line("    Should see: " . number_format($userVisibleShares) . " shares");
                $this->line("    Helper returns: " . number_format($helperResult) . " shares");
                
                if ($userVisibleShares != $helperResult) {
                    $this->error("    ❌ MISMATCH! Helper function not working correctly");
                    $issues++;
                    
                    if ($fix) {
                        $this->warn("    🔧 Clearing application cache...");
                        $this->call('cache:clear');
                        $this->call('config:clear');
                        $this->call('view:clear');
                        $this->info("    ✅ Cache cleared");
                    }
                } elseif ($userVisibleShares == $allShares->sum('total_share_count')) {
                    $this->error("    ❌ User seeing ALL shares including their own!");
                    $issues++;
                } else {
                    $this->info("    ✅ Working correctly");
                }
            }
            
            $this->newLine();
        }
        
        // Summary
        $this->info('📋 VERIFICATION SUMMARY');
        $this->info('======================');
        
        if ($issues === 0) {
            $this->info('✅ All users are correctly seeing only other users\' shares!');
            $this->info('✅ No issues found - market availability is working properly');
        } else {
            $this->error("❌ Found {$issues} issues with market availability");
            
            if (!$fix) {
                $this->warn('💡 Run with --fix flag to attempt automatic resolution');
            } else {
                $this->info('🔧 Attempted to fix issues by clearing caches');
            }
        }
        
        return $issues === 0 ? 0 : 1;
    }
}