<?php

namespace App\Console\Commands;

use App\Models\UserShare;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixShareStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'shares:fix-status {--dry-run : Show what would be changed without making changes} {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     */
    protected $description = 'Fix status for all shares ready for sale based on correct priority logic';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');
        
        $this->info('🔧 Fixing Share Status Based on Correct Priority Logic');
        $this->newLine();
        
        if ($isDryRun) {
            $this->warn('🧪 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }
        
        try {
            // Get all shares that are ready to sell
            $this->info('📊 Analyzing shares ready for sale...');
            
            $sharesReadyToSell = UserShare::where('status', 'completed')
                ->where('is_ready_to_sell', 1)
                ->where('total_share_count', '>', 0)
                ->get();
            
            $this->info("Found {$sharesReadyToSell->count()} shares ready for sale");
            $this->newLine();
            
            $statusChanges = [
                'available' => [],
                'partially_sold' => [],
                'sold' => [],
                'no_change' => []
            ];
            
            foreach ($sharesReadyToSell as $share) {
                $currentStatus = $this->determineCurrentDisplayStatus($share);
                $correctStatus = $this->determineCorrectStatus($share);
                
                if ($currentStatus !== $correctStatus) {
                    switch ($correctStatus) {
                        case 'Available':
                            $statusChanges['available'][] = [
                                'share' => $share,
                                'from' => $currentStatus,
                                'to' => $correctStatus,
                                'reason' => 'Ready to sell, no sales yet'
                            ];
                            break;
                        case 'Partially Sold':
                            $statusChanges['partially_sold'][] = [
                                'share' => $share,
                                'from' => $currentStatus,
                                'to' => $correctStatus,
                                'reason' => 'Has sales, more shares available'
                            ];
                            break;
                        case 'Sold':
                            $statusChanges['sold'][] = [
                                'share' => $share,
                                'from' => $currentStatus,
                                'to' => $correctStatus,
                                'reason' => 'All shares sold'
                            ];
                            break;
                    }
                } else {
                    $statusChanges['no_change'][] = $share;
                }
            }
            
            // Display analysis
            $this->displayAnalysis($statusChanges);
            
            $totalChanges = count($statusChanges['available']) + 
                           count($statusChanges['partially_sold']) + 
                           count($statusChanges['sold']);
            
            if ($totalChanges === 0) {
                $this->info('✅ All shares already have correct status!');
                return 0;
            }
            
            if (!$isDryRun) {
                if (!$force && !$this->confirm("Apply {$totalChanges} status corrections?")) {
                    $this->info('❌ Status correction cancelled');
                    return 1;
                }
                
                $this->info('🔧 Applying status corrections...');
                $this->applyStatusCorrections($statusChanges);
            } else {
                $this->info('🧪 DRY RUN completed - Run without --dry-run to apply changes');
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error('FixShareStatusCommand failed: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Determine what status the share currently displays
     */
    private function determineCurrentDisplayStatus(UserShare $share): string
    {
        return getSoldShareStatus($share);
    }
    
    /**
     * Determine what status the share should have based on correct logic
     */
    private function determineCorrectStatus(UserShare $share): string
    {
        // Apply the corrected priority logic
        
        // PRIORITY 1: If share is active and not ready to sell yet (Running)
        if ($share->start_date != '' && $share->is_ready_to_sell === 0) {
            return 'Active';
        }
        
        // PRIORITY 2: If share has been fully sold (no shares left and has sold some)
        if ($share->total_share_count == 0 && $share->hold_quantity == 0 && $share->sold_quantity > 0) {
            return 'Sold';
        }
        
        // PRIORITY 3: AVAILABLE - Share is ready to sell and has shares available
        if ($share->is_ready_to_sell === 1 && ($share->total_share_count > 0 || $share->hold_quantity > 0)) {
            // Check if this is truly available (no sales yet) or partially sold
            if ($share->sold_quantity == 0) {
                return 'Available';
            }
            // If some shares sold but more available, then partially sold
            else {
                return 'Partially Sold';
            }
        }
        
        // PRIORITY 4: If share has been paired but not fully processed
        if ((($share->share_will_get + $share->profit_share) > $share->total_share_count) && 
            ($share->total_share_count !== 0 || $share->hold_quantity !== 0)) {
            return 'Paired';
        }
        
        // PRIORITY 5: If share is completed but not sold (edge case)
        if ($share->total_share_count === 0 && $share->hold_quantity === 0 && $share->sold_quantity === 0) {
            return 'Completed';
        }
        
        // Default status
        return 'Available';
    }
    
    /**
     * Display detailed analysis of status changes
     */
    private function displayAnalysis(array $statusChanges): void
    {
        $this->info('📋 STATUS CORRECTION ANALYSIS:');
        $this->newLine();
        
        if (!empty($statusChanges['available'])) {
            $this->info("🟢 Shares to mark as AVAILABLE ({" . count($statusChanges['available']) . "}):");
            foreach ($statusChanges['available'] as $change) {
                $share = $change['share'];
                $this->line("   • {$share->ticket_no}: {$change['from']} → Available");
                $this->line("     Reason: {$change['reason']}");
                $this->line("     Details: total={$share->total_share_count}, sold={$share->sold_quantity}");
            }
            $this->newLine();
        }
        
        if (!empty($statusChanges['partially_sold'])) {
            $this->info("🟡 Shares to mark as PARTIALLY SOLD ({" . count($statusChanges['partially_sold']) . "}):");
            foreach ($statusChanges['partially_sold'] as $change) {
                $share = $change['share'];
                $this->line("   • {$share->ticket_no}: {$change['from']} → Partially Sold");
                $this->line("     Reason: {$change['reason']}");
                $this->line("     Details: total={$share->total_share_count}, sold={$share->sold_quantity}");
            }
            $this->newLine();
        }
        
        if (!empty($statusChanges['sold'])) {
            $this->info("🔴 Shares to mark as SOLD ({" . count($statusChanges['sold']) . "}):");
            foreach ($statusChanges['sold'] as $change) {
                $share = $change['share'];
                $this->line("   • {$share->ticket_no}: {$change['from']} → Sold");
                $this->line("     Reason: {$change['reason']}");
                $this->line("     Details: total={$share->total_share_count}, sold={$share->sold_quantity}");
            }
            $this->newLine();
        }
        
        if (!empty($statusChanges['no_change'])) {
            $this->info("✅ Shares already correct ({" . count($statusChanges['no_change']) . "})");
        }
    }
    
    /**
     * Apply status corrections to database
     */
    private function applyStatusCorrections(array $statusChanges): void
    {
        DB::beginTransaction();
        
        try {
            $correctedCount = 0;
            
            // Handle shares that should be marked as sold
            foreach ($statusChanges['sold'] as $change) {
                $share = $change['share'];
                $share->status = 'sold';
                $share->is_sold = 1;
                $share->save();
                $correctedCount++;
                
                $this->line("✅ {$share->ticket_no}: Status updated to 'sold'");
                
                Log::info('Share status corrected to sold', [
                    'ticket_no' => $share->ticket_no,
                    'from_display_status' => $change['from'],
                    'to_display_status' => $change['to'],
                    'reason' => $change['reason']
                ]);
            }
            
            // Available and Partially Sold shares don't need database status changes
            // They are display-only status based on the getSoldShareStatus() function
            foreach (array_merge($statusChanges['available'], $statusChanges['partially_sold']) as $change) {
                $share = $change['share'];
                $this->line("✅ {$share->ticket_no}: Display status will show as '{$change['to']}'");
                
                Log::info('Share display status corrected', [
                    'ticket_no' => $share->ticket_no,
                    'from_display_status' => $change['from'],
                    'to_display_status' => $change['to'],
                    'reason' => $change['reason']
                ]);
            }
            
            DB::commit();
            
            $this->newLine();
            $this->info('🎉 Status corrections applied successfully!');
            $this->info("📊 Summary:");
            $this->line("   • Database status changes: " . count($statusChanges['sold']));
            $this->line("   • Display status fixes: " . (count($statusChanges['available']) + count($statusChanges['partially_sold'])));
            $this->line("   • Total corrections: " . (count($statusChanges['available']) + count($statusChanges['partially_sold']) + count($statusChanges['sold'])));
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Failed to apply corrections: ' . $e->getMessage());
            throw $e;
        }
    }
}