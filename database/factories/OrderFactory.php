<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Supplier;
use App\Models\Wholesaler;
use App\Models\RawCoffee;
use App\Models\CoffeeProduct;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $isSupplierOrder = $this->faker->boolean();
        return [
            'id' => null,
            'supplier_id' => $isSupplierOrder ? Supplier::inRandomOrder()->first()?->id : null,
            'wholesaler_id' => !$isSupplierOrder ? Wholesaler::inRandomOrder()->first()?->id : null,
            'raw_coffee_id' => $isSupplierOrder ? RawCoffee::inRandomOrder()->first()?->id : null,
            'coffee_product_id' => !$isSupplierOrder ? CoffeeProduct::inRandomOrder()->first()?->id : null,
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'shipped', 'delivered']),
            'quantity' => $this->faker->randomFloat(2, 10, 1000),
            'total_price' => $this->faker->optional()->randomFloat(2, 100, 10000),
        ];
    }
}
