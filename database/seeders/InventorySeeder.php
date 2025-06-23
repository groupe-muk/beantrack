<?php

namespace Database\Seeders;

use App\Models\SupplyCenter;
use App\Models\CoffeeProduct;
use App\Models\RawCoffee;
use App\Models\Inventory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */    public function run(): void
    {
        $supplyCenters = SupplyCenter::all();
        $coffeeProducts = CoffeeProduct::all();
        $rawCoffees = RawCoffee::all();
        
        // Check if we have the necessary related models
        if ($supplyCenters->count() === 0) {
            Log::warning('Cannot create inventory: No supply centers available');
            return;
        }
        
        if ($coffeeProducts->count() === 0 && $rawCoffees->count() === 0) {
            Log::warning('Cannot create inventory: No coffee products or raw coffee available');
            return;
        }
        
        $inventoriesCreated = 0;
        $maxAttempts = 20;
        $attempt = 0;
        
        // Create inventory entries
        while ($inventoriesCreated < 10 && $attempt < $maxAttempts) {
            $attempt++;
            
            try {
                // Decide if this should be raw coffee or product based on what's available
                $hasRawCoffee = $rawCoffees->count() > 0;
                $hasProducts = $coffeeProducts->count() > 0;
                
                if (!$hasRawCoffee && !$hasProducts) {
                    Log::warning('No coffee products or raw coffee available');
                    return;
                }
                
                // Choose type based on what's available
                $isRawCoffee = false;
                if ($hasRawCoffee && $hasProducts) {
                    // If both are available, randomly choose one
                    $isRawCoffee = rand(0, 1) === 1;
                } else {
                    // Otherwise use whatever is available
                    $isRawCoffee = $hasRawCoffee;
                }
                
                $supplyCenter = $supplyCenters->random();
                if (!$supplyCenter) {
                    Log::warning('Failed to get a valid supply center');
                    continue;
                }
                
                // Create the inventory record with explicit null values where needed
                if ($isRawCoffee) {
                    $rawCoffee = $rawCoffees->random();
                    if ($rawCoffee) {
                        Inventory::factory()->create([
                            'supply_center_id' => $supplyCenter->id,
                            'raw_coffee_id' => $rawCoffee->id,
                            'coffee_product_id' => null, // Explicitly set to null
                        ]);
                        $inventoriesCreated++;
                    }
                } else {
                    $coffeeProduct = $coffeeProducts->random();
                    if ($coffeeProduct) {
                        Inventory::factory()->create([
                            'supply_center_id' => $supplyCenter->id,
                            'raw_coffee_id' => null, // Explicitly set to null
                            'coffee_product_id' => $coffeeProduct->id,
                        ]);
                        $inventoriesCreated++;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to create inventory: ' . $e->getMessage());
            }
        }
        
        Log::info("Created $inventoriesCreated inventory records successfully");
    }
}
