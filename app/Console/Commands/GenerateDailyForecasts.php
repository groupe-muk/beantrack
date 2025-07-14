<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PricePredictionService;
use App\Models\CoffeeProduct;

class GenerateDailyForecasts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:daily-forecasts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily price forecasts for all coffee products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = app(PricePredictionService::class);

        CoffeeProduct::all()->each(function ($product) use ($service) {
            if ($service->getLatestForecast($product)->isEmpty()) {
                $service->generateAndStoreForecast($product);
                $this->info("Forecast generated for {$product->id}");
            } else {
                $this->line("Forecast already exists for {$product->id}");
            }
        });

        $this->info('Daily forecasts generation completed.');
    }
}
