<?php

namespace App\Listeners;

use App\Services\ShareAvailabilityCache;
use Illuminate\Support\Facades\Log;

class ClearShareAvailabilityCache
{
    protected $cacheService;

    /**
     * Create the event listener.
     */
    public function __construct(ShareAvailabilityCache $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the event when a UserShare is updated
     */
    public function handle($event)
    {
        // Check if this is a UserShare model update
        if (property_exists($event, 'userShare')) {
            $userShare = $event->userShare;
            
            // Clear cache for this trade if relevant fields changed
            if ($this->shouldClearCache($userShare)) {
                $this->cacheService->clearCache($userShare->trade_id);
                
                Log::debug('Share availability cache cleared due to UserShare update', [
                    'share_id' => $userShare->id,
                    'trade_id' => $userShare->trade_id,
                    'status' => $userShare->status,
                    'is_ready_to_sell' => $userShare->is_ready_to_sell
                ]);
            }
        }
    }
    
    /**
     * Determine if cache should be cleared based on the changes
     */
    protected function shouldClearCache($userShare)
    {
        // Clear cache if any of these fields changed
        $relevantFields = [
            'status',
            'is_ready_to_sell',
            'total_share_count',
            'deleted_at' // for soft deletes
        ];
        
        foreach ($relevantFields as $field) {
            if ($userShare->isDirty($field) || $userShare->wasChanged($field)) {
                return true;
            }
        }
        
        return false;
    }
}