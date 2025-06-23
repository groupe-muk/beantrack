<?php

// Script to fix and test database seeding

// First, let's try to load our classes
require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Get the kernel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Helper function to analyze models
function analyze_model($modelClass) {
    try {
        // Create model instance
        $model = new $modelClass();
        
        // Check model properties
        $tableName = $model->getTable();
        $keyType = $model->getKeyType();
        $incrementing = $model->getIncrementing() ? 'Yes' : 'No';
        $fillable = implode(', ', $model->getFillable());
        
        // Check if the model's table exists
        $tableExists = Schema::hasTable($tableName) ? 'Yes' : 'No';
        $recordCount = 0;
        
        if ($tableExists === 'Yes') {
            $recordCount = DB::table($tableName)->count();
        }
        
        echo "Model: " . class_basename($modelClass) . PHP_EOL;
        echo "   Table: {$tableName} (Exists: {$tableExists})" . PHP_EOL;
        echo "   Key Type: {$keyType}" . PHP_EOL;
        echo "   Incrementing: {$incrementing}" . PHP_EOL;
        echo "   Fillable: {$fillable}" . PHP_EOL;
        echo "   Records: {$recordCount}" . PHP_EOL;
        echo PHP_EOL;
        
        return ['model' => class_basename($modelClass), 'table' => $tableName, 'records' => $recordCount];
    } catch (\Exception $e) {
        echo "Error analyzing model {$modelClass}: " . $e->getMessage() . PHP_EOL;
        return ['model' => $modelClass, 'error' => $e->getMessage()];
    }
}

// Models to analyze
$models = [
    \App\Models\User::class,
    \App\Models\Supplier::class,
    \App\Models\Wholesaler::class,
    \App\Models\RawCoffee::class,
    \App\Models\CoffeeProduct::class,
    \App\Models\Inventory::class,
    \App\Models\InventoryUpdate::class,
    \App\Models\Order::class,
    \App\Models\OrderTracking::class,
    \App\Models\SupplyCenter::class,
    \App\Models\VendorApplication::class,
    \App\Models\Worker::class,
    \App\Models\WorkforceAssignment::class,
    \App\Models\Report::class,
    \App\Models\Message::class,
    \App\Models\AnalyticsData::class,
];

// Run analysis
echo "----------------------------------------------------" . PHP_EOL;
echo "ANALYZING MODELS" . PHP_EOL;
echo "----------------------------------------------------" . PHP_EOL;

$emptyTables = [];
foreach ($models as $model) {
    $result = analyze_model($model);
    if (isset($result['records']) && $result['records'] === 0) {
        $emptyTables[] = $result;
    }
}

// Create test data for empty tables using factories
echo PHP_EOL;
echo "----------------------------------------------------" . PHP_EOL;
echo "EMPTY TABLES SUMMARY" . PHP_EOL;
echo "----------------------------------------------------" . PHP_EOL;

if (count($emptyTables) === 0) {
    echo "No empty tables found." . PHP_EOL;
} else {
    foreach ($emptyTables as $table) {
        echo "- {$table['model']} ({$table['table']})" . PHP_EOL;
    }
}

// Test creating a coffee product
try {
    echo PHP_EOL;
    echo "----------------------------------------------------" . PHP_EOL;
    echo "TESTING COFFEE PRODUCT CREATION" . PHP_EOL;
    echo "----------------------------------------------------" . PHP_EOL;

    $coffeeProduct = \App\Models\CoffeeProduct::factory()->create();
    echo "Created coffee product: " . json_encode($coffeeProduct->toArray()) . PHP_EOL;
} catch (\Exception $e) {
    echo "Error creating coffee product: " . $e->getMessage() . PHP_EOL;
}

// Test creating an order
try {
    echo PHP_EOL;
    echo "----------------------------------------------------" . PHP_EOL;
    echo "TESTING ORDER CREATION" . PHP_EOL;
    echo "----------------------------------------------------" . PHP_EOL;

    $order = \App\Models\Order::factory()->create();
    echo "Created order: " . json_encode($order->toArray()) . PHP_EOL;
} catch (\Exception $e) {
    echo "Error creating order: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL;
echo "Script completed." . PHP_EOL;
