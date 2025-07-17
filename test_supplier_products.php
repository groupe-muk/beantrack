<?php

// Test script to verify supplier products table data
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Bootstrap Laravel application
$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

echo "=== Supplier Products Table Test ===\n";

// Test database connection
try {
    $connection = DB::connection();
    $connection->getPdo();
    echo "✓ Database connection successful\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test supplier raw coffee products
echo "\n=== Supplier Raw Coffee Products Test ===\n";
try {
    $suppliers = DB::table('supplier')->get();
    echo "Found " . $suppliers->count() . " suppliers\n";
    
    foreach ($suppliers as $supplier) {
        echo "\n- Supplier: {$supplier->name} (ID: {$supplier->id})\n";
        
        // Check raw coffee products for this supplier
        $rawCoffeeProducts = DB::table('raw_coffee')
            ->where('supplier_id', $supplier->id)
            ->get();
        
        if ($rawCoffeeProducts->count() > 0) {
            echo "  ✓ Has " . $rawCoffeeProducts->count() . " raw coffee products\n";
            
            foreach ($rawCoffeeProducts as $product) {
                echo "    - {$product->coffee_type} (ID: {$product->id})\n";
                
                // Check inventory for this product
                $inventory = DB::table('inventory')
                    ->where('raw_coffee_id', $product->id)
                    ->first();
                
                if ($inventory) {
                    echo "      ✓ Stock: {$inventory->quantity_in_stock}\n";
                } else {
                    echo "      ✗ No inventory record\n";
                }
            }
        } else {
            echo "  ✗ No raw coffee products found\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error fetching supplier products: " . $e->getMessage() . "\n";
}

// Test inventory data
echo "\n=== Inventory Data Test ===\n";
try {
    $inventoryRecords = DB::table('inventory')
        ->whereNotNull('raw_coffee_id')
        ->join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
        ->select('inventory.*', 'raw_coffee.coffee_type', 'raw_coffee.supplier_id')
        ->get();
    
    echo "Found " . $inventoryRecords->count() . " inventory records for raw coffee\n";
    
    foreach ($inventoryRecords as $record) {
        echo "- {$record->coffee_type}: {$record->quantity_in_stock} units (Supplier: {$record->supplier_id})\n";
    }
} catch (Exception $e) {
    echo "✗ Error fetching inventory data: " . $e->getMessage() . "\n";
}

// Test what a specific supplier would see
echo "\n=== Specific Supplier Test ===\n";
try {
    $firstSupplier = DB::table('supplier')->first();
    if ($firstSupplier) {
        echo "Testing with supplier: {$firstSupplier->name}\n";
        
        $products = DB::table('raw_coffee')
            ->where('supplier_id', $firstSupplier->id)
            ->leftJoin('inventory', 'raw_coffee.id', '=', 'inventory.raw_coffee_id')
            ->select('raw_coffee.*', 'inventory.quantity_in_stock')
            ->get();
        
        echo "Products this supplier would see in their dashboard:\n";
        
        foreach ($products as $product) {
            $stock = $product->quantity_in_stock ?? 0;
            
            $status = 'In Stock';
            if ($stock == 0) {
                $status = 'Out of Stock';
            } elseif ($stock < 50) {
                $status = 'Limited Stock';
            }
            
            $price = number_format($product->price_per_kg ?? 25000, 0);
            
            echo "  - {$product->coffee_type}: {$price} UGX, Stock: {$stock}, Status: {$status}\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error in specific supplier test: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
