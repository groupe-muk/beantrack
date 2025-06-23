<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\SupplyCenter;
use App\Models\Supplier;
use App\Models\Wholesaler;
use Illuminate\Support\Facades\Log;

class FixedSeeder extends Seeder
{
    /**
     * Run a fixed seeder to populate users, supply centers, suppliers, and wholesalers
     */
    public function run(): void
    {
        // Step 1: Clear existing data to avoid constraint issues
        $this->command->info('Clearing existing data...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('supplier')->truncate();
        DB::table('wholesaler')->truncate();
        DB::table('supply_centers')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Step 2: Create users with correct IDs and roles
        $this->command->info('Creating users...');
        $adminUser = DB::table('users')->insertGetId([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '123-456-7890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create supplier users
        $supplierUsers = [];
        for ($i = 1; $i <= 5; $i++) {
            $supplierUsers[] = DB::table('users')->insertGetId([
                'name' => "Supplier User {$i}",
                'email' => "supplier{$i}@example.com",
                'password' => Hash::make('password'),
                'role' => 'supplier',
                'phone' => "123-456-78{$i}0",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Create vendor users
        $vendorUsers = [];
        for ($i = 1; $i <= 5; $i++) {
            $vendorUsers[] = DB::table('users')->insertGetId([
                'name' => "Vendor User {$i}",
                'email' => "vendor{$i}@example.com",
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'phone' => "123-456-79{$i}0",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Step 3: Create supply centers
        $this->command->info('Creating supply centers...');
        $supplyCenters = [];
        for ($i = 1; $i <= 3; $i++) {
            $supplyCenters[] = DB::table('supply_centers')->insertGetId([
                'name' => "Supply Center {$i}",
                'location' => "Location {$i}",
                'capacity' => rand(1000, 5000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
          // Step 4: Create suppliers
        $this->command->info('Creating suppliers...');
        
        // Get all users and supply centers with their actual string IDs from the database
        $supplierUsers = DB::table('users')->where('role', 'supplier')->pluck('id')->toArray();
        $supplyCenters = DB::table('supply_centers')->pluck('id')->toArray();
        
        $supplierCount = count($supplierUsers);
        $supplyCenterCount = count($supplyCenters);
        
        if ($supplierCount === 0) {
            $this->command->error("No supplier users found!");
            return;
        }
        
        if ($supplyCenterCount === 0) {
            $this->command->error("No supply centers found!");
            return;
        }
        
        $this->command->info("Found {$supplierCount} supplier users and {$supplyCenterCount} supply centers");
        
        for ($i = 0; $i < $supplierCount; $i++) {
            $userId = $supplierUsers[$i];
            $supplyCenterId = $supplyCenters[$i % $supplyCenterCount];
            
            $this->command->info("Using user ID {$userId} and supply center ID {$supplyCenterId}");
            
            try {
                DB::table('supplier')->insert([
                    'user_id' => $userId,
                    'supply_center_id' => $supplyCenterId,
                    'name' => "Supplier Company " . ($i + 1),
                    'contact_person' => "Contact Person " . ($i + 1),
                    'email' => "supplier-company{$i}@example.com",
                    'phone' => "555-123-456{$i}",
                    'address' => "Address line " . ($i + 1),
                    'registration_number' => "REG-SUP-" . ($i + 1),
                    'approved_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->command->info("Created supplier {$i} with user ID {$userId}");
            } catch (\Exception $e) {
                $this->command->error("Failed to create supplier: " . $e->getMessage());
                Log::error("Failed to create supplier: " . $e->getMessage());
                Log::error("User ID: {$userId}, Supply Center ID: {$supplyCenterId}");
            }
        }
          // Step 5: Create wholesalers
        $this->command->info('Creating wholesalers...');
        
        // Get all vendor users with their actual string IDs from the database
        $vendorUsers = DB::table('users')->where('role', 'vendor')->pluck('id')->toArray();
        $vendorCount = count($vendorUsers);
        
        if ($vendorCount === 0) {
            $this->command->error("No vendor users found!");
            return;
        }
        
        $this->command->info("Found {$vendorCount} vendor users");
        
        for ($i = 0; $i < $vendorCount; $i++) {
            $userId = $vendorUsers[$i];
            $this->command->info("Using user ID {$userId}");
            
            try {
                DB::table('wholesaler')->insert([
                    'user_id' => $userId,
                    'name' => "Wholesaler Company " . ($i + 1),
                    'contact_person' => "Contact Person " . ($i + 1),
                    'email' => "wholesaler-company{$i}@example.com",
                    'phone' => "555-987-654{$i}",
                    'address' => "Address line " . ($i + 1),
                    'distribution_region' => "Region " . ($i + 1),
                    'registration_number' => "REG-WHO-" . ($i + 1),
                    'approved_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->command->info("Created wholesaler {$i} with user ID {$userId}");
            } catch (\Exception $e) {
                $this->command->error("Failed to create wholesaler: " . $e->getMessage());
                Log::error("Failed to create wholesaler: " . $e->getMessage());
                Log::error("User ID: {$userId}");
            }
        }
        
        $this->command->info('Seeding completed successfully!');
    }
}
