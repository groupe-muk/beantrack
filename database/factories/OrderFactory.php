<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Supplier;
use App\Models\Wholesaler;
use App\Models\RawCoffee;
use App\Models\CoffeeProduct;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        Log::info('Starting OrderFactory definition');
        // Check what data is available
        $hasSuppliers = Supplier::count() > 0;
        $hasWholesalers = Wholesaler::count() > 0;
        $hasRawCoffee = RawCoffee::count() > 0;
        $hasProducts = CoffeeProduct::count() > 0;
        
        Log::info("Available data: suppliers={$hasSuppliers}, wholesalers={$hasWholesalers}, rawCoffee={$hasRawCoffee}, products={$hasProducts}");

        // Determine order type based on available data
        $isSupplierOrder = false;
        
        // If both supplier and wholesaler are available, randomly choose
        if ($hasSuppliers && $hasWholesalers) {
            $isSupplierOrder = $this->faker->boolean();
        }
        // Otherwise use whatever is available
        else {
            $isSupplierOrder = $hasSuppliers;
        }
        
        Log::info("Creating " . ($isSupplierOrder ? "supplier" : "wholesaler") . " order");
        
        // Get valid IDs from the database with careful handling to avoid nulls or 0s
        $supplierId = null;
        $wholesalerId = null;
        $rawCoffeeId = null; 
        $coffeeProductId = null;
        // Let the database trigger handle ID generation 
        $orderId = null;
        
        Log::info("Will let database trigger handle order ID generation");
        
        try {
            if ($isSupplierOrder && $hasSuppliers && $hasRawCoffee) {
                $supplier = Supplier::inRandomOrder()->first();
                $rawCoffee = RawCoffee::inRandomOrder()->first();
                
                if ($supplier && $rawCoffee) {
                    $supplierId = $supplier->id;
                    $rawCoffeeId = $rawCoffee->id;
                    Log::info("Supplier order: supplier_id={$supplierId}, raw_coffee_id={$rawCoffeeId}");
                } else {
                    Log::error("Cannot create supplier order: supplier=" . ($supplier ? "found" : "not found") . ", rawCoffee=" . ($rawCoffee ? "found" : "not found"));
                    throw new \Exception("Cannot create supplier order: missing required models");
                }            } elseif (!$isSupplierOrder && $hasWholesalers && $hasProducts) {
                $wholesaler = Wholesaler::inRandomOrder()->first();
                $coffeeProduct = CoffeeProduct::inRandomOrder()->first();
                
                if ($wholesaler && $coffeeProduct) {
                    $wholesalerId = $wholesaler->id;
                    $coffeeProductId = $coffeeProduct->id;
                    Log::info("Wholesaler order: wholesaler_id={$wholesalerId}, coffee_product_id={$coffeeProductId}");
                } else {
                    Log::error("Cannot create wholesaler order: wholesaler=" . ($wholesaler ? "found" : "not found") . ", coffeeProduct=" . ($coffeeProduct ? "found" : "not found"));
                    throw new \Exception("Cannot create wholesaler order: missing required models");
                }
            } else {
                // If we don't have the required related models, throw an exception
                Log::error("Cannot create order: No valid order type could be determined with the available data");
                throw new \Exception("Cannot create order: No valid data available for either supplier or wholesaler order");
            }
            
            // Generate the order data
            $quantity = $this->faker->numberBetween(10, 100);
            $unitPrice = $isSupplierOrder ? 
                ($rawCoffee ? $rawCoffee->price_per_kg : $this->faker->numberBetween(5, 20)) :
                ($coffeeProduct ? $this->faker->numberBetween(10, 30) : $this->faker->numberBetween(10, 30));
            $totalPrice = $quantity * $unitPrice;
            
            Log::info("Order details: quantity={$quantity}, unitPrice={$unitPrice}, totalPrice={$totalPrice}");
                 return [
            'id' => null, // Let database trigger handle ID
            'supplier_id' => $supplierId,
            'wholesaler_id' => $wholesalerId,
            'raw_coffee_id' => $rawCoffeeId,
            'coffee_product_id' => $coffeeProductId,
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'shipped', 'delivered']), // Match allowed enum values
                'quantity' => $quantity,
                'total_price' => $totalPrice,
                'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        } catch (\Exception $e) {
            Log::error("Exception in OrderFactory: {$e->getMessage()}");
            throw $e;
        }
    }
}