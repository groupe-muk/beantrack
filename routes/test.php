<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Models\Supplier;
use App\Models\Wholesaler;
use App\Models\User;
use Illuminate\Support\Facades\Log;

// Test route for checking if suppliers and vendors are loaded correctly
Route::get('/test-chat-data', function () {
    $controller = new ChatController();
    $suppliers = Supplier::all();
    $wholesalers = Wholesaler::all();
    
    return response()->json([
        'suppliers_count' => $suppliers->count(),
        'suppliers' => $suppliers->toArray(),
        'wholesalers_count' => $wholesalers->count(),
        'wholesalers' => $wholesalers->toArray()
    ]);
});
