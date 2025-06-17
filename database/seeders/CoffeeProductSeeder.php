<?php

namespace Database\Seeders;

use App\Models\SupplyCenter;
use App\Models\CoffeeProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CoffeeProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supplyCenters = SupplyCenter::all();
        
        // Check if we have supply centers available
        if ($supplyCenters->count() === 0) {
            Log::warning('Cannot create coffee products: No supply centers available');
            return;
        }
          // Create coffee products using existing supply center IDs
        foreach (range(1, 10) as $i) {
            $supplyCenter = $supplyCenters->random();
            
            if ($supplyCenter) {
                try {
                    CoffeeProduct::factory()->create([
                        'supply_center_id' => $supplyCenter->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create coffee product: ' . $e->getMessage());
                    Log::error('Supply Center ID: ' . $supplyCenter->id);
                }
            }
        }
    }
}
