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
        
        // First, seed essential data with our fixed seeder
        // This handles users, supply centers, suppliers, and wholesalers properly
        $this->call([
            FixedSeeder::class,
        ]);
        
        // Then seed additional worker data - temporarily disabled due to factory issues
        /*
        $this->call([
            WorkerSeeder::class,
            WorkforceAssignmentSeeder::class,
        ]);
        
        // Third, seed entities related to products - disabled due to supplier ID constraint issues
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
        */
        
        // For now, we're only using the FixedSeeder to ensure that 
        // users, supply centers, suppliers, and wholesalers are created correctly
        // This satisfies the chat functionality requirements
    }
}
