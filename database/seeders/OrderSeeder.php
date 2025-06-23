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
        
        // Check if we have the necessary related models
        if (($suppliers->count() === 0 && $wholesalers->count() === 0) || 
            ($coffeeProducts->count() === 0 && $rawCoffees->count() === 0)) {
            Log::warning('Cannot create orders: Missing necessary related models');
            return;
        }
        
        // Create orders
        foreach (range(1, 10) as $i) {
            // Randomly decide if this is a supplier or wholesaler order
            $isSupplierOrder = rand(0, 1) === 1;
            
            // Randomly decide if this is a raw coffee or coffee product order
            $isRawCoffeeOrder = rand(0, 1) === 1;
            
            $order = [
                'status' => $this->array_random(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
                'payment_status' => $this->array_random(['unpaid', 'paid', 'refunded']),
                'payment_method' => $this->array_random(['credit_card', 'bank_transfer', 'cash']),
                'notes' => 'Order #' . $i,
            ];
              // Set the supplier or wholesaler
            try {
                if ($isSupplierOrder && $suppliers->count() > 0) {
                    $supplier = $suppliers->random();
                    if ($supplier) {
                        $order['supplier_id'] = $supplier->id;
                        $order['wholesaler_id'] = null;
                    } else {
                        continue;
                    }
                } elseif ($wholesalers->count() > 0) {
                    $wholesaler = $wholesalers->random();
                    if ($wholesaler) {
                        $order['supplier_id'] = null;
                        $order['wholesaler_id'] = $wholesaler->id;
                    } else {
                        continue;
                    }
                } else {
                    continue; // Skip if no valid ordering entity
                }
                
                // Set the product type
                if ($isRawCoffeeOrder && $rawCoffees->count() > 0) {
                    $rawCoffee = $rawCoffees->random();
                    if ($rawCoffee) {
                        $order['raw_coffee_id'] = $rawCoffee->id;
                        $order['coffee_product_id'] = null;
                        $order['type'] = 'raw_coffee';
                    } else {
                        continue;
                    }
                } elseif ($coffeeProducts->count() > 0) {
                    $coffeeProduct = $coffeeProducts->random();
                    if ($coffeeProduct) {
                        $order['raw_coffee_id'] = null;
                        $order['coffee_product_id'] = $coffeeProduct->id;
                        $order['type'] = 'coffee_product';
                    } else {
                        continue;
                    }
                } else {
                    continue; // Skip if no valid product
                }
                
                Order::factory()->create($order);
            } catch (\Exception $e) {
                Log::error('Failed to create order: ' . $e->getMessage());
                Log::error('Order data: ' . json_encode($order));
            }
        }
    }
      /**
     * Get a random element from an array
     */
    private function array_random($array) {
        return Arr::random($array);
    }
}
