<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Worker;
use App\Models\SupplyCenter;

class WorkforceAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => null,
            'worker_id' => Worker::inRandomOrder()->first()?->id,
            'supply_center_id' => SupplyCenter::inRandomOrder()->first()?->id,
            'role' => $this->faker->jobTitle(),
            'start_date' => $this->faker->optional()->date(),
            'end_date' => $this->faker->optional()->date(),
        ];
    }
}
