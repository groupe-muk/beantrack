<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DemandHistory;
use App\Models\CoffeeProduct;
use Carbon\Carbon;

class DemandHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = CoffeeProduct::all();
        
        if ($products->isEmpty()) {
            $this->command->warn('No coffee products found. Please run CoffeeProductSeeder first.');
            return;
        }

        $this->command->info('Seeding demand history data...');

        foreach ($products as $product) {
            // Create 30 days of historical demand data
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i);
                
                // Generate realistic demand based on coffee type
                $baseDemand = $this->getBaseDemandForProduct($product);
                $dailyVariation = rand(-20, 20) / 100; // ±20% variation
                $weekendFactor = $date->isWeekend() ? 0.7 : 1.0; // 30% less on weekends
                $seasonalFactor = $this->getSeasonalFactor($date);
                
                $demand = $baseDemand * (1 + $dailyVariation) * $weekendFactor * $seasonalFactor;
                $demand = max(0, round($demand, 3)); // Ensure non-negative, 3 decimal places

                DemandHistory::create([
                    'coffee_product_id' => $product->id,
                    'demand_date' => $date->toDateString(),
                    'demand_qty_tonnes' => $demand,
                ]);
            }
        }

        $this->command->info('Demand history seeding completed.');
    }

    private function getBaseDemandForProduct(CoffeeProduct $product): float
    {
        // Base demand in tonnes based on coffee type (0.100-0.600 tonnes range)
        $coffeeType = strtolower($product->rawCoffee->coffee_type ?? $product->name);
        
        return match (true) {
            str_contains($coffeeType, 'arabica') => rand(500, 600) / 1000, // 0.500-0.600 tonnes
            str_contains($coffeeType, 'robusta') => rand(300, 500) / 1000, // 0.300-0.500 tonnes
            str_contains($coffeeType, 'premium') => rand(400, 600) / 1000, // 0.400-0.600 tonnes
            default => rand(100, 600) / 1000, // 0.100-0.600 tonnes
        };
    }

    private function getSeasonalFactor(Carbon $date): float
    {
        $month = $date->month;
        
        // Higher demand in cooler months, lower in summer
        return match ($month) {
            12, 1, 2 => 1.2,  // Winter - 20% increase
            3, 4, 5 => 1.1,   // Spring - 10% increase
            6, 7, 8 => 0.9,   // Summer - 10% decrease
            9, 10, 11 => 1.0, // Fall - baseline
            default => 1.0,
        };
    }
} 