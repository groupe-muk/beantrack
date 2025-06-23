<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\RawCoffee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CoffeeProductFactory extends Factory
{
    public function definition(): array
    {
        Log::info('Starting CoffeeProductFactory definition');
        
        $hasRawCoffee = RawCoffee::count() > 0;
        Log::info("Raw Coffee available: {$hasRawCoffee}");
        
        // Generate a unique string ID - make sure it's within 6 chars (table limit)
        // Let the database trigger handle the ID generation instead
        $productId = null; 
        Log::info("Will let database trigger generate the coffee product ID");
        
        $rawCoffeeId = null;
        
        if ($hasRawCoffee) {
            try {
                $rawCoffee = RawCoffee::inRandomOrder()->first();
                if ($rawCoffee) {
                    $rawCoffeeId = $rawCoffee->id;
                    Log::info("Selected raw coffee: id={$rawCoffeeId}");
                } else {
                    Log::error("Failed to fetch raw coffee record even though count > 0");
                }
            } catch (\Exception $e) {
                Log::error("Exception when selecting raw coffee: " . $e->getMessage());
            }
        } else {
            Log::warning("No raw coffee available to link to coffee product");
        }
        
        $category = $this->faker->randomElement(['Arabica', 'Robusta', 'Liberica']);
        $name = $this->faker->word() . ' Blend';
        $productForm = $this->faker->randomElement(['beans', 'ground', 'capsule']);
        $roastLevel = $this->faker->randomElement(['light', 'medium', 'dark']);
        $productionDate = $this->faker->date();
        
        Log::info("Coffee Product details: id={$productId}, name={$name}, category={$category}, form={$productForm}, roast={$roastLevel}");
        
        return [
            'id' => $productId,
            'raw_coffee_id' => $rawCoffeeId,
            'category' => $category,
            'name' => $name,
            'product_form' => $productForm,
            'roast_level' => $roastLevel,
            'production_date' => $productionDate,
        ];
    }
}
