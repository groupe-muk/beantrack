<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DemandForecastService;
use App\Models\CoffeeProduct;

class GenerateDailyDemandForecasts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:daily-demand-forecasts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily demand forecasts for all coffee products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = app(DemandForecastService::class);

        CoffeeProduct::all()->each(function ($product) use ($service) {
            if ($service->getLatestForecast($product)->isEmpty()) {
                $service->generateAndStoreForecast($product);
                $this->info("Demand forecast generated for {$product->id}");
            } else {
                $this->line("Demand forecast already exists for {$product->id}");
            }
        });

        $this->info('Daily demand forecasts generation completed.');
    }
} 