<?php

use App\Models\GeneralSetting;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Backfill existing users who don't have referral_bonus_at_registration set
     * with the current global referral bonus amount.
     *
     * @return void
     */
    public function up()
    {
        // Get current referral bonus from settings
        $currentBonus = GeneralSetting::where('key', 'reffaral_bonus')->value('value') ?? 100;
        
        // Update existing users who have a referral code but no referral_bonus_at_registration
        $affectedRows = User::whereNotNull('refferal_code')
            ->whereNull('referral_bonus_at_registration')
            ->where('ref_amount', '>', 0)
            ->update([
                'referral_bonus_at_registration' => $currentBonus
            ]);
            
        // Log the backfill operation
        if ($affectedRows > 0) {
            \Log::info("Backfilled referral_bonus_at_registration for {$affectedRows} existing users with bonus amount: {$currentBonus}");
        } else {
            \Log::info("No existing users required backfilling for referral_bonus_at_registration");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reset referral_bonus_at_registration to null for users affected by this migration
        // This is a best-effort reversal - we can't perfectly restore the original state
        \Log::info('Reversing referral bonus backfill migration - setting referral_bonus_at_registration to null for affected users');
        
        User::whereNotNull('referral_bonus_at_registration')
            ->update(['referral_bonus_at_registration' => null]);
    }
};
