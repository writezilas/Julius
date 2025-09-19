<?php

namespace App\Services;

use App\Models\Trade;
use App\Models\UserShare;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProgressCalculationService
{
    /**
     * Compute the correct progress percentage for a trade
     * This handles the core logic for calculating how much of a trade has been completed
     * Formula: (bought shares / total market shares) * 100
     * Where: total market shares = available shares + bought shares
     *
     * @param int $tradeId
     * @return array
     */
    public function computeTradeProgress(int $tradeId): array
    {
        try {
            $trade = Trade::find($tradeId);
            
            if (!$trade) {
                Log::warning("Trade not found for progress calculation: {$tradeId}");
                return [
                    'progress_percentage' => 0,
                    'shares_bought' => 0,
                    'available_shares' => 1,
                    'total_shares' => 1,
                    'error' => 'Trade not found'
                ];
            }

            // Get total shares available for this trade (from trade quantity)
            $totalShares = $trade->quantity ?? 80000; // Default fallback
            
            // Count bought shares that are paid up and completed
            // This includes both market-bought shares (with paid pairs) and admin-allocated shares
            $marketBoughtShares = UserShare::where('trade_id', $tradeId)
                ->where('status', 'completed')
                ->where('get_from', 'purchase')
                ->whereHas('pairedShares', function($query) {
                    $query->where('is_paid', 1);
                })
                ->sum('share_will_get');
            
            // Count admin-allocated shares that are completed (they don't have paired shares)
            $adminAllocatedShares = UserShare::where('trade_id', $tradeId)
                ->where('status', 'completed')
                ->where('get_from', 'allocated-by-admin')
                ->sum('share_will_get');
            
            // Count referral bonus shares that are completed
            $referralBonusShares = UserShare::where('trade_id', $tradeId)
                ->where('status', 'completed')
                ->where('get_from', 'refferal-bonus')
                ->sum('share_will_get');
            
            // Total bought shares = market bought + admin allocated + referral bonus
            $boughtShares = $marketBoughtShares + $adminAllocatedShares + $referralBonusShares;

            // Get currently available shares (not yet bought)
            $availableShares = checkAvailableSharePerTrade($tradeId);
            
            // Calculate progress percentage using the correct logic
            $progressPercentage = 0;
            
            // Get total market shares (available + bought shares in the market)
            // This represents all shares that have been or could be traded in the market
            $totalMarketShares = $availableShares + $boughtShares;
            
            // Special case handling:
            if ($availableShares == 0 && $boughtShares == 0) {
                // No shares available and none bought = market hasn't started yet
                $progressPercentage = 0;
                Log::info("Trade {$tradeId}: No shares in market and none bought yet, setting progress to 0%");
            } elseif ($availableShares == 0 && $boughtShares > 0) {
                // Market is truly sold out (shares were bought and now unavailable)
                $progressPercentage = 100;
                Log::info("Trade {$tradeId}: Market sold out with {$boughtShares} shares bought, setting progress to 100%");
            } elseif ($totalMarketShares > 0) {
                // Active trading: calculate based on actual market shares (available + bought)
                // This shows what percentage of the available market has been purchased
                $progressPercentage = ($boughtShares / $totalMarketShares) * 100;
                Log::info("Trade {$tradeId}: Active market - {$boughtShares} bought out of {$totalMarketShares} total market shares");
            }
            
            // Ensure progress is between 0 and 100
            $progressPercentage = max(0, min($progressPercentage, 100));

            Log::info("Progress calculated for trade {$tradeId}: {$progressPercentage}% (market bought: {$marketBoughtShares}, admin allocated: {$adminAllocatedShares}, referral bonus: {$referralBonusShares}, total bought: {$boughtShares}, available: {$availableShares}, total market: {$totalMarketShares})");

            return [
                'progress_percentage' => round($progressPercentage, 2),
                'shares_bought' => $boughtShares,
                'market_bought_shares' => $marketBoughtShares,
                'admin_allocated_shares' => $adminAllocatedShares,
                'referral_bonus_shares' => $referralBonusShares,
                'available_shares' => $availableShares,
                'total_shares' => $totalShares, // Total trade capacity
                'total_market_shares' => $totalMarketShares, // Actual market size (available + bought)
                'trade_id' => $tradeId,
                'trade_name' => $trade->name
            ];

        } catch (\Exception $e) {
            Log::error("Error calculating progress for trade {$tradeId}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'progress_percentage' => 0,
                'shares_bought' => 0,
                'available_shares' => 0,
                'total_shares' => 1,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle progress restoration when a trade fails or payment is not completed
     * This is crucial for the progress bar fix - when trades fail or payments are not made, progress should be restored
     *
     * @param int $tradeId
     * @param int $failedShares
     * @param string $reason - Reason for failure (payment_failed, trade_failed, etc.)
     * @return array
     */
    public function handleFailedTradeProgressRestoration(int $tradeId, int $failedShares, string $reason = 'trade_failed'): array
    {
        try {
            Log::info("Handling failed trade progress restoration for trade {$tradeId}, failed shares: {$failedShares}, reason: {$reason}");

            // Get current progress before restoration
            $beforeProgress = $this->computeTradeProgress($tradeId);
            
            // Handle different failure scenarios
            if ($reason === 'payment_failed') {
                // When payment fails, we need to:
                // 1. Mark the share pair as unpaid (is_paid = 0)
                // 2. Return the shares to available status
                UserShare::where('trade_id', $tradeId)
                    ->whereHas('pairedShares', function($query) {
                        $query->where('is_paid', 0);
                    })
                    ->update(['status' => 'pending']); // Return to pending state
                    
                Log::info("Updated unpaid shares to pending status for trade {$tradeId}");
            }
            
            // Recalculate progress after the failure handling
            $afterProgress = $this->computeTradeProgress($tradeId);
            
            // Calculate the amount of progress restored (should be positive when progress increases)
            $progressRestored = $afterProgress['progress_percentage'] - $beforeProgress['progress_percentage'];
            
            Log::info("Progress restoration completed for trade {$tradeId}: restored {$progressRestored}% progress (from {$beforeProgress['progress_percentage']}% to {$afterProgress['progress_percentage']}%)");

            return [
                'success' => true,
                'trade_id' => $tradeId,
                'failed_shares' => $failedShares,
                'failure_reason' => $reason,
                'progress_before' => $beforeProgress['progress_percentage'],
                'progress_after' => $afterProgress['progress_percentage'],
                'progress_restored' => round($progressRestored, 2),
                'message' => "Successfully restored {$progressRestored}% progress for {$reason}"
            ];

        } catch (\Exception $e) {
            Log::error("Error handling failed trade progress restoration for trade {$tradeId}: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'trade_id' => $tradeId
            ];
        }
    }

    /**
     * Update progress when a trade completes successfully
     * This ensures consistent progress tracking
     *
     * @param int $tradeId
     * @param int $completedShares
     * @return array
     */
    public function handleTradeCompletion(int $tradeId, int $completedShares): array
    {
        try {
            Log::info("Handling trade completion for trade {$tradeId}, completed shares: {$completedShares}");

            // Calculate new progress
            $newProgress = $this->computeTradeProgress($tradeId);
            
            // Check if trade is now fully completed
            $isTradeComplete = $newProgress['progress_percentage'] >= 100;
            
            if ($isTradeComplete) {
                Log::info("Trade {$tradeId} is now 100% complete!");
                
                // Could trigger additional logic here, like notifications, etc.
                $this->handleFullTradeCompletion($tradeId);
            }

            return [
                'success' => true,
                'trade_id' => $tradeId,
                'completed_shares' => $completedShares,
                'new_progress' => $newProgress,
                'is_trade_complete' => $isTradeComplete,
                'message' => "Trade progress updated successfully"
            ];

        } catch (\Exception $e) {
            Log::error("Error handling trade completion for trade {$tradeId}: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'trade_id' => $tradeId
            ];
        }
    }

    /**
     * Handle full trade completion (when 100% reached)
     * 
     * @param int $tradeId
     * @return void
     */
    private function handleFullTradeCompletion(int $tradeId): void
    {
        try {
            $trade = Trade::find($tradeId);
            
            if ($trade) {
                // Could update trade status, send notifications, etc.
                Log::info("Full completion handling for trade: {$trade->name} (ID: {$tradeId})");
                
                // Add any additional logic needed when a trade reaches 100%
                // For example: marking trade as 'completed', sending notifications, etc.
            }

        } catch (\Exception $e) {
            Log::error("Error in full trade completion handling for trade {$tradeId}: " . $e->getMessage());
        }
    }

    /**
     * Get progress data for multiple trades at once
     * Useful for dashboard updates
     *
     * @param array $tradeIds
     * @return array
     */
    public function getBulkTradeProgress(array $tradeIds): array
    {
        $results = [];
        
        foreach ($tradeIds as $tradeId) {
            $results[$tradeId] = $this->computeTradeProgress($tradeId);
        }
        
        return $results;
    }

    /**
     * Get progress statistics for reporting
     *
     * @param int $tradeId
     * @return array
     */
    public function getProgressStatistics(int $tradeId): array
    {
        try {
            $progress = $this->computeTradeProgress($tradeId);
            $trade = Trade::find($tradeId);
            
            if (!$trade) {
                return ['error' => 'Trade not found'];
            }

            // Additional statistics
            $completedShares = UserShare::where('trade_id', $tradeId)
                ->where('status', 'completed')
                ->count();
            
            $failedShares = UserShare::where('trade_id', $tradeId)
                ->where('status', 'failed')
                ->count();
                
            $pendingShares = UserShare::where('trade_id', $tradeId)
                ->where('status', 'pending')
                ->count();

            return [
                'trade_id' => $tradeId,
                'trade_name' => $trade->name,
                'progress_percentage' => $progress['progress_percentage'],
                'total_shares' => $progress['total_shares'],
                'shares_bought' => $progress['shares_bought'],
                'available_shares' => $progress['available_shares'],
                'shares_remaining' => $progress['available_shares'],
                'completed_count' => $completedShares,
                'failed_count' => $failedShares,
                'pending_count' => $pendingShares,
                'success_rate' => $completedShares > 0 ? ($completedShares / ($completedShares + $failedShares)) * 100 : 0
            ];

        } catch (\Exception $e) {
            Log::error("Error getting progress statistics for trade {$tradeId}: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
