<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReportEmailService;
use App\Models\Report;
use App\Models\User;

class TestEmailService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-email-service {--email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the email service with a sample report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        
        if (!$email) {
            $this->error('Please provide an email address using --email option');
            return 1;
        }

        $this->info("Testing email service with email: {$email}");
        
        // Create a mock user
        $user = User::first();
        if (!$user) {
            $this->error('No users found in database. Please create a user first.');
            return 1;
        }

        // Create a mock report for testing
        $report = new Report([
            'name' => 'Test Report',
            'type' => 'adhoc',
            'content' => json_encode([
                'filters' => ['test' => 'value'],
                'parameters' => []
            ]),
            'delivery_method' => 'email',
            'recipients' => json_encode([$email]),
            'format' => 'pdf',
            'status' => 'pending',
            'created_by' => $user->id
        ]);

        // Set the creator relationship manually for testing
        $report->setRelation('creator', $user);

        try {
            $emailService = new ReportEmailService();
            
            $this->info('Generating and sending test report...');
            
            // Test ad-hoc report generation
            $result = $emailService->sendAdHocReport($report);
            
            if ($result) {
                $this->info('✅ Email sent successfully!');
                $this->info('Check your mail logs or inbox for the email.');
            } else {
                $this->error('❌ Failed to send email.');
            }
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
}
