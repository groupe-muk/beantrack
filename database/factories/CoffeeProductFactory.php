<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\RawCoffee; // Make sure to import the RawCoffee model

class CoffeeProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => null,
            'raw_coffee_id' => RawCoffee::inRandomOrder()->first()?->id,
            'category' => $this->faker->randomElement(['Arabica', 'Robusta', 'Liberica']),
            'name' => $this->faker->word() . ' Blend',
            'product_form' => $this->faker->randomElement(['beans', 'ground', 'capsule']),
            'roast_level' => $this->faker->optional()->randomElement(['light', 'medium', 'dark']),
            'production_date' => $this->faker->optional()->date(),
        ];
    }
}
