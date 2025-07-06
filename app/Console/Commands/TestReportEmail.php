<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Report;
use App\Models\User;
use App\Services\ReportEmailService;

class TestReportEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:test-email {report_id} {--type=scheduled : Type of email to test (scheduled or adhoc)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test report email functionality';

    /**
     * Execute the console command.
     */
    public function handle(ReportEmailService $reportEmailService)
    {
        $reportId = $this->argument('report_id');
        $type = $this->option('type');
        
        $this->info("Testing {$type} report email for report ID: {$reportId}");
        
        $report = Report::find($reportId);
        
        if (!$report) {
            $this->error("Report with ID {$reportId} not found.");
            return 1;
        }
        
        $this->info("Found report: {$report->name}");
        $this->info("Report type: {$report->type}");
        $this->info("Report format: {$report->format}");
        
        try {
            if ($type === 'adhoc') {
                $result = $reportEmailService->sendAdHocReport($report);
            } else {
                $result = $reportEmailService->sendScheduledReport($report);
            }
            
            if ($result) {
                $this->info("✅ Email sent successfully!");
            } else {
                $this->error("❌ Failed to send email.");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error sending email: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
