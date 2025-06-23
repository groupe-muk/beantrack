<?php

namespace Database\Seeders;

use App\Models\Worker;
use App\Models\SupplyCenter;
use App\Models\WorkforceAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class WorkforceAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */    public function run(): void
    {
        // Get real worker records from the database
        $workers = Worker::all();
        $supplyCenters = SupplyCenter::all();
        
        // Check if we have workers and supply centers available
        if ($workers->count() === 0 || $supplyCenters->count() === 0) {
            Log::warning('Cannot create workforce assignments: No workers or supply centers available');
            return;
        }
        
        $this->command->info('Found ' . $workers->count() . ' workers and ' . $supplyCenters->count() . ' supply centers');
          
        // Create workforce assignments
        foreach (range(1, 10) as $i) {
            try {
                $worker = $workers->random();
                $supplyCenter = $supplyCenters->random();
                
                $this->command->info("Creating workforce assignment with worker ID: {$worker->id} and supply center ID: {$supplyCenter->id}");
                
                // Generate a unique ID for workforce assignment
                $assignmentId = 'WFA' . str_pad($i, 3, '0', STR_PAD_LEFT);
                
                WorkforceAssignment::create([
                    'id' => $assignmentId,
                    'worker_id' => $worker->id,
                    'supply_center_id' => $supplyCenter->id,
                    'role' => fake()->jobTitle(),
                    'start_date' => fake()->optional()->date(),
                    'end_date' => fake()->optional()->date(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->command->info("Created workforce assignment {$assignmentId}");
            } catch (\Exception $e) {
                Log::error('Failed to create workforce assignment: ' . $e->getMessage());
                Log::error('Worker ID: ' . ($worker->id ?? 'N/A') . ', Supply Center ID: ' . ($supplyCenter->id ?? 'N/A'));
            }
        }
    }
}
