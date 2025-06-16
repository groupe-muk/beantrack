<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order; // Make sure to import the Order model

class OrderTrackingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => null,
            'order_id' => Order::inRandomOrder()->first()?->id,
            'status' => $this->faker->randomElement(['shipped', 'in-transit', 'delivered']),
            'location' => $this->faker->optional()->city(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
