<?php

// Script to create inventory records
require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Get the kernel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\RawCoffee;
use App\Models\CoffeeProduct;
use App\Models\SupplyCenter;
use App\Models\Inventory;

// First, check what we have available
$rawCoffees = RawCoffee::all();
$coffeeProducts = CoffeeProduct::all();
$supplyCenters = SupplyCenter::all();

echo "Found " . count($rawCoffees) . " raw coffees\n";
echo "Found " . count($coffeeProducts) . " coffee products\n";
echo "Found " . count($supplyCenters) . " supply centers\n";

if (count($rawCoffees) > 0 && count($supplyCenters) > 0) {
    echo "Creating inventories for raw coffees...\n";
    
    // For each raw coffee, create an inventory in each supply center
    foreach ($rawCoffees as $rawCoffee) {
        foreach ($supplyCenters as $supplyCenter) {
            try {
                $inventory = new Inventory();
                $inventory->raw_coffee_id = $rawCoffee->id;
                $inventory->coffee_product_id = null;
                $inventory->quantity_in_stock = rand(50, 500);
                $inventory->supply_center_id = $supplyCenter->id;
                $inventory->last_updated = now();
                $inventory->save();
                
                echo "Created inventory for raw coffee {$rawCoffee->id} at supply center {$supplyCenter->id}\n";
            } catch (\Exception $e) {
                echo "Error creating inventory for raw coffee {$rawCoffee->id}: " . $e->getMessage() . "\n";
            }
        }
    }
}

if (count($coffeeProducts) > 0 && count($supplyCenters) > 0) {
    echo "Creating inventories for coffee products...\n";
    
    // For each coffee product, create an inventory in each supply center
    foreach ($coffeeProducts as $coffeeProduct) {
        foreach ($supplyCenters as $supplyCenter) {
            try {
                $inventory = new Inventory();
                $inventory->raw_coffee_id = null;
                $inventory->coffee_product_id = $coffeeProduct->id;
                $inventory->quantity_in_stock = rand(20, 200);
                $inventory->supply_center_id = $supplyCenter->id;
                $inventory->last_updated = now();
                $inventory->save();
                
                echo "Created inventory for coffee product {$coffeeProduct->id} at supply center {$supplyCenter->id}\n";
            } catch (\Exception $e) {
                echo "Error creating inventory for coffee product {$coffeeProduct->id}: " . $e->getMessage() . "\n";
            }
        }
    }
}

echo "Done!\n";
