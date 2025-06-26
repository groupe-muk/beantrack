<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SupplyCenter;
use App\Models\Report;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        // Check if we have users available
        if ($users->count() === 0) {
            Log::warning('Cannot create reports: No users available');
            return;
        }

        $reportTemplates = [
            [
                'name' => 'Monthly Supplier Demand Forecast',
                'description' => 'Comprehensive analysis of supplier demand patterns',
                'type' => 'inventory',
                'frequency' => 'monthly',
                'format' => 'pdf',
                'recipients' => 'Finance Dept, Logistics Team',
                'schedule_time' => '09:00:00',
                'schedule_day' => 'monday',
                'status' => 'active',
                'content' => json_encode(['template' => 'supplier_demand', 'parameters' => ['period' => 'monthly']])
            ],
            [
                'name' => 'Weekly Production Efficiency',
                'description' => 'Production metrics and efficiency analysis',
                'type' => 'performance',
                'frequency' => 'weekly',
                'format' => 'excel',
                'recipients' => 'Production Team',
                'schedule_time' => '08:00:00',
                'schedule_day' => 'monday',
                'status' => 'active',
                'content' => json_encode(['template' => 'production_efficiency', 'parameters' => ['metrics' => 'all']])
            ],
            [
                'name' => 'Daily Retail Sales Summary',
                'description' => 'Daily sales performance across all outlets',
                'type' => 'order_summary',
                'frequency' => 'daily',
                'format' => 'pdf',
                'recipients' => 'Sales Team, Management',
                'schedule_time' => '18:00:00',
                'schedule_day' => null,
                'status' => 'active',
                'content' => json_encode(['template' => 'sales_summary', 'parameters' => ['outlets' => 'all']])
            ],
            [
                'name' => 'Quarterly Quality Control Report',
                'description' => 'Quality metrics and compliance tracking',
                'type' => 'performance',
                'frequency' => 'quarterly',
                'format' => 'pdf',
                'recipients' => 'Quality Team, Compliance',
                'schedule_time' => '10:00:00',
                'schedule_day' => 'monday',
                'status' => 'active',
                'content' => json_encode(['template' => 'quality_control', 'parameters' => ['compliance_level' => 'full']])
            ],
            [
                'name' => 'Inventory Movement Analysis',
                'description' => 'Detailed inventory tracking and movement patterns',
                'type' => 'inventory',
                'frequency' => 'weekly',
                'format' => 'excel',
                'recipients' => 'Warehouse Team',
                'schedule_time' => '07:00:00',
                'schedule_day' => 'monday',
                'status' => 'paused',
                'content' => json_encode(['template' => 'inventory_movement', 'parameters' => ['movement_types' => 'all']])
            ]
        ];

        foreach ($reportTemplates as $template) {
            try {
                $randomUser = $users->random();
                
                Report::create([
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'type' => $template['type'],
                    'recipient_id' => $randomUser->id,
                    'frequency' => $template['frequency'],
                    'format' => $template['format'],
                    'recipients' => $template['recipients'],
                    'schedule_time' => $template['schedule_time'],
                    'schedule_day' => $template['schedule_day'],
                    'status' => $template['status'],
                    'content' => $template['content'],
                    'last_sent' => $template['status'] === 'active' ? now()->subDays(rand(1, 30)) : null
                ]);
                
                Log::info('Created report: ' . $template['name']);
            } catch (\Exception $e) {
                Log::error('Failed to create report: ' . $template['name'] . ' - ' . $e->getMessage());
            }
        }
    }
}
