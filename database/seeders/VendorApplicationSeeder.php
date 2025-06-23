<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VendorApplication;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class VendorApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get specific users that would be applying for vendor status
        $users = User::whereNotIn('role', ['supplier', 'vendor', 'admin'])->get();
        
        // If no specific users, get any user
        if ($users->count() === 0) {
            $users = User::all();
        }
        
        // Check if we have users available
        if ($users->count() === 0) {
            Log::warning('Cannot create vendor applications: No users available');
            return;
        }
        
        $this->command->info('Found ' . $users->count() . ' users for vendor application seeding');
        
        // Debug - list all users
        foreach ($users as $user) {
            $this->command->info("Available user ID: {$user->id}, Role: {$user->role}");
        }
          
        // Create vendor applications with the first valid user
        $user = $users->first();
        
        // Check if we have a valid user
        if (!$user) {
            Log::warning('Cannot create vendor applications: No valid users available');
            return;
        }
          
        // Create vendor applications with explicit user IDs
        foreach (range(1, 5) as $i) {
            try {
                // Debug info
                $this->command->info("Creating vendor application with applicant ID: {$user->id}");
                
                // Generate a unique ID for vendor application
                $applicationId = 'VA' . str_pad($i, 4, '0', STR_PAD_LEFT);
                
                VendorApplication::create([
                    'id' => $applicationId,
                    'applicant_id' => $user->id, // Explicitly set to a valid user ID
                    'financial_data' => json_encode(['revenue' => fake()->numberBetween(10000, 100000)]),
                    'references' => json_encode([fake()->company(), fake()->company()]),
                    'license_data' => json_encode(['license' => fake()->uuid]),
                    'status' => fake()->randomElement(['pending', 'under_review', 'approved', 'rejected']),
                    'visit_scheduled' => fake()->optional()->date(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->command->info("Created vendor application {$applicationId} for applicant {$user->id}");
            } catch (\Exception $e) {
                Log::error('Failed to create vendor application: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Get a random element from an array
     */
    private function array_random($array) {
        return $array[array_rand($array)];
    }
}
