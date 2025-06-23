<?php
// Check if the database is seeded correctly
// This script counts records in key tables

require_once __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

// Tables to check
$tables = [
    'users',
    'supply_centers',
    'suppliers', 
    'wholesalers',
    'raw_coffee',
    'coffee_product',
    'orders',
    'order_trackings',
    'inventory',
];

echo "======== DATABASE SEEDING CHECK ========\n";

// Use Laravel's DB facade to query the tables
$db = new \Illuminate\Database\Capsule\Manager;
$config = $app->make('config')->get('database.connections.mysql');

$db->addConnection([
    'driver'    => 'mysql',
    'host'      => $config['host'],
    'database'  => $config['database'],
    'username'  => $config['username'],
    'password'  => $config['password'],
    'charset'   => $config['charset'],
    'collation' => $config['collation'],
    'prefix'    => $config['prefix'],
]);

$db->setAsGlobal();
$db->bootEloquent();

// Check each table
foreach ($tables as $table) {
    $count = \Illuminate\Support\Facades\DB::table($table)->count();
    echo "{$table}: {$count} records\n";
    
    // For orders and order_trackings, show more details
    if ($table === 'orders') {
        echo "  - First 3 orders:\n";
        $orders = \Illuminate\Support\Facades\DB::table('orders')->take(3)->get();
        foreach ($orders as $order) {
            echo "    ID: {$order->id}, Status: {$order->status}, ";
            if ($order->supplier_id) {
                echo "Supplier ID: {$order->supplier_id}\n";
            } else {
                echo "Wholesaler ID: {$order->wholesaler_id}\n";
            }
        }
    }
    
    if ($table === 'order_trackings') {
        echo "  - First 3 order trackings:\n";
        $trackings = \Illuminate\Support\Facades\DB::table('order_trackings')->take(3)->get();
        foreach ($trackings as $tracking) {
            echo "    ID: {$tracking->id}, Order ID: {$tracking->order_id}, Status: {$tracking->status}, Location: {$tracking->location}\n";
        }
    }
}

echo "\n======== CHECK COMPLETE ========\n";
