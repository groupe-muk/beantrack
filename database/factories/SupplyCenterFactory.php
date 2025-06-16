<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupplyCenterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => null,
            'name' => $this->faker->company(),
            'location' => $this->faker->address(),
            'capacity' => $this->faker->randomFloat(2, 1000, 10000),
        ];
    }
}
