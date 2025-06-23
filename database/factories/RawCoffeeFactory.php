<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Supplier; // Make sure to import the Supplier model

class RawCoffeeFactory extends Factory
{
    public function definition(): array
    {
        static $coffeeNumber = 1;
        
        // Get an actual supplier ID from the database
        $supplier = Supplier::inRandomOrder()->first();
        
        return [
            'id' => 'RC' . str_pad($coffeeNumber++, 5, '0', STR_PAD_LEFT),
            'supplier_id' => $supplier ? $supplier->id : null,
            'coffee_type' => $this->faker->word(),
            'grade' => $this->faker->randomElement(['A', 'B', 'C']),
            'screen_size' => $this->faker->optional()->randomElement(['12', '14', '16']),
            'defect_count' => $this->faker->optional()->numberBetween(0, 10),
            'harvest_date' => $this->faker->optional()->date(),
        ];
    }
}
