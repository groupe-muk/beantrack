<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\columnChartController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MessageAttachmentController;
use App\Http\Controllers\tableCardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\supplierInventoryController;
use App\Http\Controllers\SupplyCentersController;
use App\Http\Controllers\userManagerController;
use App\Http\Controllers\VendorApplicationController;
use App\Http\Controllers\SupplierApplicationController;
use App\Http\Controllers\vendorInventoryController;

Route::get('/sample', [columnChartController::class, 'showColumnChart'])->name('column.chart');


// Onboarding route - entry point
Route::get('/', [AuthController::class, 'showOnboarding'])->name('onboarding');

// Role selection from onboarding page
Route::post('/role-select', [AuthController::class, 'roleSelection'])->name('role.select');

// Vendor Application Routes (Public - no authentication required)
Route::controller(VendorApplicationController::class)->group(function () {
    Route::get('/vendor', 'vendorOnboarding')->name('vendor.onboarding');
    Route::get('/apply', 'create')->name('vendor.apply');
    Route::post('/apply', 'store')->name('vendor.apply.store');
    Route::get('/check-status', 'checkStatus')->name('vendor.check-status');
    Route::get('/application/status', 'status')->name('vendor.application.status');
});

// Supplier Application Routes (Public - no authentication required)
Route::controller(SupplierApplicationController::class)->group(function () {
    Route::get('/supplier', 'supplierOnboarding')->name('supplier.onboarding');
    Route::get('/supplier/apply', 'create')->name('supplier.apply');
    Route::post('/supplier/apply', 'store')->name('supplier.apply.store');
    Route::get('/supplier/check-status', 'checkStatus')->name('supplier.check-status');
    Route::get('/supplier/application/status', 'status')->name('supplier.application.status');
});

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
    Route::get('/dashboard', [dashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [dashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    
    // Debug route to check user role
    Route::get('/debug/user-role', function() {
        $user = Auth::user();
        return response()->json([
            'user_id' => $user->id,
            'role' => $user->role,
            'wholesaler' => $user->wholesaler,
            'is_vendor' => $user->role === 'vendor'
        ]);
    })->name('debug.user.role');
    
    // Chat Routes
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/unread', [ChatController::class, 'getUnreadCount'])->name('chat.unread');
    Route::post('/chat/mark-read', [ChatController::class, 'markAsRead'])->name('chat.mark-read');
    Route::get('/chat/{userId}', [ChatController::class, 'chatRoom'])->name('chat.room');
    Route::post('/chat/send',[ChatController::class, 'send'])->name('chat.send');
    // Chat Test Route
    Route::match(['get', 'post'], '/chat/test/send', [App\Http\Controllers\ChatTestController::class, 'testSend'])->name('chat.test');
    Route::post('/chat/receive', function (Request $request) {
        try {
            // Get the input data - JavaScript sends JSON, not form data
            $jsonData = json_decode($request->getContent(), true);
            
            $message = $jsonData['message'] ?? $request->input('message');
            $userData = $jsonData['user'] ?? $request->input('user');
            $timestamp = $jsonData['timestamp'] ?? $request->input('timestamp');
            $messageId = $jsonData['messageId'] ?? $request->input('messageId', uniqid());
            
            // Log the incoming data for debugging
            \Log::info('Chat receive route data', [
                'message' => $message,
                'userData' => $userData,
                'userDataType' => gettype($userData),
                'timestamp' => $timestamp,
                'messageId' => $messageId
            ]);
            
            // Create a user object from the data
            // The user data comes as an array from JavaScript, but the component expects an object
            $user = is_array($userData) ? (object) $userData : $userData;
            
            // Return only the chat bubble component, not a full layout
            return response()->view('components.chat.left-chat-bubble', [
                'message' => $message,
                'user' => $user,
                'timestamp' => $timestamp,
                'messageId' => $messageId
            ])->header('Content-Type', 'text/html');
            
        } catch (\Exception $e) {
            \Log::error('Chat receive error', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'json_data' => json_decode($request->getContent(), true),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response('Error loading message', 500);
        }
    })->name('chat.receive');
    
    // Message Attachments
    Route::post('/chat/attachments', [MessageAttachmentController::class, 'store'])->name('chat.attachments.store');
    Route::get('/chat/attachments/{fileName}', [MessageAttachmentController::class, 'show'])->name('chat.attachments.show');


    /*Route::get('/dashboard', [AuthController::class, 'showApp'])->name('dashboard');*/
    
    // Role-specific routes - Admin routes (require auth)
    Route::middleware(['role:admin'])->group(function () {
        //User management routes
        Route::get('admin/users', [userManagerController::class, 'index'])->name('admin.users.index');
        Route::post('admin/users', [userManagerController::class, 'store'])->name('admin.users.store');
        Route::patch('admin/users/{user}', [userManagerController::class, 'update'])->name('admin.users.update');
        Route::delete('admin/users/{user}', [userManagerController::class, 'destroy'])->name('admin.users.destroy');

        // Vendor Application Management API Routes
        Route::get('api/vendor-applications', [userManagerController::class, 'getVendorApplications'])->name('api.vendor-applications');
        Route::post('admin/vendor-applications/{application}/update-status', [userManagerController::class, 'updateVendorApplicationStatus'])->name('admin.vendor-applications.update-status');
        Route::post('admin/vendor-applications/{application}/reject', [userManagerController::class, 'rejectVendorApplication'])->name('admin.vendor-applications.reject-status');
        Route::post('admin/vendor-applications/{application}/add-to-system', [userManagerController::class, 'addVendorToSystem'])->name('admin.vendor-applications.add-to-system');
        Route::post('admin/vendor-applications/{application}/retry-validation', [VendorApplicationController::class, 'retryValidation'])->name('admin.vendor-applications.retry-validation');
      
        // Supplier Application Management API Routes
        Route::get('api/supplier-applications', [userManagerController::class, 'getSupplierApplications'])->name('api.supplier-applications');
        Route::get('api/supplier-applications/{application}', [userManagerController::class, 'getSupplierApplicationDetails'])->name('api.supplier-applications.details');
        Route::post('admin/supplier-applications/{application}/update-status', [userManagerController::class, 'updateSupplierApplicationStatus'])->name('admin.supplier-applications.update-status');
        Route::post('admin/supplier-applications/{application}/reject', [userManagerController::class, 'rejectSupplierApplication'])->name('admin.supplier-applications.reject-status');
        Route::post('admin/supplier-applications/{application}/add-to-system', [userManagerController::class, 'addSupplierToSystem'])->name('admin.supplier-applications.add-to-system');
        Route::post('admin/supplier-applications/{application}/retry-validation', [SupplierApplicationController::class, 'retryValidation'])->name('admin.supplier-applications.retry-validation');
      
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
        Route::get('/inventory/stats', [InventoryController::class, 'stats'])->name('inventory.stats');
        Route::get('/inventory/details/{type}/{id}', [InventoryController::class, 'getItemDetails'])->name('inventory.details');

        // Raw Coffee routes
        Route::patch('/inventory/raw-coffee/{rawCoffee}', [InventoryController::class, 'updateRawCoffee'])->name('inventory.update.rawCoffee');
        Route::delete('/inventory/raw-coffee/{rawCoffee}', [InventoryController::class, 'destroyRawCoffee'])->name('inventory.destroy.rawCoffee');

        // Coffee Product routes  
        Route::patch('/inventory/coffee-product/{coffeeProduct}', [InventoryController::class, 'updateCoffeeProduct'])->name('inventory.update.coffeeProduct');
        Route::delete('/inventory/coffee-product/{coffeeProduct}', [InventoryController::class, 'destroyCoffeeProduct'])->name('inventory.destroy.coffeeProduct');




        // Vendor Application Management Routes (Admin only)
        Route::prefix('admin/vendor-applications')->name('admin.vendor-applications.')->controller(VendorApplicationController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{application}', 'show')->name('show');
            Route::post('/{application}/approve', 'approve')->name('approve');
            Route::post('/{application}/reject', 'reject')->name('reject');
            Route::post('/{application}/schedule-visit', 'scheduleVisit')->name('schedule-visit');
            Route::get('/{application}/download/{type}', 'downloadDocument')->name('download-document');
            Route::post('/{application}/retry-validation', 'retryValidation')->name('retry-validation');
        });

        // Supplier Application Management Routes (Admin only)
        Route::prefix('admin/supplier-applications')->name('admin.supplier-applications.')->controller(SupplierApplicationController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{application}', 'show')->name('show');
            Route::post('/{application}/approve', 'approve')->name('approve');
            Route::post('/{application}/reject', 'reject')->name('reject');
            Route::post('/{application}/schedule-visit', 'scheduleVisit')->name('schedule-visit');
            Route::get('/{application}/download/{type}', 'downloadDocument')->name('download-document');
            Route::post('/{application}/retry-validation', 'retryValidation')->name('retry-validation');
        });


        // Report Management Routes for Admins - Protected by admin middleware
        Route::prefix('reports')->group(function () {
            Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
            Route::get('/stats', [App\Http\Controllers\ReportController::class, 'getStats'])->name('reports.stats');
            Route::get('/library', [App\Http\Controllers\ReportController::class, 'getReportLibrary'])->name('reports.library');
            Route::get('/historical', [App\Http\Controllers\ReportController::class, 'getHistoricalReports'])->name('reports.historical');
            Route::get('/templates', [App\Http\Controllers\ReportController::class, 'getTemplates'])->name('reports.templates');
            Route::get('/recipients', [App\Http\Controllers\ReportController::class, 'getRecipients'])->name('reports.recipients');
            
            // Recipient CRUD operations
            Route::post('/recipients', [App\Http\Controllers\ReportController::class, 'storeRecipient'])->name('reports.recipients.store');
            Route::put('/recipients/{id}', [App\Http\Controllers\ReportController::class, 'updateRecipient'])->name('reports.recipients.update');
            Route::delete('/recipients/{id}', [App\Http\Controllers\ReportController::class, 'deleteRecipient'])->name('reports.recipients.delete');
            
            Route::post('/', [App\Http\Controllers\ReportController::class, 'store'])->name('reports.store');
            Route::post('/adhoc', [App\Http\Controllers\ReportController::class, 'generateAdhocReport'])->name('reports.adhoc');
            Route::get('/{report}/edit', [App\Http\Controllers\ReportController::class, 'edit'])->name('reports.edit');
            Route::put('/{report}', [App\Http\Controllers\ReportController::class, 'update'])->name('reports.update');
            Route::post('/{report}/generate', [App\Http\Controllers\ReportController::class, 'generateNow'])->name('reports.generate');
            Route::post('/{report}/pause', [App\Http\Controllers\ReportController::class, 'pause'])->name('reports.pause');
            Route::post('/{report}/resume', [App\Http\Controllers\ReportController::class, 'resume'])->name('reports.resume');
            Route::delete('/{report}', [App\Http\Controllers\ReportController::class, 'destroy'])->name('reports.destroy');
            Route::get('/{report}/download', [App\Http\Controllers\ReportController::class, 'download'])->name('reports.download');
            Route::get('/{report}/view', [App\Http\Controllers\ReportController::class, 'view'])->name('reports.view');
        });
    });

    // Supplier routes - also require auth
    Route::middleware(['role:supplier'])->group(function () {
    // Supplier-specific reports routes
    Route::get('/reports/supplier', [App\Http\Controllers\ReportController::class, 'supplierIndex'])->name('reports.supplier');
    
    // API endpoints for supplier reports (with supplier_only filtering) - use different URLs
    Route::prefix('supplier-reports')->group(function () {
        Route::get('/stats', [App\Http\Controllers\ReportController::class, 'getStats'])->name('reports.supplier.stats');
        Route::get('/library', [App\Http\Controllers\ReportController::class, 'getReportLibrary'])->name('reports.supplier.library');
        Route::get('/historical', [App\Http\Controllers\ReportController::class, 'getHistoricalReports'])->name('reports.supplier.historical');
        Route::get('/templates', [App\Http\Controllers\ReportController::class, 'getTemplates'])->name('reports.supplier.templates');
        Route::get('/recipients', [App\Http\Controllers\ReportController::class, 'getRecipients'])->name('reports.supplier.recipients');
        Route::post('/', [App\Http\Controllers\ReportController::class, 'store'])->name('reports.supplier.store');
        Route::post('/adhoc', [App\Http\Controllers\ReportController::class, 'generateAdhocReport'])->name('reports.supplier.adhoc');
        Route::get('/{report}/edit', [App\Http\Controllers\ReportController::class, 'edit'])->name('reports.supplier.edit');
        Route::put('/{report}', [App\Http\Controllers\ReportController::class, 'update'])->name('reports.supplier.update');
        Route::post('/{report}/generate', [App\Http\Controllers\ReportController::class, 'generateNow'])->name('reports.supplier.generate');
        Route::post('/{report}/pause', [App\Http\Controllers\ReportController::class, 'pause'])->name('reports.supplier.pause');
        Route::post('/{report}/resume', [App\Http\Controllers\ReportController::class, 'resume'])->name('reports.supplier.resume');
        Route::delete('/{report}', [App\Http\Controllers\ReportController::class, 'destroy'])->name('reports.supplier.destroy');
        Route::get('/{report}/download', [App\Http\Controllers\ReportController::class, 'download'])->name('reports.supplier.download');
        Route::get('/{report}/view', [App\Http\Controllers\ReportController::class, 'view'])->name('reports.supplier.view');
    });
      
       // Supplier inventory routes
         Route::get('/supplierInventory', [supplierInventoryController::class, 'index'])->name('supplierInventory.index');
         Route::post('/supplierInventory', [supplierInventoryController::class, 'store'])->name('supplierInventory.store')->middleware(['auth','role:supplier']);
         Route::get('/supplierInventory/stats', [supplierInventoryController::class, 'stats'])->name('supplierInventory.stats');
         Route::get('/supplierInventory/details/{type}', [supplierInventoryController::class, 'getDetails'])->name('supplierInventory.details');
         Route::get('/supplierInventory/{id}/edit', [supplierInventoryController::class, 'edit'])->name('supplierInventory.edit');
         Route::patch('/supplierInventory/{id}', [supplierInventoryController::class, 'update'])->name('supplierInventory.update');
         Route::delete('/supplierInventory/{id}', [supplierInventoryController::class, 'destroy'])->name('supplierInventory.destroy');
         Route::get('/supplierInventory/details/{type}',[supplierInventoryController::class, 'getDetails'])
             ->name('supplierInventory.details')
             ->middleware(['auth', 'role:supplier']);
         Route::put('/supplierInventory/item/{id}', [supplierInventoryController::class, 'updateItem'])
             ->name('supplierInventory.updateItem')
             ->middleware(['auth', 'role:supplier']);
         Route::get('/supplierInventory/item/{id}', [supplierInventoryController::class, 'getItem'])
             ->name('supplierInventory.getItem')
             ->middleware(['auth', 'role:supplier']);



    });

    // Vendor routes - also require auth (vendors have 'vendor' role in DB)
    Route::middleware(['role:vendor'])->group(function () {

        // Vendor order management routes
        Route::get('/vendor/orders', [OrderController::class, 'vendorIndex'])->name('orders.vendor.index');
        Route::get('/vendor/orders/create', [OrderController::class, 'vendorCreate'])->name('orders.vendor.create');
        Route::post('/vendor/orders', [OrderController::class, 'vendorStore'])->name('orders.vendor.store');
        Route::get('/vendor/orders/{order}', [OrderController::class, 'vendorShow'])->name('orders.vendor.show');
        Route::patch('/vendor/orders/{order}/cancel', [OrderController::class, 'vendorCancel'])->name('orders.vendor.cancel');
        
        // Debug route to test vendor order store
        Route::post('/debug/vendor/orders', function(Request $request) {
            \Log::info('Debug: Vendor order store route accessed', [
                'user' => Auth::user(),
                'request_data' => $request->all(),
                'route' => request()->route()->getName()
            ]);
            return response()->json(['message' => 'Debug route accessed successfully']);
        })->name('debug.vendor.orders');
        
        // Temporary debug route to test POST method
        Route::match(['GET', 'POST'], '/debug/test-post', function(Request $request) {
            \Log::info('Debug: Test POST route accessed', [
                'method' => $request->method(),
                'user' => Auth::user(),
                'request_data' => $request->all(),
                'route' => request()->route()->getName()
            ]);
            return response()->json([
                'message' => 'Debug test route accessed successfully',
                'method' => $request->method(),
                'data' => $request->all()
            ]);
        })->name('debug.test.post');
        
        // Vendor reports routes
        Route::get('/reports/vendor', [App\Http\Controllers\ReportController::class, 'vendorIndex'])->name('reports.vendor');
        
        // API endpoints for vendor reports (with vendor_only filtering)
        Route::prefix('vendor-reports')->group(function () {
            Route::get('/stats', [App\Http\Controllers\ReportController::class, 'getStats'])->name('reports.vendor.stats');
            Route::get('/library', [App\Http\Controllers\ReportController::class, 'getReportLibrary'])->name('reports.vendor.library');
            Route::get('/historical', [App\Http\Controllers\ReportController::class, 'getHistoricalReports'])->name('reports.vendor.historical');
            Route::get('/templates', [App\Http\Controllers\ReportController::class, 'getTemplates'])->name('reports.vendor.templates');
            Route::get('/recipients', [App\Http\Controllers\ReportController::class, 'getRecipients'])->name('reports.vendor.recipients');
            Route::post('/', [App\Http\Controllers\ReportController::class, 'store'])->name('reports.vendor.store');
            Route::post('/adhoc', [App\Http\Controllers\ReportController::class, 'generateAdhocReport'])->name('reports.vendor.adhoc');
            Route::get('/{report}/edit', [App\Http\Controllers\ReportController::class, 'edit'])->name('reports.vendor.edit');
            Route::put('/{report}', [App\Http\Controllers\ReportController::class, 'update'])->name('reports.vendor.update');
            Route::post('/{report}/generate', [App\Http\Controllers\ReportController::class, 'generateNow'])->name('reports.vendor.generate');
            Route::post('/{report}/pause', [App\Http\Controllers\ReportController::class, 'pause'])->name('reports.vendor.pause');
            Route::post('/{report}/resume', [App\Http\Controllers\ReportController::class, 'resume'])->name('reports.vendor.resume');
            Route::delete('/{report}', [App\Http\Controllers\ReportController::class, 'destroy'])->name('reports.vendor.destroy');
            Route::get('/{report}/download', [App\Http\Controllers\ReportController::class, 'download'])->name('reports.vendor.download');
            Route::get('/{report}/view', [App\Http\Controllers\ReportController::class, 'view'])->name('reports.vendor.view');
        });
        
       // Vendor inventory routes
        Route::get('/vendorInventory', [vendorInventoryController::class, 'index'])->name('vendorInventory.index');
        Route::get('/vendorInventory/stats', [vendorInventoryController::class, 'stats'])->name('vendorInventory.stats');
        Route::get('/vendorInventory/details/{id}', [vendorInventoryController::class, 'details'])->name('vendorInventory.details');
        Route::get('/vendorInventory/{id}/edit', [vendorInventoryController::class, 'edit'])->name('vendorInventory.edit');
        Route::post('/vendorInventory', [vendorInventoryController::class, 'store'])->name('vendorInventory.store');
        Route::patch('/vendorInventory/{coffeeProduct}', [vendorInventoryController::class, 'update'])->name('vendorInventory.update');
        Route::delete('/vendorInventory/{coffeeProduct}', [vendorInventoryController::class, 'destroy'])->name('vendorInventory.destroy');



    });
}); // Close auth middleware group



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





// Route::get('warehouses', SupplyCentersController::class);
// Route::get('warehouses.staff', WorkerController::class);
Route::get('/SupplyCenters', [SupplyCentersController::class, 'supplycenters'])->name('supplycenters');



Route::get('/supplycenters', [SupplyCentersController::class, 'supplycenters'])->name('supplycenters.supplycenters');
Route::post('/supplycenters', [SupplyCentersController::class, 'store'])->name('supplycenters.store');
Route::patch('/supplycenters/{supplycenter}', [SupplyCentersController::class, 'update'])->name('supplycenters.update');
Route::delete('/supplycenters/{supplycenters}', [SupplyCentersController::class, 'destroy'])->name('supplycenters.destroy');

Route::post('/supplycenters/{supplycenter}/worker', [SupplyCentersController::class, 'storeWorker'])->name('worker.store');
Route::patch('/worker/{worker}', [SupplyCentersController::class, 'updateWorker'])->name('worker.update');

require __DIR__.'/auth.php';



