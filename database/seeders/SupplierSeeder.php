<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SupplyCenter;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */    public function run(): void
    {
        $users = User::all();
        $supplyCenters = SupplyCenter::all();
        
        // Check if we have users and supply centers available
        if ($users->count() === 0 || $supplyCenters->count() === 0) {
            // Log a warning if no users or supply centers exist
            Log::warning('Cannot create suppliers: No users or supply centers available');
            return;
        }
          // Create suppliers using existing user and supply center IDs
        foreach (range(1, 5) as $i) {
            // Get a random user and supply center
            $user = $users->random();
            $supplyCenter = $supplyCenters->random();
            
            // Ensure we have valid IDs before creating
            if ($user && $supplyCenter) {
                try {
                    Supplier::factory()->create([
                        'user_id' => $user->id,
                        'supply_center_id' => $supplyCenter->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create supplier: ' . $e->getMessage());
                    Log::error('User ID: ' . $user->id . ', Supply Center ID: ' . $supplyCenter->id);
                }
            }
        }
    }
}
