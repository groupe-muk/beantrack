<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WorkerFactory extends Factory
{
    public function definition(): array
    {
        static $workerNumber = 1;
        
        return [
            'id' => 'W' . str_pad($workerNumber++, 5, '0', STR_PAD_LEFT),
            'name' => $this->faker->name(),
            'role' => $this->faker->jobTitle(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            // 'created_at' and 'updated_at' are handled by Laravel
        ];
    }
}
