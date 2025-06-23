<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User; // Import the User model

class ReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => null,
            'type' => $this->faker->randomElement(['inventory', 'order_summary', 'performance']),
            'recipient_id' => User::inRandomOrder()->first()?->id, // Set to a valid random User ID
            'frequency' => $this->faker->randomElement(['weekly', 'monthly']),
            'content' => json_encode(['summary' => $this->faker->sentence()]),
            'last_sent' => $this->faker->optional()->dateTimeThisYear(),
        ];
    }
}
