<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SupplyCenter;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */    public function run(): void
    {
        Log::info('Starting supplier seeding process');
        
        // Create fresh supply centers if needed
        if (SupplyCenter::count() === 0) {
            Log::info('Creating supply centers');
            SupplyCenter::factory(3)->create();
        }
        
        // Get supplier users and supply centers with their actual IDs
        $supplierUsers = User::where('role', 'supplier')->get();
        $supplyCenters = SupplyCenter::all();
        
        if ($supplierUsers->count() === 0) {
            Log::info('Creating supplier users');
            for ($i = 1; $i <= 5; $i++) {
                User::create([
                    'name' => "Supplier User {$i}",
                    'email' => "supplier{$i}@example.com",
                    'password' => bcrypt('password'),
                    'role' => 'supplier',
                    'phone' => "123-456-78{$i}0"
                ]);
            }
            $supplierUsers = User::where('role', 'supplier')->get();
        }
        
        Log::info('Found ' . $supplierUsers->count() . ' supplier users and ' . $supplyCenters->count() . ' supply centers');
        
        // Create suppliers with the correct user_id and supply_center_id
        foreach ($supplierUsers as $index => $user) {
            $supplyCenter = $supplyCenters[$index % $supplyCenters->count()];
            
            try {
                Supplier::create([
                    'user_id' => $user->id,
                    'supply_center_id' => $supplyCenter->id,
                    'name' => "Supplier Company " . ($index + 1),
                    'contact_person' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? "555-123-456{$index}",
                    'address' => "Address line " . ($index + 1),
                    'registration_number' => "REG-SUP-" . ($index + 1),
                    'approved_date' => now()
                ]);
                Log::info("Created supplier for user ID {$user->id}");
            } catch (\Exception $e) {
                Log::error('Failed to create supplier: ' . $e->getMessage());
                Log::error("User ID: {$user->id}, Supply Center ID: {$supplyCenter->id}");
            }
        }
        
        Log::info('Supplier seeding complete');
    }
}
