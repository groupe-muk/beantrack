<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AnalyticsDataFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => null,
            'type' => $this->faker->randomElement(['demand', 'customer_segmentation']),
            'data' => json_encode(['value' => $this->faker->randomFloat(2, 0, 100)]),
            'generated_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
