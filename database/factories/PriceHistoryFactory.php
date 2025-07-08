<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CoffeeProduct;

class PriceHistoryFactory extends Factory
{
    protected $model = \App\Models\PriceHistory::class;

    public function definition(): array
    {
        return [
            'id' => null, // let DB trigger assign
            'coffee_product_id' => CoffeeProduct::factory(),
            'market_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'price_per_lb' => $this->faker->randomFloat(4, 0.3, 0.8),
        ];
    }
} 