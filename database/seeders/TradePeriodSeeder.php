<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TradePeriod;

class TradePeriodSeeder extends Seeder
{
    /**
     * Run the database seeder.
     *
     * @return void
     */
    public function run()
    {
        $periods = [
            [
                'days' => 1,
                'percentage' => 5,
                'status' => 1,
            ],
            [
                'days' => 3,
                'percentage' => 15,
                'status' => 1,
            ],
            [
                'days' => 7,
                'percentage' => 30,
                'status' => 1,
            ],
            [
                'days' => 14,
                'percentage' => 60,
                'status' => 1,
            ],
            [
                'days' => 30,
                'percentage' => 120,
                'status' => 1,
            ],
            [
                'days' => 60,
                'percentage' => 250,
                'status' => 1,
            ],
            [
                'days' => 90,
                'percentage' => 400,
                'status' => 1,
            ],
        ];

        foreach ($periods as $period) {
            TradePeriod::updateOrCreate(
                ['days' => $period['days']],
                [
                    'percentage' => $period['percentage'],
                    'status' => $period['status']
                ]
            );
        }
    }
}
