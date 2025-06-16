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
        $users = User::all();
        $supplyCenters = SupplyCenter::all();
        
        // Check if we have users and supply centers available
        if ($users->count() === 0 || $supplyCenters->count() === 0) {
            Log::warning('Cannot create reports: No users or supply centers available');
            return;
        }
          // Create reports
        foreach (range(1, 5) as $i) {
            try {
                // The Report factory already sets recipient_id to a random user
                // We don't need supply_center_id as it doesn't exist in the reports table
                Report::factory()->create();
            } catch (\Exception $e) {
                Log::error('Failed to create report: ' . $e->getMessage());
            }
        }
    }
}
