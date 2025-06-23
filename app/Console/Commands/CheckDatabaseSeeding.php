<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDatabaseSeeding extends Command
{
    protected $signature = 'app:check-database-seeding';
    protected $description = 'Check if all tables have been seeded properly';

    public function handle()
    {
        $this->info('======== DATABASE SEEDING CHECK ========');
        
        $tables = [
            'users',
            'workers',
            'supply_centers',
            'suppliers',
            'wholesalers',
            'raw_coffees',
            'coffee_products',
            'inventory',
            'inventory_updates',
            'orders',
            'order_trackings',
            'workforce_assignments',
            'messages',
            'reports',
            'vendor_applications',
            'analytics_data'
        ];
        
        $this->info(str_repeat('=', 40));
        $this->info('TABLE NAME' . str_repeat(' ', 20) . 'RECORD COUNT');
        $this->info(str_repeat('-', 40));
        
        $success = true;
        
        foreach ($tables as $table) {
            $count = DB::table($table)->count();
            $formattedTableName = str_pad($table, 30, ' ');
            $this->info("{$formattedTableName} {$count}");
            
            if ($table === 'orders' && $count < 10) {
                $this->error("  ⚠️ Expected at least 10 orders!");
                $success = false;
            }
            
            if ($table === 'order_trackings' && $count < 1) {
                $this->error("  ⚠️ Expected order tracking records!");
                $success = false;
            }
            
            if ($table === 'inventory' && $count < 1) {
                $this->error("  ⚠️ Expected inventory records!");
                $success = false;
            }
        }
        
        $this->info(str_repeat('=', 40));
        
        // Check order_trackings in more detail
        $this->info("\nOrder Tracking Status:");
        $trackings = DB::table('order_trackings')->select('status', DB::raw('count(*) as count'))->groupBy('status')->get();
        foreach ($trackings as $tracking) {
            $this->info("- {$tracking->status}: {$tracking->count} records");
        }
        
        // Check order types
        $this->info("\nOrder Types:");
        $supplierOrders = DB::table('orders')->whereNotNull('supplier_id')->count();
        $wholesalerOrders = DB::table('orders')->whereNotNull('wholesaler_id')->count();
        $this->info("- Supplier orders: {$supplierOrders}");
        $this->info("- Wholesaler orders: {$wholesalerOrders}");
        
        if ($success) {
            $this->info("\n✅ Database seeding verification complete. All expected data is present.");
        } else {
            $this->error("\n❌ Database seeding verification failed. Some data is missing.");
        }
    }
}
