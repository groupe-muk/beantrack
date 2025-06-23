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
     */
    public function run(): void
    {
        // First, ensure there are suppliers in the database
        $suppliers = Supplier::all();
        
        // Check if we have suppliers available
        if ($suppliers->count() === 0) {
            Log::warning('Cannot create raw coffee: No suppliers available');
            return;
        }
        
        $this->command->info('Found ' . $suppliers->count() . ' suppliers for raw coffee seeding');
        
        // For debugging, list the IDs of all suppliers
        foreach ($suppliers as $supplier) {
            $this->command->info("Available supplier ID: {$supplier->id}");
        }
          
        // Create raw coffee entries using factory and existing supplier IDs
        foreach (range(1, 10) as $index) {
            try {
                // Get a random supplier
                $supplier = $suppliers->random();
                
                // Debug info
                $this->command->info("Creating raw coffee with supplier ID: {$supplier->id}");
                
                // Generate a unique ID for raw coffee
                $rawCoffeeId = 'RC' . str_pad($index, 5, '0', STR_PAD_LEFT);
                
                // Use factory to create raw coffee with a valid supplier ID
                RawCoffee::factory()->create([
                    'id' => $rawCoffeeId,
                    'supplier_id' => $supplier->id,
                ]);
                
                $this->command->info("Created raw coffee {$rawCoffeeId} with supplier ID {$supplier->id}");
            } catch (\Exception $e) {
                Log::error('Failed to create raw coffee: ' . $e->getMessage());
                Log::error('Supplier ID: ' . ($supplier->id ?? 'N/A'));
            }
        }
    }
}
