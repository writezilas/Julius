<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GeneralSetting;

class EmailConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeder.
     *
     * @return void
     */
    public function run()
    {
        // Email configuration settings for Autobidder
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
            'created_by' => 'System Seeder'
        ];

        // Create or update the mail_setting record
        GeneralSetting::updateOrCreate(
            ['key' => 'mail_setting'],
            ['value' => json_encode($emailSettings)]
        );

        // Log the seeding action
        \Log::info('Email configuration seeded successfully', [
            'mail_host' => $emailSettings['mail_host'],
            'mail_port' => $emailSettings['mail_port'],
            'mail_username' => $emailSettings['mail_username'],
            'mail_from_name' => $emailSettings['mail_from_name'],
            'mail_encryption' => $emailSettings['mail_encryption'],
            'seeded_at' => now()
        ]);
    }
}