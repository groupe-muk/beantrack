<?php

use App\Http\Controllers\dashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageAttachmentController;
use App\Http\Controllers\VendorApplicationController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\columnChartController;


use App\Http\Controllers\OrderController;
use App\Http\Controllers\userManagerController;
use App\Http\Controllers\tableCardController;

use App\Http\Controllers\SupplyCentersController;
use App\Http\Controllers\InventoryController;

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
        Route::patch('/inventory/{rawCoffee}', [InventoryController::class, 'update'])->name('inventory.update');
        //Route::patch('/inventory/{coffeeProduct}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{rawCoffee}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

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

        // Report Management Routes for Admins - Protected by admin middleware
        Route::prefix('reports')->group(function () {
            Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
            Route::get('/stats', [App\Http\Controllers\ReportController::class, 'getStats'])->name('reports.stats');
            Route::get('/library', [App\Http\Controllers\ReportController::class, 'getReportLibrary'])->name('reports.library');
            Route::get('/historical', [App\Http\Controllers\ReportController::class, 'getHistoricalReports'])->name('reports.historical');
            Route::get('/templates', [App\Http\Controllers\ReportController::class, 'getTemplates'])->name('reports.templates');
            Route::get('/recipients', [App\Http\Controllers\ReportController::class, 'getRecipients'])->name('reports.recipients');
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
});

    // Vendor routes - also require auth  
    Route::middleware(['role:vendor'])->group(function () {
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

require __DIR__.'/auth.php';