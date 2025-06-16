<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SupplyCenter;
use App\Models\Wholesaler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class WholesalerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */    public function run(): void
    {
        $users = User::all();
        $supplyCenters = SupplyCenter::all();
        
        // Check if we have users and supply centers available
        if ($users->count() === 0 || $supplyCenters->count() === 0) {
            Log::warning('Cannot create wholesalers: No users or supply centers available');
            return;
        }
          // Create wholesalers using existing user and supply center IDs
        foreach (range(1, 5) as $i) {
            // Get a random user and supply center
            $user = $users->random();
            $supplyCenter = $supplyCenters->random();
            
            // Ensure we have valid IDs before creating
            if ($user && $supplyCenter) {
                try {
                    Wholesaler::factory()->create([
                        'user_id' => $user->id,
                        'supply_center_id' => $supplyCenter->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create wholesaler: ' . $e->getMessage());
                    Log::error('User ID: ' . $user->id . ', Supply Center ID: ' . $supplyCenter->id);
                }
            }
        }
    }
}
