<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User; // Import the User model

class VendorApplicationFactory extends Factory
{
    public function definition(): array
    {
        static $applicationNumber = 1;
        
        // Try to get users who might be applying for vendor status
        $users = User::whereNotIn('role', ['supplier', 'vendor', 'admin'])->get();
        
        // If no specific users found, get any user
        if ($users->isEmpty()) {
            $users = User::all();
        }
        
        // If still no users, throw exception as we need a valid user ID
        if ($users->isEmpty()) {
            throw new \Exception('No valid user available for vendor application creation');
        }
        
        $user = $users->random();
        
        return [
            'id' => 'VA' . str_pad($applicationNumber++, 4, '0', STR_PAD_LEFT),
            'applicant_id' => $user->id, // Ensure we always have a valid user ID
            'financial_data' => json_encode(['revenue' => $this->faker->numberBetween(10000, 100000)]),
            'references' => json_encode([$this->faker->company(), $this->faker->company()]),
            'license_data' => json_encode(['license' => $this->faker->uuid]),
            'status' => $this->faker->randomElement(['pending', 'under_review', 'approved', 'rejected']),
            'visit_scheduled' => $this->faker->optional()->date(),
        ];
    }
}