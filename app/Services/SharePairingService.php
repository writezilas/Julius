<?php

namespace App\Services;

use App\Models\Log;
use App\Models\Trade;
use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Exceptions\SharePairingException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SharePairingService
{
    /**
     * Create a new share purchase and pair it with available sellers
     *
     * @param array $data Validated purchase data
     * @param Trade $trade The trade being purchased
     * @return UserShare The created buyer share
     * @throws SharePairingException
     */
    public function createSharePurchase(array $data, Trade $trade): UserShare
    {
        return DB::transaction(function () use ($data, $trade) {
            // Generate unique ticket number
            $ticketNo = $this->generateUniqueTicketNumber();
            
            // Calculate shares to purchase
            $sharesWillGet = floor($data['amount'] / $trade->price);
            
            // Validate sufficient shares are available
            $this->validateShareAvailability($trade, $sharesWillGet, $data['user_id']);
            
            // Create buyer share with pending status first
            $buyerShare = $this->createBuyerShare($data, $trade, $ticketNo, $sharesWillGet);
            
            // Pair with sellers and update both sides atomically
            $this->pairWithSellers($buyerShare, $trade, $sharesWillGet);
            
            // Update buyer share to paired status with start_date
            $this->activateBuyerShare($buyerShare);
            
            // Log the transaction
            $this->logSharePurchase($buyerShare, $sharesWillGet);
            
            return $buyerShare->refresh();
        });
    }
    
    /**
     * Generate a unique ticket number
     */
    private function generateUniqueTicketNumber(): string
    {
        do {
            $ticketNo = 'AB-' . time() . rand(3, 8);
            $count = 2;
            
            while (UserShare::where('ticket_no', $ticketNo)->exists()) {
                $ticketNo = 'AB-' . time() . rand(3, 8) . $count++;
            }
        } while (UserShare::where('ticket_no', $ticketNo)->exists());
        
        return $ticketNo;
    }
    
    /**
     * Validate that sufficient shares are available for purchase
     */
    private function validateShareAvailability(Trade $trade, int $requestedShares, int $buyerId): void
    {
        $availableShares = UserShare::where('trade_id', $trade->id)
            ->where('status', 'completed')
            ->where('is_ready_to_sell', 1)
            ->where('total_share_count', '>', 0)
            ->where('user_id', '!=', $buyerId)
            ->whereHas('user', function ($query) {
                $query->where('status', 'active');
            })
            ->sum('total_share_count');
            
        if ($requestedShares > $availableShares) {
            throw new SharePairingException(
                "Insufficient shares available. Requested: {$requestedShares}, Available: {$availableShares}"
            );
        }
    }
    
    /**
     * Create the initial buyer share with pending status
     */
    private function createBuyerShare(array $data, Trade $trade, string $ticketNo, int $sharesWillGet): UserShare
    {
        return UserShare::create([
            'trade_id' => $trade->id,
            'user_id' => $data['user_id'],
            'ticket_no' => $ticketNo,
            'amount' => $data['amount'],
            'period' => $data['period'],
            'share_will_get' => $sharesWillGet,
            'status' => 'pending', // Start as pending
            'payment_deadline_minutes' => get_gs_value('bought_time') ?? 60,
            'get_from' => 'purchase',
        ]);
    }
    
    /**
     * Pair buyer share with available sellers
     */
    private function pairWithSellers(UserShare $buyerShare, Trade $trade, int $totalSharesNeeded): void
    {
        $availableShares = $this->getAvailableSellerShares($trade, $buyerShare->user_id);
        
        // Prioritize sellers with enough shares to fulfill entire request
        $highestShares = $availableShares->where('total_share_count', '>=', $totalSharesNeeded)->shuffle();
        $arrayShares = $highestShares->count() ? $highestShares : $availableShares->shuffle();
        
        $remainingShares = $totalSharesNeeded;
        $pairings = [];
        
        foreach ($arrayShares as $sellerShare) {
            if ($remainingShares <= 0) break;
            
            $currentShare = UserShare::lockForUpdate()->findOrFail($sellerShare->id);
            $availableFromSeller = $currentShare->total_share_count;
            
            if ($availableFromSeller <= 0) continue;
            
            $sharesToTake = min($remainingShares, $availableFromSeller);
            
            // Update seller share counts and status
            $this->updateSellerShare($currentShare, $sharesToTake);
            
            // Create pairing record
            $pairing = $this->createSharePair($buyerShare, $currentShare, $sharesToTake);
            $pairings[] = $pairing;
            
            $remainingShares -= $sharesToTake;
        }
        
        if ($remainingShares > 0) {
            throw new SharePairingException(
                "Could not pair all requested shares. Missing: {$remainingShares} shares"
            );
        }
    }
    
    /**
     * Get available seller shares for a trade
     */
    private function getAvailableSellerShares(Trade $trade, int $excludeUserId)
    {
        return UserShare::where('trade_id', $trade->id)
            ->where('status', 'completed')
            ->where('is_ready_to_sell', 1)
            ->where('total_share_count', '>', 0)
            ->where('user_id', '!=', $excludeUserId)
            ->whereHas('user', function ($query) {
                $query->where('status', 'active');
            })
            ->get();
    }
    
    /**
     * Update seller share with hold quantity and status
     */
    private function updateSellerShare(UserShare $sellerShare, int $sharesToTake): void
    {
        $sellerShare->increment('hold_quantity', $sharesToTake);
        $sellerShare->decrement('total_share_count', $sharesToTake);
        
        // Update seller to paired status with start_date
        $sellerShare->status = 'paired';
        $sellerShare->start_date = $sellerShare->start_date ?? now();
        $sellerShare->save();
    }
    
    /**
     * Create a share pair record
     */
    private function createSharePair(UserShare $buyerShare, UserShare $sellerShare, int $shareCount): UserSharePair
    {
        return UserSharePair::create([
            'user_id' => $buyerShare->user_id,
            'user_share_id' => $buyerShare->id,
            'paired_user_share_id' => $sellerShare->id,
            'share' => $shareCount,
            'is_paid' => 0,
        ]);
    }
    
    /**
     * Activate buyer share by setting status to paired and start_date
     */
    private function activateBuyerShare(UserShare $buyerShare): void
    {
        $buyerShare->status = 'paired';
        $buyerShare->start_date = now();
        $buyerShare->save();
    }
    
    /**
     * Log the share purchase transaction
     */
    private function logSharePurchase(UserShare $buyerShare, int $sharesCount): void
    {
        $log = new Log([
            'remarks' => "Share bought successfully. Paired with sellers.",
            'type' => "share_purchase",
            'value' => $sharesCount,
            'user_id' => $buyerShare->user_id,
        ]);
        
        $buyerShare->logs()->save($log);
    }
    
    /**
     * Revert a failed share pairing (for cron jobs)
     */
    public function revertFailedPairing(UserShare $buyerShare): void
    {
        DB::transaction(function () use ($buyerShare) {
            // Get all pairs for this buyer share
            $pairs = UserSharePair::where('user_share_id', $buyerShare->id)->get();
            
            foreach ($pairs as $pair) {
                if ($pair->is_paid == 0) {
                    // Return shares to seller
                    $sellerShare = $pair->pairedShare;
                    if ($sellerShare) {
                        $sellerShare->decrement('hold_quantity', $pair->share);
                        $sellerShare->increment('total_share_count', $pair->share);
                        
                        // Check if seller has any other active pairs
                        $otherPairs = UserSharePair::where('paired_user_share_id', $sellerShare->id)
                            ->where('id', '!=', $pair->id)
                            ->where('is_paid', 0)
                            ->exists();
                            
                        if (!$otherPairs) {
                            $sellerShare->status = 'completed';
                        }
                        
                        $sellerShare->save();
                    }
                    
                    // Mark pair as failed
                    $pair->is_paid = 2;
                    $pair->save();
                }
            }
            
            // Update buyer share to failed
            $buyerShare->status = 'failed';
            $buyerShare->save();
        });
    }
}
