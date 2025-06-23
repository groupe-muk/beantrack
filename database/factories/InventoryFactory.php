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
        $rawCoffeeId = RawCoffee::inRandomOrder()->first()?->id;
        $coffeeProductId = CoffeeProduct::inRandomOrder()->first()?->id;
        // Only one of raw_coffee_id or coffee_product_id should be set
        $rawOrProduct = $this->faker->boolean();
        return [
            'id' => null,
            'raw_coffee_id' => $rawOrProduct ? $rawCoffeeId : null,
            'coffee_product_id' => !$rawOrProduct ? $coffeeProductId : null,
            'quantity_in_stock' => $this->faker->randomFloat(2, 10, 1000),
            'supply_center_id' => SupplyCenter::inRandomOrder()->first()?->id,
            'last_updated' => $this->faker->dateTimeThisYear(),
        ];
    }
}
