<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SupplyCenter;
use App\Models\Report;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Specifically get the admin user since that's more likely to receive reports
        $users = User::where('role', 'admin')->get();
        
        // If no admin users, get any user
        if ($users->count() === 0) {
            $users = User::all();
        }
        
        // Check if we have users available
        if ($users->count() === 0) {
            Log::warning('Cannot create reports: No users available');
            return;
        }
        
        $this->command->info('Found ' . $users->count() . ' users for report seeding');
        
        // Debug - list all users
        foreach ($users as $user) {
            $this->command->info("Available user ID: {$user->id}, Role: {$user->role}");
        }
          
        // Create reports with explicit user IDs
        foreach (range(1, 5) as $i) {
            try {
                // Get the admin user or first user
                $user = $users->first();
                
                // Debug info
                $this->command->info("Creating report with recipient ID: {$user->id}");
                
                // Generate a unique ID for report
                $reportId = 'R' . str_pad($i, 5, '0', STR_PAD_LEFT);
                
                Report::create([
                    'id' => $reportId,
                    'type' => fake()->randomElement(['inventory', 'order_summary', 'performance']),
                    'recipient_id' => $user->id, // Explicitly set to a valid user ID
                    'frequency' => fake()->randomElement(['weekly', 'monthly']),
                    'content' => json_encode(['summary' => fake()->sentence()]),
                    'last_sent' => fake()->optional()->dateTimeThisYear(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->command->info("Created report {$reportId} for recipient {$user->id}");
            } catch (\Exception $e) {
                Log::error('Failed to create report: ' . $e->getMessage());
            }
        }
    }
}
