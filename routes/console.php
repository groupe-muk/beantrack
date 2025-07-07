<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\ChatController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily forecasts generation
Schedule::command('generate:daily-forecasts')->dailyAt('02:00');

// Create a test endpoint to check the Chat Controller
Artisan::command('test:chat-controller', function () {
    $this->info('Testing ChatController...');
    
    // Get an admin user
    $adminUser = User::where('role', 'admin')->first();
    
    if (!$adminUser) {
        $this->error('No admin user found. Please run the UserSeeder first.');
        return 1;
    }
    
    $this->info("Using admin user: {$adminUser->name} (ID: {$adminUser->id})");
    
    // Set the authenticated user to the admin user
    Auth::login($adminUser);
    
    // Create a new instance of the ChatController
    $controller = new ChatController();
    
    try {
        // Call the index method
        $response = $controller->index();
        
        // Check the view data
        $viewData = $response->getData();
        
        $this->info("Suppliers count: " . count($viewData['suppliers']));
        $this->info("Vendors count: " . count($viewData['vendors']));
        
        if (count($viewData['suppliers']) > 0) {
            $this->info("Supplier sample: " . json_encode($viewData['suppliers'][0]));
        }
        
        if (count($viewData['vendors']) > 0) {
            $this->info("Vendor sample: " . json_encode($viewData['vendors'][0]));
        }
        
        $this->info('Test completed successfully!');
        return 0;
    } catch (\Exception $e) {
        $this->error("Error testing ChatController: " . $e->getMessage());
        $this->error($e->getTraceAsString());
        return 1;
    }
});
