<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\RawCoffee;
use App\Models\CoffeeProduct;
use App\Models\SupplyCenter;

class InventoryFactory extends Factory
{
    public function definition(): array
    {
        // First check if we have any raw coffee or coffee products
        $hasRawCoffee = RawCoffee::count() > 0;
        $hasCoffeeProduct = CoffeeProduct::count() > 0;
        
        // Determine inventory type based on available data
        $isRawCoffeeInventory = $hasRawCoffee && ($this->faker->boolean() || !$hasCoffeeProduct);
        
        // Get valid IDs, ensuring we never have null or 0 values
        $rawCoffeeId = $isRawCoffeeInventory ? RawCoffee::inRandomOrder()->first()?->id : null;
        $coffeeProductId = !$isRawCoffeeInventory && $hasCoffeeProduct ? CoffeeProduct::inRandomOrder()->first()?->id : null;
        
        // Get a valid supply center
        $supplyCenterId = SupplyCenter::inRandomOrder()->first()?->id;
        
        // Only proceed if we have valid IDs and meet our constraint (one of raw or product must be set)
        if (($rawCoffeeId === null && $coffeeProductId === null) || $supplyCenterId === null) {
            throw new \Exception("Cannot create inventory: missing required related models");
        }
        
        return [
            'id' => null,
            'raw_coffee_id' => $rawCoffeeId,
            'coffee_product_id' => $coffeeProductId,
            'quantity_in_stock' => $this->faker->randomFloat(2, 10, 1000),
            'supply_center_id' => $supplyCenterId,
            'last_updated' => $this->faker->dateTimeThisYear(),
        ];
    }
}