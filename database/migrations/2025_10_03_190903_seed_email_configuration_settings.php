<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Seed the email configuration settings into the general_settings table
        $emailSettings = [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.titan.email',
            'mail_port' => 465,
            'mail_username' => 'info@autobidder.com',
            'mail_password' => 'Login1000@',
            'mail_encryption' => 'ssl', // SSL encryption
            'mail_from_address' => 'info@autobidder.com',
            'mail_from_name' => 'Autobidder',
            'created_at' => now()->toDateTimeString(),
            'created_by' => 'Migration Seeder'
        ];

        // Create or update the mail_setting record
        GeneralSetting::updateOrCreate(
            ['key' => 'mail_setting'],
            ['value' => json_encode($emailSettings)]
        );

        // Log the migration action
        Log::info('Email configuration migrated successfully', [
            'mail_host' => $emailSettings['mail_host'],
            'mail_port' => $emailSettings['mail_port'],
            'mail_username' => $emailSettings['mail_username'],
            'mail_from_name' => $emailSettings['mail_from_name'],
            'mail_encryption' => $emailSettings['mail_encryption'],
            'migrated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the email configuration from general_settings
        GeneralSetting::where('key', 'mail_setting')->delete();
        
        Log::info('Email configuration migration rolled back', [
            'rolled_back_at' => now()
        ]);
    }
};
