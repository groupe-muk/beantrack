<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Worker;
use App\Models\SupplyCenter;
use App\Models\WorkforceAssignment;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkforceAssignment>
 */
class WorkforceAssignmentFactory extends Factory
{
    protected $model = WorkforceAssignment::class;
    
    public function definition(): array
    {
        static $assignmentNumber = 1;
        
        // Get existing worker and supply center IDs
        $worker = Worker::inRandomOrder()->first();
        $supplyCenter = SupplyCenter::inRandomOrder()->first();
        
        return [
            'id' => 'WFA' . str_pad($assignmentNumber++, 3, '0', STR_PAD_LEFT),
            'worker_id' => $worker ? $worker->id : null,
            'supply_center_id' => $supplyCenter ? $supplyCenter->id : null,
            'role' => $this->faker->jobTitle(),
            'start_date' => $this->faker->optional()->date(),
            'end_date' => $this->faker->optional()->date(),
        ];
    }
}