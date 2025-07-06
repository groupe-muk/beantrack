<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Report;
use App\Services\ReportEmailService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessScheduledReports implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ReportEmailService $reportEmailService): void
    {
        Log::info('Processing scheduled reports job started');

        // Get all active scheduled reports
        $scheduledReports = Report::where('status', 'active')
            ->where('type', '!=', 'adhoc')
            ->whereNotNull('frequency')
            ->get();

        Log::info('Found scheduled reports', ['count' => $scheduledReports->count()]);

        foreach ($scheduledReports as $report) {
            try {
                if ($this->shouldGenerateReport($report)) {
                    Log::info('Generating scheduled report', [
                        'report_id' => $report->id,
                        'report_name' => $report->name,
                        'frequency' => $report->frequency
                    ]);

                    // Update report status
                    $report->update([
                        'last_sent' => now(),
                        'status' => 'delivered'
                    ]);

                    // Send email
                    $emailSent = $reportEmailService->sendScheduledReport($report);
                    
                    if ($emailSent) {
                        Log::info('Scheduled report processed successfully', [
                            'report_id' => $report->id
                        ]);
                    } else {
                        Log::warning('Failed to send scheduled report email', [
                            'report_id' => $report->id
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error processing scheduled report', [
                    'report_id' => $report->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        Log::info('Processing scheduled reports job completed');
    }

    /**
     * Determine if a report should be generated based on its frequency
     */
    private function shouldGenerateReport(Report $report): bool
    {
        $now = Carbon::now();
        $lastSent = $report->last_sent ? Carbon::parse($report->last_sent) : null;

        switch ($report->frequency) {
            case 'daily':
                return !$lastSent || $lastSent->diffInDays($now) >= 1;
            
            case 'weekly':
                return !$lastSent || $lastSent->diffInWeeks($now) >= 1;
            
            case 'monthly':
                return !$lastSent || $lastSent->diffInMonths($now) >= 1;
            
            case 'quarterly':
                return !$lastSent || $lastSent->diffInMonths($now) >= 3;
            
            case 'yearly':
                return !$lastSent || $lastSent->diffInYears($now) >= 1;
            
            default:
                return false;
        }
    }
}
