<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call individual seeders in the correct order to maintain relationships
        
        // First, seed essential data with our fixed seeder
        // This handles users, supply centers, suppliers, and wholesalers properly
        $this->call([
            FixedSeeder::class,
        ]);
        
        // Create some direct raw coffee entries to support the chain of dependencies
        \App\Models\RawCoffee::create([
            'id' => 'RC00001',
            'supplier_id' => \App\Models\Supplier::first()->id,
            'coffee_type' => 'Arabica',
            'grade' => 'A',
            'screen_size' => '16',
            'defect_count' => 2,
            'harvest_date' => now()->subMonths(3),
        ]);
        
        \App\Models\RawCoffee::create([
            'id' => 'RC00002',
            'supplier_id' => \App\Models\Supplier::first()->id,
            'coffee_type' => 'Robusta',
            'grade' => 'B',
            'screen_size' => '14',
            'defect_count' => 3,
            'harvest_date' => now()->subMonths(2),
        ]);
        
        // Create some coffee products based on the raw coffee
        \App\Models\CoffeeProduct::create([
            'raw_coffee_id' => 'RC00001',
            'category' => 'Premium',
            'name' => 'Mountain Blend',
            'product_form' => 'beans',
            'roast_level' => 'medium',
            'production_date' => now()->subWeeks(2),
        ]);
        
        \App\Models\CoffeeProduct::create([
            'raw_coffee_id' => 'RC00002',
            'category' => 'Standard',
            'name' => 'Morning Brew',
            'product_form' => 'ground',
            'roast_level' => 'dark',
            'production_date' => now()->subWeeks(1),
        ]);
        
        // Create 10 orders - mix of supplier and wholesaler orders
        $suppliers = \App\Models\Supplier::all();
        $wholesalers = \App\Models\Wholesaler::all();
        $rawCoffees = \App\Models\RawCoffee::all();
        $coffeeProducts = \App\Models\CoffeeProduct::all();
        $statuses = ['pending', 'confirmed', 'shipped', 'delivered']; // Order statuses
        $trackingStatuses = ['shipped', 'in-transit', 'delivered']; // Order tracking statuses
        
        // Create 5 supplier orders with different raw coffees
        for ($i = 0; $i < 5; $i++) {
            $supplier = $suppliers->random();
            $rawCoffee = $rawCoffees->random();
            $quantity = rand(20, 200);
            $pricePerUnit = rand(5, 25);
            $totalPrice = $quantity * $pricePerUnit;
            
            \App\Models\Order::create([
                'supplier_id' => $supplier->id,
                'raw_coffee_id' => $rawCoffee->id,
                'status' => $statuses[array_rand($statuses)],
                'quantity' => $quantity,
                'total_price' => $totalPrice,
                'created_at' => now()->subDays(rand(1, 60)),
                'updated_at' => now()->subDays(rand(0, 5)),
            ]);
        }
        
        // Create 5 wholesaler orders with different coffee products
        for ($i = 0; $i < 5; $i++) {
            $wholesaler = $wholesalers->random();
            $coffeeProduct = $coffeeProducts->random();
            $quantity = rand(5, 100);
            $pricePerUnit = rand(15, 40);
            $totalPrice = $quantity * $pricePerUnit;
            
            \App\Models\Order::create([
                'wholesaler_id' => $wholesaler->id,
                'coffee_product_id' => $coffeeProduct->id,
                'status' => $statuses[array_rand($statuses)],
                'quantity' => $quantity,
                'total_price' => $totalPrice,
                'created_at' => now()->subDays(rand(1, 60)),
                'updated_at' => now()->subDays(rand(0, 5)),
            ]);
        }
        
        // Create inventory records for raw coffee in each supply center
        $rawCoffees = \App\Models\RawCoffee::all();
        $supplyCenters = \App\Models\SupplyCenter::all();
        
        foreach ($rawCoffees as $rawCoffee) {
            foreach ($supplyCenters as $supplyCenter) {
                \App\Models\Inventory::create([
                    'raw_coffee_id' => $rawCoffee->id,
                    'coffee_product_id' => null,
                    'quantity_in_stock' => rand(50, 500),
                    'supply_center_id' => $supplyCenter->id,
                    'last_updated' => now(),
                ]);
            }
        }
        
        // Create inventory records for coffee products in each supply center
        $coffeeProducts = \App\Models\CoffeeProduct::all();
        
        foreach ($coffeeProducts as $coffeeProduct) {
            foreach ($supplyCenters as $supplyCenter) {
                \App\Models\Inventory::create([
                    'raw_coffee_id' => null,
                    'coffee_product_id' => $coffeeProduct->id,
                    'quantity_in_stock' => rand(20, 200),
                    'supply_center_id' => $supplyCenter->id,
                    'last_updated' => now(),
                ]);
            }
        }
        
        // Create order tracking records for each order
        $orders = \App\Models\Order::all();
        $locations = [
            'Warehouse A', 'Distribution Center B', 'Shipping Hub C', 
            'Supply Center D', 'Transit Station E', 'Customer Facility'
        ];
        $trackingStatuses = ['shipped', 'in-transit', 'delivered']; // Match allowed enum values in the table
        
        foreach ($orders as $order) {
            // Add 1-3 tracking updates for each order
            $trackingCount = rand(1, 3);
            
            for ($i = 0; $i < $trackingCount; $i++) {
                $location = $locations[array_rand($locations)];
                
                // For orders with multiple tracking entries, create a logical progression
                if ($trackingCount > 1) {
                    if ($i === 0) {
                        $status = 'shipped';
                    } elseif ($i === $trackingCount - 1) {
                        $status = 'delivered';
                    } else {
                        $status = 'in-transit';
                    }
                } else {
                    // If only one tracking entry, use a random status
                    $status = $trackingStatuses[array_rand($trackingStatuses)];
                }
                
                // Direct insert to bypass Laravel's timestamps handling
                \Illuminate\Support\Facades\DB::table('order_trackings')->insert([
                    'order_id' => $order->id,
                    'status' => $status,
                    'location' => $location,
                    'updated_at' => now()->subDays($trackingCount - $i),
                ]);
            }
        }
        
        // Seed price histories for products
        $this->call([
            PriceHistorySeeder::class,
            DemandHistorySeeder::class,
        ]);
        
        // For now, we're only using the FixedSeeder to ensure that 
        // users, supply centers, suppliers, and wholesalers are created correctly
        // This satisfies the chat functionality requirements
    }
}
