<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserShare;
use App\Models\Log;
use App\Models\Invoice;

class FixMissingReferralBonuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'referrals:fix-missing-bonuses {--dry-run : Show what would be fixed without making changes} {--user-id= : Fix specific user ID only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix missing referral bonus shares for users who referred others but didn\'t receive their bonus shares';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $specificUserId = $this->option('user-id');
        
        $this->info('🔍 Scanning for missing referral bonuses...');
        
        // Find all users who have been referred but their referrers didn't get bonus shares
        $query = User::whereNotNull('refferal_code')
            ->where('refferal_code', '!=', '');
            
        if ($specificUserId) {
            // If specific user ID provided, find users they referred
            $referrer = User::find($specificUserId);
            if (!$referrer) {
                $this->error("User with ID {$specificUserId} not found!");
                return 1;
            }
            $query->where('refferal_code', $referrer->username);
        }
        
        $referredUsers = $query->get();
        
        $this->info("Found {$referredUsers->count()} users with referral codes");
        
        $fixedCount = 0;
        $errors = [];
        
        foreach ($referredUsers as $referredUser) {
            try {
                // Find the referrer
                $referrer = User::where('username', $referredUser->refferal_code)->first();
                
                if (!$referrer) {
                    $this->warn("⚠️  Referrer '{$referredUser->refferal_code}' not found for user {$referredUser->username}");
                    continue;
                }
                
                // Check if referrer already has bonus shares for this referral
                $existingBonusShares = UserShare::where('user_id', $referrer->id)
                    ->where('get_from', 'refferal-bonus')
                    ->exists();
                
                // For now, let's create bonus shares if the referrer has none at all
                // (This could be refined to track specific referral relationships)
                if (!$existingBonusShares) {
                    $this->info("🎯 Processing referral: {$referredUser->username} (ID: {$referredUser->id}) referred by {$referrer->username} (ID: {$referrer->id})");
                    
                    if (!$dryRun) {
                        $this->createReferralBonus($referredUser, $referrer);
                        $fixedCount++;
                        $this->info("✅ Created referral bonus shares for {$referrer->username}");
                    } else {
                        $this->info("🔥 Would create referral bonus shares for {$referrer->username}");
                        $fixedCount++;
                    }
                } else {
                    $this->comment("ℹ️  {$referrer->username} already has referral bonus shares");
                }
                
            } catch (\Exception $e) {
                $error = "Error processing {$referredUser->username}: " . $e->getMessage();
                $errors[] = $error;
                $this->error("❌ " . $error);
            }
        }
        
        // Summary
        if ($dryRun) {
            $this->info("🧪 DRY RUN COMPLETE - No changes made");
            $this->info("📊 Would fix {$fixedCount} missing referral bonuses");
        } else {
            $this->info("✨ COMPLETED");
            $this->info("📊 Fixed {$fixedCount} missing referral bonuses");
        }
        
        if (!empty($errors)) {
            $this->error("\n❌ Errors encountered:");
            foreach ($errors as $error) {
                $this->error("   • " . $error);
            }
        }
        
        return 0;
    }
    
    /**
     * Create referral bonus shares for referrer
     * This is based on the createRefferalBonus helper function
     */
    private function createReferralBonus($referredUser, $referrer)
    {
        // Get the default trade (ID: 1)
        $trade = \App\Models\Trade::where('id', 1)->first();
        if (!$trade) {
            throw new \Exception("Default trade (ID: 1) not found");
        }
        
        // Get referral bonus amount
        $sharesWillGet = get_gs_value('reffaral_bonus') ?? 100;
        
        // Calculate data
        $data = [
            'trade_id' => $trade->id,
            'amount' => $trade->price * $sharesWillGet,
            'period' => 1,
        ];
        
        // Generate unique ticket number
        $ticketNo = 'AB-'.time().rand(3,8).$referredUser->id;
        
        $userShareWithTicket = UserShare::where('ticket_no', $ticketNo)->exists();
        $count = 2;
        
        if ($userShareWithTicket) {
            $data['ticket_no'] = $ticketNo . $count++;
        } else {
            $data['ticket_no'] = $ticketNo;
        }
        
        // Set ref_amount for the referred user
        $referredUser->ref_amount = $sharesWillGet;
        $referredUser->save();
        
        // Create the bonus share for the REFERRER
        $data['user_id'] = $referrer->id;  // Important: bonus goes to referrer
        $data['share_will_get'] = $sharesWillGet;
        $data['total_share_count'] = $sharesWillGet;
        $data['start_date'] = date_format(now(), "Y/m/d H:i:s");
        $data['status'] = 'completed';
        $data['is_ready_to_sell'] = 1;
        $data['get_from'] = 'refferal-bonus';
        
        $createdShare = UserShare::create($data);
        
        // Create allocation history
        $allocateShareHistoryData = [
            'user_share_id' => $createdShare->id,
            'shares' => $sharesWillGet,
            'created_by' => $referredUser->id,
        ];
        \App\Models\AllocateShareHistory::create($allocateShareHistoryData);
        
        // Calculate old amount for invoice
        $oldAmount = UserShare::where('status', 'completed')
            ->where('user_id', $referrer->id)
            ->where('id', '!=', $createdShare->id)  // Exclude the newly created share
            ->sum('amount');
        
        // Create invoice
        $invoiceData = [
            'user_id' => $referrer->id,
            'reff_user_id' => $referredUser->id,
            'share_id' => $createdShare->id,
            'old_amount' => $oldAmount,
            'add_amount' => $sharesWillGet,
            'new_amount' => $oldAmount + ($trade->price * $sharesWillGet),
        ];
        \App\Models\Invoice::create($invoiceData);
        
        // Create referral setup log for the referred user
        $refLog = new Log();
        $refLog->remarks = "RETROACTIVE: Referral bonus created for referrer {$referrer->username}. Referred user will earn KSH {$sharesWillGet} when referrer sells bonus shares.";
        $refLog->type = "referral_setup";
        $refLog->value = $sharesWillGet;
        $refLog->user_id = $referredUser->id;
        $referredUser->logs()->save($refLog);
        
        // Create log for the referrer
        $referrerLog = new Log();
        $referrerLog->remarks = "RETROACTIVE: Received {$sharesWillGet} referral bonus shares for referring {$referredUser->username}. Shares are ready to sell.";
        $referrerLog->type = "referral_bonus_received";
        $referrerLog->value = $trade->price * $sharesWillGet;
        $referrerLog->user_id = $referrer->id;
        $referrer->logs()->save($referrerLog);
        
        \Log::info("Retroactive referral bonus created: {$referrer->username} received {$sharesWillGet} bonus shares for referring {$referredUser->username}");
    }
}