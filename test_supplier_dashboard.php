<?php

// Test script to verify supplier dashboard data
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

echo "=== Supplier Dashboard Test ===\n";

// Test database connection
try {
    $connection = DB::connection();
    $connection->getPdo();
    echo "✓ Database connection successful\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test supplier users
echo "\n=== Supplier Users Test ===\n";
try {
    $suppliers = DB::table('users')->where('role', 'supplier')->get();
    echo "Found " . $suppliers->count() . " supplier users\n";
    
    foreach ($suppliers as $supplier) {
        echo "- Supplier: {$supplier->name} (ID: {$supplier->id})\n";
        
        // Check if they have a supplier record
        $supplierRecord = DB::table('supplier')->where('user_id', $supplier->id)->first();
        if ($supplierRecord) {
            echo "  ✓ Has supplier record (ID: {$supplierRecord->id})\n";
        } else {
            echo "  ✗ No supplier record found\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error fetching supplier users: " . $e->getMessage() . "\n";
}

// Test orders for suppliers
echo "\n=== Orders for Suppliers Test ===\n";
try {
    $orders = DB::table('orders')
        ->whereNotNull('supplier_id')
        ->where('status', 'pending')
        ->get();
    
    echo "Found " . $orders->count() . " pending orders for suppliers\n";
    
    foreach ($orders as $order) {
        echo "- Order {$order->id}: Supplier {$order->supplier_id}, Quantity: {$order->quantity}, Status: {$order->status}\n";
        
        // Check raw coffee relationship
        if ($order->raw_coffee_id) {
            $rawCoffee = DB::table('raw_coffee')->where('id', $order->raw_coffee_id)->first();
            if ($rawCoffee) {
                echo "  ✓ Raw Coffee: {$rawCoffee->coffee_type}\n";
            } else {
                echo "  ✗ Raw Coffee not found\n";
            }
        }
    }
} catch (Exception $e) {
    echo "✗ Error fetching orders: " . $e->getMessage() . "\n";
}

// Test getPendingOrders method functionality
echo "\n=== Dashboard Controller Test ===\n";
try {
    // Simulate authenticated supplier user
    $supplierUser = DB::table('users')->where('role', 'supplier')->first();
    if ($supplierUser) {
        echo "✓ Found supplier user for testing: {$supplierUser->name}\n";
        
        // Check supplier relationship
        $supplierRecord = DB::table('supplier')->where('user_id', $supplierUser->id)->first();
        if ($supplierRecord) {
            echo "✓ Supplier record exists: {$supplierRecord->name}\n";
            
            // Check orders for this supplier
            $supplierOrders = DB::table('orders')
                ->whereNotNull('supplier_id')
                ->where('supplier_id', $supplierRecord->id)
                ->where('status', 'pending')
                ->get();
            
            echo "✓ Found " . $supplierOrders->count() . " pending orders for this supplier\n";
            
            if ($supplierOrders->count() > 0) {
                echo "✓ Supplier dashboard will show real order data\n";
            } else {
                echo "⚠ No pending orders - supplier dashboard will show empty state\n";
            }
        } else {
            echo "✗ No supplier record found for user\n";
        }
    } else {
        echo "✗ No supplier users found\n";
    }
} catch (Exception $e) {
    echo "✗ Error testing dashboard controller: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
