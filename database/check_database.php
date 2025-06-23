<?php

// Script to check database tables
require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Get the kernel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Get all tables
$tables = DB::select('SHOW TABLES');
$tableColumn = 'Tables_in_' . env('DB_DATABASE');

echo "DATABASE TABLE SUMMARY\n";
echo "=====================\n\n";

foreach ($tables as $table) {
    $tableName = $table->$tableColumn;
    $count = DB::table($tableName)->count();
    $structure = Schema::getColumnListing($tableName);
    
    echo str_pad($tableName, 30) . " | " . str_pad($count . " records", 15) . " | Columns: " . count($structure) . "\n";
}

echo "\n\nTABLE DETAILS\n";
echo "=============\n\n";

// Get details for specific tables
$detailedTables = [
    'supplier', 'wholesaler', 'supply_centers', 
    'raw_coffee', 'coffee_product', 
    'inventory', 'orders'
];

foreach ($detailedTables as $tableName) {
    echo "\nTABLE: " . strtoupper($tableName) . "\n";
    
    $records = DB::table($tableName)->get();
    
    if (count($records) > 0) {
        // Get column names
        $columns = array_keys((array)$records[0]);
        
        // Print header
        foreach ($columns as $column) {
            echo str_pad(substr($column, 0, 15), 16);
        }
        echo "\n";
        
        // Print separator
        foreach ($columns as $column) {
            echo str_pad('', 16, '-');
        }
        echo "\n";
        
        // Print data
        foreach ($records as $record) {
            foreach ((array)$record as $value) {
                echo str_pad(substr((string)$value, 0, 15), 16);
            }
            echo "\n";
        }
    } else {
        echo "No records found.\n";
    }
    
    echo "\n";
}
