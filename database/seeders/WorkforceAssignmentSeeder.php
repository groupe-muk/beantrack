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
        $workers = Worker::all();
        $supplyCenters = SupplyCenter::all();
        
        // Check if we have workers and supply centers available
        if ($workers->count() === 0 || $supplyCenters->count() === 0) {
            Log::warning('Cannot create workforce assignments: No workers or supply centers available');
            return;
        }
          // Create workforce assignments
        foreach (range(1, 10) as $i) {
            $worker = $workers->random();
            $supplyCenter = $supplyCenters->random();
            
            if ($worker && $supplyCenter) {
                try {
                    WorkforceAssignment::factory()->create([
                        'worker_id' => $worker->id,
                        'supply_center_id' => $supplyCenter->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create workforce assignment: ' . $e->getMessage());
                    Log::error('Worker ID: ' . $worker->id . ', Supply Center ID: ' . $supplyCenter->id);
                }
            }
        }
    }
}
