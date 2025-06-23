<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\Wholesaler;
use App\Models\CoffeeProduct;
use App\Models\RawCoffee;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = Supplier::all();
        $wholesalers = Wholesaler::all();
        $coffeeProducts = CoffeeProduct::all();
        $rawCoffees = RawCoffee::all();
        
        // Log the count of related models for debugging
        Log::info("OrderSeeder - Available models: Suppliers: {$suppliers->count()}, Wholesalers: {$wholesalers->count()}, Products: {$coffeeProducts->count()}, Raw Coffee: {$rawCoffees->count()}");
        
        // Check if we have the necessary related models
        $hasSuppliers = $suppliers->count() > 0;
        $hasWholesalers = $wholesalers->count() > 0;
        $hasProducts = $coffeeProducts->count() > 0;
        $hasRawCoffee = $rawCoffees->count() > 0;
        
        // Early exit if we don't have required relationships
        if (!$hasSuppliers && !$hasWholesalers) {
            Log::warning('Cannot create orders: No suppliers or wholesalers available');
            return;
        }
        
        if (!$hasProducts && !$hasRawCoffee) {
            Log::warning('Cannot create orders: No coffee products or raw coffee available');
            return;
        }
        
        // Create orders directly with factory, which has been improved to handle validation
        $ordersCreated = 0;
        $maxAttempts = 20; // Try up to 20 times to create 10 valid orders
        
        for ($i = 0; $i < $maxAttempts && $ordersCreated < 10; $i++) {
            try {
                // Use our improved factory which throws exceptions for invalid data
                $order = Order::factory()->create();
                $ordersCreated++;
                
                // Double check that the order doesn't have any zero values for foreign keys
                $hasZeroValues = false;
                foreach (['supplier_id', 'wholesaler_id', 'raw_coffee_id', 'coffee_product_id'] as $key) {
                    if ($order->$key === '0' || $order->$key === 0) {
                        $hasZeroValues = true;
                        Log::error("Order created with zero value for {$key}: " . json_encode($order->toArray()));
                    }
                }
                
                // If we somehow created an order with zero values, delete it
                if ($hasZeroValues) {
                    $order->delete();
                    $ordersCreated--;
                    Log::info("Deleted invalid order with ID: {$order->id}");
                }
            } catch (\Exception $e) {
                Log::info('Order creation attempt failed: ' . $e->getMessage());
            }
        }
        
        Log::info("OrderSeeder completed: Created {$ordersCreated} valid orders after {$maxAttempts} attempts");
    }
      /**
     * Get a random element from an array
     */
    private function array_random($array) {
        return Arr::random($array);
    }
}
