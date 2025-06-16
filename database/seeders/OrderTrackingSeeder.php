<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderTracking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class OrderTrackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::all();
        
        // Check if we have orders available
        if ($orders->count() === 0) {
            Log::warning('Cannot create order tracking: No orders available');
            return;
        }
          // Create order tracking entries for each order
        foreach ($orders as $order) {
            try {
                OrderTracking::factory()->create([
                    'order_id' => $order->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create order tracking: ' . $e->getMessage());
                Log::error('Order ID: ' . $order->id);
            }
        }
    }
}
