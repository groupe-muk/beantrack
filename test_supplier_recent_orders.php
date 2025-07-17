<?php

// Test script to verify supplier recent orders functionality
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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

echo "=== Supplier Recent Orders Test ===\n";

// Test database connection
try {
    $connection = DB::connection();
    $connection->getPdo();
    echo "✓ Database connection successful\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test orders for suppliers
echo "\n=== Supplier Orders Test ===\n";
try {
    $suppliers = DB::table('supplier')->get();
    echo "Found " . $suppliers->count() . " suppliers\n";
    
    foreach ($suppliers as $supplier) {
        echo "\n- Supplier: {$supplier->name} (ID: {$supplier->id})\n";
        
        // Check all orders for this supplier
        $allOrders = DB::table('orders')
            ->where('supplier_id', $supplier->id)
            ->get();
        
        echo "  Total orders: " . $allOrders->count() . "\n";
        
        // Check fulfilled orders (delivered, shipped, confirmed)
        $fulfilledOrders = DB::table('orders')
            ->where('supplier_id', $supplier->id)
            ->whereIn('status', ['delivered', 'shipped', 'confirmed'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        echo "  Fulfilled orders: " . $fulfilledOrders->count() . "\n";
        
        // Check pending orders
        $pendingOrders = DB::table('orders')
            ->where('supplier_id', $supplier->id)
            ->where('status', 'pending')
            ->get();
        
        echo "  Pending orders: " . $pendingOrders->count() . "\n";
        
        if ($fulfilledOrders->count() > 0) {
            echo "  Recent fulfilled orders:\n";
            foreach ($fulfilledOrders->take(3) as $order) {
                echo "    - Order {$order->id}: {$order->quantity} kg, Status: {$order->status}\n";
            }
        }
    }
} catch (Exception $e) {
    echo "✗ Error fetching orders: " . $e->getMessage() . "\n";
}

// Test what would be displayed in dashboard
echo "\n=== Dashboard Data Test ===\n";
try {
    $firstSupplier = DB::table('supplier')->first();
    if ($firstSupplier) {
        echo "Testing dashboard for supplier: {$firstSupplier->name}\n";
        
        $recentOrders = DB::table('orders')
            ->join('raw_coffee', 'orders.raw_coffee_id', '=', 'raw_coffee.id')
            ->where('orders.supplier_id', $firstSupplier->id)
            ->whereIn('orders.status', ['delivered', 'shipped', 'confirmed'])
            ->orderBy('orders.created_at', 'desc')
            ->select('orders.*', 'raw_coffee.coffee_type')
            ->limit(4)
            ->get();
        
        echo "Dashboard would show " . $recentOrders->count() . " recent orders:\n";
        
        foreach ($recentOrders as $order) {
            $quantity = number_format((float)$order->quantity, 0) . ' kg';
            $status = ucfirst($order->status);
            $date = date('M d, Y', strtotime($order->created_at));
            
            echo "  - {$order->id} | Factory Order | {$order->coffee_type} | {$quantity} | {$status} | {$date}\n";
        }
        
        if ($recentOrders->count() == 0) {
            echo "  No recent orders found - fallback data would be displayed\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error in dashboard test: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
