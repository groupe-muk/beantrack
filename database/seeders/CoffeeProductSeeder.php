<?php

namespace Database\Seeders;

use App\Models\RawCoffee;
use App\Models\CoffeeProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CoffeeProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rawCoffees = RawCoffee::all();
        
        // Check if we have raw coffee available
        if ($rawCoffees->count() === 0) {
            Log::warning('Cannot create coffee products: No raw coffee available');
            return;
        }
        
        $this->command->info('Found ' . $rawCoffees->count() . ' raw coffees for product seeding');
        
        // Create coffee products using existing raw coffee IDs
        foreach (range(1, 10) as $index) {
            $rawCoffee = $rawCoffees->random();
            
            if ($rawCoffee) {
                try {
                    // Generate a unique ID for coffee product
                    $coffeeProductId = 'CP' . str_pad($index, 4, '0', STR_PAD_LEFT);
                    
                    CoffeeProduct::create([
                        'id' => $coffeeProductId,
                        'raw_coffee_id' => $rawCoffee->id,
                        'category' => fake()->randomElement(['Arabica', 'Robusta', 'Liberica']),
                        'name' => fake()->word() . ' Blend',
                        'product_form' => fake()->randomElement(['beans', 'ground', 'capsule']),
                        'roast_level' => fake()->optional()->randomElement(['light', 'medium', 'dark']),
                        'production_date' => fake()->optional()->date(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    $this->command->info("Created coffee product {$coffeeProductId} with raw coffee ID {$rawCoffee->id}");
                } catch (\Exception $e) {
                    Log::error('Failed to create coffee product: ' . $e->getMessage());
                    Log::error('Raw Coffee ID: ' . $rawCoffee->id);
                }
            }
        }
    }
}
