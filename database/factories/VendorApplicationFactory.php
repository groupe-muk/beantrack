<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User; // Import the User model

class VendorApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => null,
            'applicant_id' => User::inRandomOrder()->first()?->id,
            'financial_data' => json_encode(['revenue' => $this->faker->numberBetween(10000, 100000)]),
            'references' => json_encode([$this->faker->company(), $this->faker->company()]),
            'license_data' => json_encode(['license' => $this->faker->uuid]),
            'status' => $this->faker->randomElement(['pending', 'under_review', 'approved', 'rejected']),
            'visit_scheduled' => $this->faker->optional()->date(),
        ];
    }
}
