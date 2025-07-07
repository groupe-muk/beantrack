<?php

namespace Database\Seeders;

use App\Models\SupplyCenter;
use App\Models\CoffeeProduct;
use App\Models\RawCoffee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CoffeeProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure every RawCoffee row owns at least one product
        foreach (RawCoffee::all() as $raw) {
            CoffeeProduct::firstOrCreate(
                ['raw_coffee_id' => $raw->id],
                [
                    'category'      => $raw->coffee_type,
                    'name'          => $raw->coffee_type.' Beans',
                    'product_form'  => 'beans',
                ]
            );
        }

        $supplyCenters = SupplyCenter::all();
        
        // Check if we have supply centers available
        if ($supplyCenters->count() === 0) {
            Log::warning('Cannot create coffee products: No supply centers available');
            return;
        }
          // Create additional coffee products using existing supply center IDs
        foreach (range(1, 7) as $i) {
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
