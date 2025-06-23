<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Supplier;
use App\Models\Wholesaler;
use App\Models\RawCoffee;
use App\Models\CoffeeProduct;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        // Check what data is available
        $hasSuppliers = Supplier::count() > 0;
        $hasWholesalers = Wholesaler::count() > 0;
        $hasRawCoffee = RawCoffee::count() > 0;
        $hasProducts = CoffeeProduct::count() > 0;
        
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
        
        // Get valid IDs from the database with careful handling to avoid nulls or 0s
        $supplierId = null;
        $wholesalerId = null;
        $rawCoffeeId = null; 
        $coffeeProductId = null;
        
        if ($isSupplierOrder && $hasSuppliers && $hasRawCoffee) {
            $supplier = Supplier::inRandomOrder()->first();
            $rawCoffee = RawCoffee::inRandomOrder()->first();
            
            if ($supplier && $rawCoffee) {
                $supplierId = $supplier->id;
                $rawCoffeeId = $rawCoffee->id;
            } else {
                throw new \Exception("Cannot create supplier order: missing required models");
            }
        } elseif (!$isSupplierOrder && $hasWholesalers && $hasProducts) {
            $wholesaler = Wholesaler::inRandomOrder()->first();
            $coffeeProduct = CoffeeProduct::inRandomOrder()->first();
            
            if ($wholesaler && $coffeeProduct) {
                $wholesalerId = $wholesaler->id;
                $coffeeProductId = $coffeeProduct->id;
            } else {
                throw new \Exception("Cannot create wholesaler order: missing required models");
            }
        } else {
            // If we don't have the required related models, throw an exception
            throw new \Exception("Cannot create order: missing required models");
        }
        
        return [
            'id' => null,
            'supplier_id' => $supplierId, // Always either a valid ID or null, never 0 or falsy value
            'wholesaler_id' => $wholesalerId, // Always either a valid ID or null, never 0 or falsy value
            'raw_coffee_id' => $rawCoffeeId, // Always either a valid ID or null, never 0 or falsy value
            'coffee_product_id' => $coffeeProductId, // Always either a valid ID or null, never 0 or falsy value
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'shipped', 'delivered']),
            'quantity' => $this->faker->randomFloat(2, 10, 1000),
            'total_price' => $this->faker->randomFloat(2, 100, 10000), // Always have a price
        ];
    }
}
