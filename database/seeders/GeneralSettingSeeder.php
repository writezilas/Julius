<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GeneralSetting;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeder.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            [
                'key' => 'reffaral_bonus',
                'value' => '100',
            ],
            [
                'key' => 'bought_time',
                'value' => '1440', // 24 hours in minutes
            ],
            [
                'key' => 'app_timezone',
                'value' => 'Africa/Nairobi',
            ],
            [
                'key' => 'currency_symbol',
                'value' => 'KSH',
            ],
            [
                'key' => 'min_trading_price',
                'value' => '10.00',
            ],
            [
                'key' => 'max_trading_price',
                'value' => '100000.00',
            ],
            [
                'key' => 'tax_rate',
                'value' => '0.00', // 0% tax rate
            ],
            [
                'key' => 'open_market',
                'value' => '09:00',
            ],
            [
                'key' => 'close_market', 
                'value' => '17:00',
            ],
            [
                'key' => 'site_name',
                'value' => 'Auto Bidder',
            ],
            [
                'key' => 'site_description',
                'value' => 'Your Preferred Trading Partner',
            ],
            [
                'key' => 'default_trade_periods',
                'value' => json_encode([
                    ['days' => 1, 'percentage' => 5, 'label' => '1 Day (5% return)'],
                    ['days' => 3, 'percentage' => 15, 'label' => '3 Days (15% return)'],
                    ['days' => 7, 'percentage' => 30, 'label' => '1 Week (30% return)'],
                    ['days' => 14, 'percentage' => 60, 'label' => '2 Weeks (60% return)'],
                    ['days' => 30, 'percentage' => 120, 'label' => '1 Month (120% return)'],
                    ['days' => 60, 'percentage' => 250, 'label' => '2 Months (250% return)'],
                    ['days' => 90, 'percentage' => 400, 'label' => '3 Months (400% return)'],
                ]),
            ],
            [
                'key' => 'load_periods_from_settings',
                'value' => '0', // 0 = load from database, 1 = load from settings
            ],
            [
                'key' => 'support_form_enabled',
                'value' => '1', // 1 = enabled, 0 = disabled
            ],
        ];

        foreach ($settings as $setting) {
            GeneralSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
