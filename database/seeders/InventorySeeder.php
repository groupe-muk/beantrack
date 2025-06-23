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
        if ($supplyCenters->count() === 0 || 
            ($coffeeProducts->count() === 0 && $rawCoffees->count() === 0)) {
            Log::warning('Cannot create inventory: Missing necessary related models');
            return;
        }
        
        // Create inventory entries
        foreach (range(1, 10) as $i) {
            // Randomly decide whether this is a raw coffee or coffee product inventory
            $isRawCoffee = rand(0, 1) === 1;
              try {
                if ($isRawCoffee && $rawCoffees->count() > 0) {
                    $supplyCenter = $supplyCenters->random();
                    $rawCoffee = $rawCoffees->random();
                    
                    if ($supplyCenter && $rawCoffee) {
                        Inventory::factory()->create([
                            'supply_center_id' => $supplyCenter->id,
                            'raw_coffee_id' => $rawCoffee->id,
                            'coffee_product_id' => null,
                            'type' => 'raw_coffee',
                        ]);
                    }
                } elseif ($coffeeProducts->count() > 0) {
                    $supplyCenter = $supplyCenters->random();
                    $coffeeProduct = $coffeeProducts->random();
                    
                    if ($supplyCenter && $coffeeProduct) {
                        Inventory::factory()->create([
                            'supply_center_id' => $supplyCenter->id,
                            'raw_coffee_id' => null,
                            'coffee_product_id' => $coffeeProduct->id,
                            'type' => 'coffee_product',
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to create inventory: ' . $e->getMessage());
            }
        }
    }
}
