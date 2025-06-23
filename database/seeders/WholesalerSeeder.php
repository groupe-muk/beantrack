<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SupplyCenter;
use App\Models\Wholesaler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class WholesalerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */    public function run(): void
    {
        Log::info('Starting wholesaler seeding process');
        
        // Get vendor users with their actual IDs
        $vendorUsers = User::where('role', 'vendor')->get();
        
        if ($vendorUsers->count() === 0) {
            Log::info('Creating vendor users');
            for ($i = 1; $i <= 5; $i++) {
                User::create([
                    'name' => "Vendor User {$i}",
                    'email' => "vendor{$i}@example.com",
                    'password' => bcrypt('password'),
                    'role' => 'vendor',
                    'phone' => "123-456-79{$i}0"
                ]);
            }
            $vendorUsers = User::where('role', 'vendor')->get();
        }
        
        Log::info('Found ' . $vendorUsers->count() . ' vendor users');
        
        // Create wholesalers with the correct user_id
        foreach ($vendorUsers as $index => $user) {
            try {
                Wholesaler::create([
                    'user_id' => $user->id,
                    'name' => "Wholesaler Company " . ($index + 1),
                    'contact_person' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? "555-987-654{$index}",
                    'address' => "Address line " . ($index + 1),
                    'distribution_region' => "Region " . ($index + 1),
                    'registration_number' => "REG-WHO-" . ($index + 1),
                    'approved_date' => now()
                ]);
                Log::info("Created wholesaler for user ID {$user->id}");
            } catch (\Exception $e) {
                Log::error('Failed to create wholesaler: ' . $e->getMessage());
                Log::error("User ID: {$user->id}");
            }
        }
        
        Log::info('Wholesaler seeding complete');
    }
}
