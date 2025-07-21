<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use App\Models\Worker;
use App\Models\Supplier;
use App\Models\Wholesaler;
use App\Models\SupplyCenter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('Starting warehouse and worker seeding process');
        
        // Get suppliers and wholesalers
        $suppliers = Supplier::all();
        $wholesalers = Wholesaler::all();
        $supplyCenters = SupplyCenter::all();
        
        // Create warehouses for suppliers
        foreach ($suppliers as $index => $supplier) {
            $warehouse = Warehouse::create([
                'name' => "Supplier Warehouse " . ($index + 1),
                'location' => "Location " . ($index + 1),
                'capacity' => rand(1000, 5000),
                'supplier_id' => $supplier->id,
                'manager_name' => "Manager " . ($index + 1),
            ]);
            
            // Add 2-3 workers to each supplier warehouse
            $workerCount = rand(2, 3);
            for ($i = 1; $i <= $workerCount; $i++) {
                Worker::create([
                    'warehouse_id' => $warehouse->id,
                    'name' => "Warehouse Worker " . ($index + 1) . "-" . $i,
                    'role' => ['Operator', 'Supervisor', 'Forklift Driver'][rand(0, 2)],
                    'shift' => ['Morning', 'Afternoon', 'Night'][rand(0, 2)],
                    'email' => "warehouse.worker" . ($index + 1) . "-" . $i . "@beantrack.com",
                    'phone' => "555-" . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT) . "-" . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                    'address' => "Address for Worker " . ($index + 1) . "-" . $i,
                ]);
            }
            
            Log::info("Created warehouse for supplier {$supplier->id} with {$workerCount} workers");
        }
        
        // Create warehouses for wholesalers/vendors
        foreach ($wholesalers as $index => $wholesaler) {
            $warehouse = Warehouse::create([
                'name' => "Vendor Warehouse " . ($index + 1),
                'location' => "Vendor Location " . ($index + 1),
                'capacity' => rand(1500, 6000),
                'wholesaler_id' => $wholesaler->id,
                'manager_name' => "Vendor Manager " . ($index + 1),
            ]);
            
            // Add 2-4 workers to each vendor warehouse
            $workerCount = rand(2, 4);
            for ($i = 1; $i <= $workerCount; $i++) {
                Worker::create([
                    'warehouse_id' => $warehouse->id,
                    'name' => "Vendor Worker " . ($index + 1) . "-" . $i,
                    'role' => ['Operator', 'Supervisor', 'Forklift Driver', 'Quality Inspector'][rand(0, 3)],
                    'shift' => ['Morning', 'Afternoon', 'Night'][rand(0, 2)],
                    'email' => "vendor.worker" . ($index + 1) . "-" . $i . "@beantrack.com",
                    'phone' => "555-" . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT) . "-" . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                    'address' => "Address for Vendor Worker " . ($index + 1) . "-" . $i,
                ]);
            }
            
            Log::info("Created warehouse for wholesaler {$wholesaler->id} with {$workerCount} workers");
        }
        
        // Add workers to supply centers (for admin)
        foreach ($supplyCenters as $index => $supplyCenter) {
            $workerCount = rand(3, 5);
            for ($i = 1; $i <= $workerCount; $i++) {
                Worker::create([
                    'supplycenter_id' => $supplyCenter->id,
                    'name' => "Supply Center Worker " . ($index + 1) . "-" . $i,
                    'role' => ['Operator', 'Supervisor', 'Quality Control', 'Logistics'][rand(0, 3)],
                    'shift' => ['Morning', 'Afternoon', 'Night'][rand(0, 2)],
                    'email' => "supply.worker" . ($index + 1) . "-" . $i . "@beantrack.com",
                    'phone' => "555-" . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT) . "-" . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                    'address' => "Address for Supply Worker " . ($index + 1) . "-" . $i,
                ]);
            }
            
            Log::info("Added {$workerCount} workers to supply center {$supplyCenter->id}");
        }
        
        Log::info('Warehouse and worker seeding completed');
    }
}
