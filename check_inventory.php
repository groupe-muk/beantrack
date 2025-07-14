<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\RawCoffee;
use App\Models\Inventory;
use App\Models\CoffeeProduct;

echo "=== Raw Coffee Records ===\n";
$rawCoffees = RawCoffee::all();
foreach ($rawCoffees as $rc) {
    echo "ID: {$rc->id}, Type: {$rc->coffee_type}, Grade: {$rc->grade}\n";
}

echo "\n=== Inventory Records ===\n";
$inventories = Inventory::with('rawCoffee', 'coffeeProduct')->get();
foreach ($inventories as $inv) {
    if ($inv->rawCoffee) {
        echo "Raw Coffee Inventory: {$inv->rawCoffee->coffee_type} Grade {$inv->rawCoffee->grade} - Qty: {$inv->quantity_in_stock}\n";
    }
    if ($inv->coffeeProduct) {
        echo "Coffee Product Inventory: {$inv->coffeeProduct->name} {$inv->coffeeProduct->category} - Qty: {$inv->quantity_in_stock}\n";
    }
}

echo "\n=== Coffee Product Records ===\n";
$coffeeProducts = CoffeeProduct::all();
foreach ($coffeeProducts as $cp) {
    echo "ID: {$cp->id}, Name: {$cp->name}, Category: {$cp->category}\n";
} 