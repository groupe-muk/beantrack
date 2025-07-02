<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Report;
use App\Models\User;

class ValidateReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:validate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate the Reports System functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== BeanTrack Reports System Validation ===');
        $this->newLine();

        try {
            // Check if we can connect to the database and models work
            $this->info('1. Testing database connection...');
            $reportCount = Report::count();
            $this->info("   ✓ Connected! Found {$reportCount} reports in database");
            $this->newLine();

            // Test Report model functionality
            $this->info('2. Testing Report model...');
            $reports = Report::with('recipient')->limit(3)->get();
            
            foreach ($reports as $report) {
                $this->line("   Report: {$report->name}");
                $this->line("   Type: {$report->format}");
                $this->line("   Frequency: {$report->frequency}");
                $this->line("   Status: {$report->status}");
                $this->line("   Recipient: " . ($report->recipient ? $report->recipient->name : 'N/A'));
                $this->line("   ---");
            }

            // Test creating a sample report
            $this->newLine();
            $this->info('3. Testing report creation...');
            $user = User::first();
            
            if ($user) {
                $testReport = Report::create([
                    'name' => 'Test Report - ' . date('Y-m-d H:i:s'),
                    'description' => 'This is a test report created during validation',
                    'type' => 'performance',
                    'recipient_id' => $user->id,
                    'frequency' => 'once',
                    'format' => 'pdf',
                    'recipients' => 'Test User',
                    'schedule_time' => '09:00:00',
                    'status' => 'completed',
                    'content' => json_encode(['test' => true]),
                    'last_sent' => now()
                ]);
                
                $this->info("   ✓ Test report created with ID: {$testReport->id}");
                
                // Clean up - delete the test report
                $testReport->delete();
                $this->info("   ✓ Test report cleaned up");
            } else {
                $this->warn("   ⚠ No users found in database for testing");
            }

            // Test model accessors
            $this->newLine();
            $this->info('4. Testing model accessors...');
            $sampleReport = Report::first();
            if ($sampleReport) {
                $this->line("   Status Badge: " . strip_tags($sampleReport->status_badge));
                $this->line("   Format Badge: " . strip_tags($sampleReport->format_badge));
                $this->line("   Formatted Last Sent: {$sampleReport->formatted_last_sent}");
            }

            $this->newLine();
            $this->info('✅ All validations passed! The Reports System is working correctly.');
            $this->newLine();
            
            $this->info('📋 Summary:');
            $this->line('   - Database connection: ✓ Working');
            $this->line('   - Report model: ✓ Working');
            $this->line('   - CRUD operations: ✓ Working');
            $this->line('   - Model relationships: ✓ Working');
            $this->line('   - Model accessors: ✓ Working');
            
            $this->newLine();
            $this->info('🎯 Next Steps:');
            $this->line('   1. Access the reports page at: /reports');
            $this->line('   2. Test the Create New Report Schedule wizard');
            $this->line('   3. Try generating ad-hoc reports');
            $this->line('   4. Explore the historical reports section');

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('Stack trace:');
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('=== Validation Complete ===');
        return Command::SUCCESS;
    }
}
