
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;

// Get all inventory items
Route::get('/inventory', [InventoryController::class, 'index']);

// Store a new inventory item
Route::post('/inventory', [InventoryController::class, 'store']);

// (Optional) Get a single inventory item
Route::get('/inventory/{id}', [InventoryController::class, 'show']);

// (Optional) Update an inventory item
Route::put('/inventory/{id}', [InventoryController::class, 'update']);

// (Optional) Delete an inventory item
Route::delete('/inventory/{id}', [InventoryController::class, 'destroy']);