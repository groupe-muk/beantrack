<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ListTables extends Command
{
    protected $signature = 'app:list-tables';
    protected $description = 'List all tables in the database';

    public function handle()
    {
        $tables = DB::select('SHOW TABLES');
        $this->info('======== DATABASE TABLES ========');
        foreach ($tables as $table) {
            $tableName = reset($table);
            $this->info($tableName);
        }
        
        // Check a few specific tables
        $this->info("\n======== DATA COUNTS ========");
        
        $tableChecks = [
            'users',
            'coffee_product',
            'raw_coffee',
            'order_trackings',
            'orders',
            'inventory',
            'supplier',
            'wholesaler',
            'supply_centers',
        ];
        
        foreach ($tableChecks as $tableName) {
            try {
                $count = DB::table($tableName)->count();
                $this->info("{$tableName}: {$count} records");
            } catch (\Exception $e) {
                $this->error("{$tableName}: Error - {$e->getMessage()}");
            }
        }
    }
}
