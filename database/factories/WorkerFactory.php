<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WorkerFactory extends Factory
{
    public function definition(): array
    {
        return [
            // 'id' => null, // id is not fillable
            'name' => $this->faker->name(),
            'role' => $this->faker->jobTitle(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            // 'created_at' and 'updated_at' are handled by Laravel
        ];
    }
}
