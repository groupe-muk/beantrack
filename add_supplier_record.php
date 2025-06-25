<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Supplier;
use App\Models\User;
use App\Models\SupplyCenter;
use Illuminate\Support\Facades\DB;

// Find the Gideon supplier user
$user = User::where('email', 'supplier@beantrack.com')->first();

if (!$user) {
    echo "User with email supplier@beantrack.com not found.\n";
    exit(1);
}

// Get the first supply center for assignment
$supplyCenter = SupplyCenter::first();

if (!$supplyCenter) {
    echo "No supply center found in the database. Please create one first.\n";
    exit(1);
}

try {
    // Begin a transaction
    DB::beginTransaction();
    
    // Create a new supplier record
    $supplier = new Supplier();
    $supplier->user_id = $user->id;
    $supplier->supply_center_id = $supplyCenter->id;
    $supplier->name = 'Gideon Coffee Supplier';
    $supplier->contact_person = 'Gideon';
    $supplier->email = 'supplier@beantrack.com';
    $supplier->phone = $user->phone ?? '555-123-4599';
    $supplier->address = 'Gideon\'s Address';
    $supplier->registration_number = 'REG-GIDEON-1'; 
    $supplier->approved_date = now();
    $supplier->save();
    
    // Commit the transaction
    DB::commit();
    
    echo "Success! Supplier record created with ID: " . $supplier->id . "\n";
    echo "User ID: " . $user->id . "\n";
    echo "Name: " . $supplier->name . "\n";
    
} catch (\Exception $e) {
    // Roll back the transaction if something goes wrong
    DB::rollback();
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
}
