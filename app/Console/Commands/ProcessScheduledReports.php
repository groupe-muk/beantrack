<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessScheduledReports as ProcessScheduledReportsJob;

class ProcessScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled reports and send emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting scheduled report processing...');
        
        // Dispatch the job
        ProcessScheduledReportsJob::dispatch();
        
        $this->info('Scheduled report processing job dispatched successfully.');
        
        return 0;
    }
}
