<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        \App\Console\Commands\UpdateDailyDemand::class,
        \App\Console\Commands\GenerateDailyDemandForecasts::class,
        \App\Console\Commands\GenerateWholesalerSegments::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 1. Aggregate yesterday's confirmed wholesaler orders into DemandHistory
        $schedule->command('demand:update-daily')
                 ->timezone('Africa/Kampala')
                 ->dailyAt('00:10');

        // 2. Generate demand forecasts for each product after history is updated
        $schedule->command('generate:daily-demand-forecasts')
                 ->timezone('Africa/Kampala')
                 ->dailyAt('00:30');

        // 3. Generate wholesaler segments based on RFM and order size analysis
        $schedule->command('wholesaler:generate-segments')
                 ->timezone('Africa/Kampala')
                 ->dailyAt('01:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
