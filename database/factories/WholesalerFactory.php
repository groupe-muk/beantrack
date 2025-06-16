<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WholesalerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => null,
            'user_id' => null, // Set in seeder if needed
            'name' => $this->faker->company(),
            'contact_person' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'distribution_region' => $this->faker->city(),
            'registration_number' => $this->faker->unique()->bothify('REG#####'),
            'approved_date' => $this->faker->optional()->date(),
        ];
    }
}
