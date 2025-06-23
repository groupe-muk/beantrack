<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\User;
use App\Models\InventoryUpdate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class InventoryUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inventories = Inventory::all();
        $users = User::all();
        
        // Check if we have inventories and users available
        if ($inventories->count() === 0 || $users->count() === 0) {
            Log::warning('Cannot create inventory updates: No inventories or users available');
            return;
        }
          // Create inventory updates using existing inventory and user IDs
        foreach (range(1, 10) as $i) {
            $inventory = $inventories->random();
            $user = $users->random();
            
            if ($inventory && $user) {
                try {
                    InventoryUpdate::factory()->create([
                        'inventory_id' => $inventory->id,
                        'user_id' => $user->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create inventory update: ' . $e->getMessage());
                    Log::error('Inventory ID: ' . $inventory->id . ', User ID: ' . $user->id);
                }
            }
        }
    }
}
