<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateSoldSharesStatus extends Command
{
    protected $signature = 'shares:update-sold-status';
    protected $description = 'Update sold share statuses correctly based on sold quantities';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Starting sold shares status update...');
        
        try {
            DB::beginTransaction();
            
            $updatedCount = $this->updateSoldSharesStatus();
            
            DB::commit();
            
            $this->info("Successfully updated {$updatedCount} sold share statuses at " . now());
            Log::info("Updated {$updatedCount} sold share statuses at " . now());
            
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error updating sold shares status: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine() . ' Message: ' . $e->getMessage());
            return 1;
        }
    }

    private function updateSoldSharesStatus()
    {
        $updatedCount = 0;
        
        // Find shares that should be marked as fully sold
        $fullySoldShares = UserShare::where('total_share_count', 0)
            ->where('hold_quantity', 0)
            ->where('sold_quantity', '>', 0)
            ->whereIn('status', ['completed', 'paired'])
            ->get();
        
        foreach ($fullySoldShares as $share) {
            $share->status = 'sold';
            $share->is_sold = 1;
            $share->save();
            $updatedCount++;
            
            $this->line("Updated share ID {$share->id} - Sold quantity: {$share->sold_quantity}");
        }
        
        // Find shares that are partially sold but status doesn't reflect this
        $partiallySoldShares = UserShare::where('sold_quantity', '>', 0)
            ->where(function($query) {
                $query->where('total_share_count', '>', 0)
                      ->orWhere('hold_quantity', '>', 0);
            })
            ->whereIn('status', ['completed'])
            ->get();
        
        foreach ($partiallySoldShares as $share) {
            // These shares keep their completed status but we log them for tracking
            $this->line("Partially sold share ID {$share->id} - Sold: {$share->sold_quantity}, Remaining: {$share->total_share_count}, Hold: {$share->hold_quantity}");
        }
        
        // Find shares where sold_quantity + total_share_count + hold_quantity doesn't match original shares
        $inconsistentShares = UserShare::whereRaw('(sold_quantity + total_share_count + hold_quantity) != share_will_get')
            ->whereIn('status', ['completed', 'paired', 'sold'])
            ->get();
            
        if ($inconsistentShares->count() > 0) {
            $this->warn("Found {$inconsistentShares->count()} shares with inconsistent quantities:");
            foreach ($inconsistentShares as $share) {
                $expected = $share->share_will_get;
                $actual = $share->sold_quantity + $share->total_share_count + $share->hold_quantity;
                $this->warn("Share ID {$share->id}: Expected {$expected}, Actual {$actual}");
            }
        }
        
        return $updatedCount;
    }
}
