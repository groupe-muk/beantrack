<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\SupplyCenter;

class SupplierFactory extends Factory
{
    public function definition(): array
    {
        // Always create a new user and supply center to ensure valid IDs
        $user = User::factory()->create(['role' => 'supplier']);
        $supplyCenter = SupplyCenter::factory()->create();
        
        return [
            'user_id' => $user->id,
            'supply_center_id' => $supplyCenter->id,
            'name' => $this->faker->company(),
            'contact_person' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'registration_number' => $this->faker->unique()->bothify('REG#####'),
            'approved_date' => $this->faker->optional()->date(),
        ];
    }
}