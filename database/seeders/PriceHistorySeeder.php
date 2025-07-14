<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CoffeeProduct;
use App\Models\PriceHistory;
use Illuminate\Support\Facades\Log;

class PriceHistorySeeder extends Seeder
{
    public function run(): void
    {
        $products = CoffeeProduct::all();

        if ($products->isEmpty()) {
            Log::warning('PriceHistorySeeder: No coffee products found. Skipping seeding price histories.');
            return;
        }

        foreach ($products as $product) {
            // use a base price between 0.3 and 0.8 (as cents per lb)
            $base = mt_rand(30, 80) / 100;
            for ($daysAgo = 30; $daysAgo >= 0; $daysAgo--) {
                $price = max(0.3, min(0.8, $base + (mt_rand(-20, 20) / 1000))); // vary ±0.02

                PriceHistory::updateOrCreate(
                    [
                        'coffee_product_id' => $product->id,
                        'market_date'       => now()->subDays($daysAgo)->toDateString(),
                    ],
                    [
                        'price_per_lb' => round($price, 4),
                    ]
                );
            }
        }
    }
} 