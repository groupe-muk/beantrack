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
        $this->command->info('Starting database seeding...');
        
        // Step 1: Seed essential data with our fixed seeder
        // This handles users, supply centers, suppliers, and wholesalers properly
        $this->command->info('Seeding users, supply centers, suppliers, and wholesalers...');
        $this->call([
            FixedSeeder::class,
            WorkerSeeder::class, 
            WorkforceAssignmentSeeder::class,
            RawCoffeeSeeder::class,
        ]);
        
        // Step 2: Seed products
        $this->command->info('Seeding coffee products and inventory...');
        $this->call([
            CoffeeProductSeeder::class,
            InventorySeeder::class,
        ]);
        
        // Step 7: Seed inventory updates and orders
        $this->command->info('Seeding inventory updates and orders...');
        $this->call([
            InventoryUpdateSeeder::class,
            OrderSeeder::class,
        ]);
        
        // Step 8: Seed order tracking
        $this->command->info('Seeding order tracking...');
        $this->call([
            OrderTrackingSeeder::class,
        ]);
        
        // Step 9: Seed remaining entities
        $this->command->info('Seeding remaining entities...');
        $this->call([
            MessageSeeder::class,
            ReportSeeder::class,
            VendorApplicationSeeder::class,
            AnalyticsDataSeeder::class,
        ]);
    }
}
