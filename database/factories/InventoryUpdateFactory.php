<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Inventory;
use App\Models\User;

class InventoryUpdateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => null,
            'inventory_id' => Inventory::inRandomOrder()->first()?->id,
            'quantity_change' => $this->faker->randomFloat(2, -100, 100),
            'reason' => $this->faker->sentence(),
            'updated_by' => User::inRandomOrder()->first()?->id,
            'created_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
