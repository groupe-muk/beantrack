<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WholesalerFactory extends Factory
{
    public function definition(): array
    {
        // Always create a new user to ensure valid IDs
        $user = User::factory()->create(['role' => 'vendor']);
        
        return [
            'id' => null,
            'user_id' => $user->id,
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
