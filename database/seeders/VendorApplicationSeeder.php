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
        $users = User::all();
        
        // Check if we have users available
        if ($users->count() === 0) {
            Log::warning('Cannot create vendor applications: No users available');
            return;
        }
          // Create vendor applications
        foreach (range(1, 5) as $i) {
            try {
                $user = $users->random();
                
                if ($user) {
                    VendorApplication::factory()->create([
                        'applicant_id' => $user->id,
                        'status' => $this->array_random(['pending', 'approved', 'rejected']),
                    ]);
                }
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
