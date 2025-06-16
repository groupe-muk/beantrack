<?php

namespace Database\Seeders;

use App\Models\SupplyCenter;
use App\Models\AnalyticsData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class AnalyticsDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supplyCenters = SupplyCenter::all();
        
        // Check if we have supply centers available
        if ($supplyCenters->count() === 0) {
            Log::warning('Cannot create analytics data: No supply centers available');
            return;
        }
          // Create analytics data - no supply_center_id column in the table
        foreach (range(1, 5) as $i) {
            try {
                // Just create analytics data without supply_center_id since it doesn't exist
                AnalyticsData::factory()->create();
            } catch (\Exception $e) {
                Log::error('Failed to create analytics data: ' . $e->getMessage());
            }
        }
    }
}
