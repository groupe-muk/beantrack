<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\RawCoffee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class RawCoffeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */    public function run(): void
    {
        $suppliers = Supplier::all();
        
        // Check if we have suppliers available
        if ($suppliers->count() === 0) {
            Log::warning('Cannot create raw coffee: No suppliers available');
            return;
        }

        // Ensure canonical coffee types exist
        $types = ['Arabica', 'Robusta', 'Liberica'];

        foreach ($types as $type) {
            RawCoffee::firstOrCreate(
                ['coffee_type' => $type],   // unique key
                [
                    'supplier_id' => $suppliers->random()->id,
                    'grade'       => 'A',
                ]
            );
        }

        // Create additional raw coffee entries using existing supplier IDs
        foreach (range(1, 7) as $i) {
            $supplier = $suppliers->random();
            
            if ($supplier) {
                try {
                    RawCoffee::factory()->create([
                        'supplier_id' => $supplier->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create raw coffee: ' . $e->getMessage());
                    Log::error('Supplier ID: ' . $supplier->id);
                }
            }
        }
    }
}
