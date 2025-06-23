<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User; // Import the User model

class ReportFactory extends Factory
{
    public function definition(): array
    {
        static $reportNumber = 1;
        
        // Get an admin user - they're the most likely to receive reports
        $adminUsers = User::where('role', 'admin')->get();
        $user = $adminUsers->isNotEmpty() 
            ? $adminUsers->random() 
            : User::inRandomOrder()->first();
        
        // Ensure we have a valid user ID
        $userId = $user ? $user->id : null;
        if (!$userId) {
            // If no user is available, don't proceed with creating the report
            throw new \Exception('No valid user available for report creation');
        }
            
        return [
            'id' => 'R' . str_pad($reportNumber++, 5, '0', STR_PAD_LEFT),
            'type' => $this->faker->randomElement(['inventory', 'order_summary', 'performance']),
            'recipient_id' => $userId, // Set to a valid admin User ID
            'frequency' => $this->faker->randomElement(['weekly', 'monthly']),
            'content' => json_encode(['summary' => $this->faker->sentence()]),
            'last_sent' => $this->faker->optional()->dateTimeThisYear(),
        ];
    }
}