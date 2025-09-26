<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Trade;
use Illuminate\Support\Str;

class TradeSeeder extends Seeder
{
    /**
     * Run the database seeder.
     *
     * @return void
     */
    public function run()
    {
        $trades = [
            [
                'name' => 'Forex Trading',
                'slug' => 'forex-trading',
                'quantity' => 1000,
                'price' => 100.00,
                'buying_price' => 95.00,
                'status' => 1,
            ],
            [
                'name' => 'Crypto Trading',
                'slug' => 'crypto-trading', 
                'quantity' => 500,
                'price' => 250.00,
                'buying_price' => 240.00,
                'status' => 1,
            ],
            [
                'name' => 'Stock Trading',
                'slug' => 'stock-trading',
                'quantity' => 750,
                'price' => 150.00,
                'buying_price' => 145.00,
                'status' => 1,
            ],
            [
                'name' => 'Commodities Trading',
                'slug' => 'commodities-trading',
                'quantity' => 300,
                'price' => 200.00,
                'buying_price' => 190.00,
                'status' => 1,
            ],
            [
                'name' => 'Bonds Trading',
                'slug' => 'bonds-trading',
                'quantity' => 200,
                'price' => 500.00,
                'buying_price' => 480.00,
                'status' => 1,
            ],
        ];

        foreach ($trades as $trade) {
            Trade::updateOrCreate(
                ['slug' => $trade['slug']],
                $trade
            );
        }
    }
}
