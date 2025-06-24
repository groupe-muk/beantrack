<?php

use App\Http\Controllers\dashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageAttachmentController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\columnChartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\userManagerController;

use App\Http\Controllers\PusherController;
use Illuminate\Http\Request;


use App\Http\Controllers\tableCardController;

use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SupplyCentersController;

Route::get('/sample', [columnChartController::class, 'showColumnChart'])->name('column.chart');


// Onboarding route - entry point
Route::get('/', [AuthController::class, 'showOnboarding'])->name('onboarding');

// Role selection from onboarding page
Route::post('/role-select', [AuthController::class, 'roleSelection'])->name('role.select');

// Authentication routes for guests
Route::middleware(['guest'])->controller(AuthController::class)->group(function () {
    Route::get('/create', 'showcreate')->name('show.create');
    Route::get('/login', 'showlogin')->name('show.login');
    Route::post('/create', 'create')->name('create');
    Route::post('/login', 'login')->name('login');
});

// Routes for authenticated users
Route::middleware(['auth'])->group(function () {
    Route::post('/logout',[AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Chat Routes
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{userId}', [ChatController::class, 'chatRoom'])->name('chat.room');
    Route::post('/chat/send',[ChatController::class, 'send'])->name('chat.send');
    // Chat Test Route
    Route::match(['get', 'post'], '/chat/test/send', [App\Http\Controllers\ChatTestController::class, 'testSend'])->name('chat.test');
    Route::post('/chat/receive', function (Request $request) {
        // Since we're expecting JSON data
        $message = $request->input('message');
        $user = $request->input('user');
        $timestamp = $request->input('timestamp');
        
        // Return only the chat bubble component, not a full layout
        return response()->view('components.chat.left-chat-bubble', [
            'message' => $message,
            'user' => $user,
            'timestamp' => $timestamp,
            'messageId' => uniqid()
        ])->header('Content-Type', 'text/html');
    })->name('chat.receive');
    
    // Message Attachments
    Route::post('/chat/attachments', [MessageAttachmentController::class, 'store'])->name('chat.attachments.store');
    Route::get('/chat/attachments/{fileName}', [MessageAttachmentController::class, 'show'])->name('chat.attachments.show');


    /*Route::get('/dashboard', [AuthController::class, 'showApp'])->name('dashboard');*/
    
    // Role-specific routes
});

    Route::middleware(['role:admin'])->group(function () {
        //User management routes
        Route::get('admin/users', [userManagerController::class, 'index'])->name('admin.users.index');
        Route::post('admin/users', [userManagerController::class, 'store'])->name('admin.users.store');
        Route::patch('admin/users/{user}', [userManagerController::class, 'update'])->name('admin.users.update');
        Route::delete('admin/users/{user}', [userManagerController::class, 'destroy'])->name('admin.users.destroy');
      
       // Order routes
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/create', [OrderController::class, 'create'])->name('create');
            Route::post('/', [OrderController::class, 'store'])->name('store');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
            Route::put('/{order}', [OrderController::class, 'update'])->name('update');
            Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
            Route::put('/{order}/status', [OrderController::class, 'updateStatus'])->name('update-status');
            Route::get('/api/stats', [OrderController::class, 'getOrderStats'])->name('api.stats');
        });

        //Inventory routes
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::patch('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

   
    
    Route::middleware(['role:supplier'])->group(function () {
        // Supplier routes
    });
    
    Route::middleware(['role:vendor'])->group(function () {
        // Vendor routes
    });
});




/*Route::view('dashboard', 'dashboard')

// Routes for authenticated users
Route::middleware(['auth'])->group(function () {
    Route::post('/logout',[AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*Route::get('/dashboard', [AuthController::class, 'showApp'])->name('dashboard');*/
    
    // Role-specific routes
    Route::middleware(['role:admin'])->group(function () {
        //User management routes
        Route::get('admin/users', [userManagerController::class, 'index'])->name('admin.users.index');
        Route::post('admin/users', [userManagerController::class, 'store'])->name('admin.users.store');
        Route::patch('admin/users/{user}', [userManagerController::class, 'update'])->name('admin.users.update');
        Route::delete('admin/users/{user}', [userManagerController::class, 'destroy'])->name('admin.users.destroy');
    });
    
    Route::middleware(['role:supplier'])->group(function () {
        // Supplier routes
    });
    
    Route::middleware(['role:vendor'])->group(function () {
        // Vendor routes
    });




/*Route::view('dashboard', 'dashboard')



Route::get('/SupplyCenters', function () {
    return view('SupplyCenters.SupplyCenters');
})->name('SupplyCenters');
Route::get('/SupplyCenter1', [SupplyCentersController::class,  'shownSupplyCenter1'])->name('show.SupplyCenter1'); 
Route::get('/SupplyCenter2', [SupplyCentersController::class, 'shownSupplyCenter2'])->name('show.SupplyCenter2'); 
Route::get('/SupplyCenter3', [SupplyCentersController::class, 'shownSupplyCenter3'])->name('show.SupplyCenter3'); 



//Route::post('/warehouseA', [WarehouseController::class, 'warehouseA'])->name('warehouseA');
//Route::post('/workers/{worker}/transfer', [WarehouseController::class, 'transfer'])->name('workers.transfer');
//Route::delete('/workers/{worker}', [WarehouseController::class, 'destroy'])->name('workers.destroy');


Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');*/

// Route::middleware(['auth'])->group(function () {
//     Route::redirect('settings', 'settings/profile');




require __DIR__.'/auth.php';
 



