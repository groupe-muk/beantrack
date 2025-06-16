<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call individual seeders in the correct order to maintain relationships
        
        // First, seed independent entities
        $this->call([
            UserSeeder::class,
            SupplyCenterSeeder::class,
        ]);
        
        // Second, seed entities that depend on users and/or supply centers
        $this->call([
            WorkerSeeder::class,
            SupplierSeeder::class,
            WholesalerSeeder::class,
            WorkforceAssignmentSeeder::class,
        ]);
        
        // Third, seed entities related to products
        $this->call([
            RawCoffeeSeeder::class,
            CoffeeProductSeeder::class,
            InventorySeeder::class,
        ]);
        
        // Fourth, seed entities that depend on inventory
        $this->call([
            InventoryUpdateSeeder::class,
            OrderSeeder::class,
        ]);
        
        // Fifth, seed entities that depend on orders
        $this->call([
            OrderTrackingSeeder::class,
        ]);
        
        // Lastly, seed remaining entities
        $this->call([
            MessageSeeder::class,
            ReportSeeder::class,
            VendorApplicationSeeder::class,
            AnalyticsDataSeeder::class,
        ]);
    }
}
