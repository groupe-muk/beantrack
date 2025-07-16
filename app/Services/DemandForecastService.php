<?php

namespace App\Services;

use App\Models\CoffeeProduct;
use App\Models\DemandForecast;
use App\Models\DemandHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Exception;

class DemandForecastService
{
    protected string $url;
    protected int $timeout;

    public function __construct()
    {
        $this->url = config('services.demand_prediction.url');
        $this->timeout = (int) config('services.demand_prediction.timeout', 10);
    }

    /**
     * Generate forecast for a product and persist results.
     *
     * @param  CoffeeProduct  $product
     * @param  int  $horizon
     * @return Collection  rows of [date, predicted_demand_tonnes]
     */
    public function generateAndStoreForecast(CoffeeProduct $product, int $horizon = 7): Collection
    {
        [$startDate, $startDemand] = $this->determineStartingPoint($product);

        // Build payload expected by ML micro-service
        $payload = [
            'start_date'  => $startDate->toDateString(),
            'start_price' => (float) $startDemand,
        ];

        Log::info('Calling demand prediction service', [
            'url' => $this->url,
            'payload' => $payload,
            'product_id' => $product->id,
        ]);

        try {
            $response = Http::timeout($this->timeout)->post($this->url, $payload);

            if ($response->failed()) {
                throw new Exception('ML service error: ' . $response->status() . ' – ' . $response->body());
            }

            $data = $response->json('forecast');
            if (!is_array($data)) {
                throw new Exception('Unexpected ML response structure: ' . $response->body());
            }

            return $this->storeForecast($product, $data, $horizon);
        } catch (Exception $e) {
            Log::error('Demand prediction request failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Store forecast rows into DB and return collection.
     */
    protected function storeForecast(CoffeeProduct $product, array $rows, int $horizon): Collection
    {
        $collection = collect($rows)->map(function ($row) use ($product, $horizon) {
            return DemandForecast::updateOrCreate(
                [
                    'coffee_product_id' => $product->id,
                    'predicted_date'    => Carbon::parse($row['date'])->toDateString(),
                ],
                [
                    'predicted_demand_tonnes' => $row['predicted_price'],
                    'horizon'                 => $horizon,
                    'generated_at'            => now(),
                ]
            );
        });

        return $collection;
    }

    /**
     * Determine starting date & demand for forecast: use last market demand if available, otherwise today + fallback demand.
     */
    protected function determineStartingPoint(CoffeeProduct $product): array
    {
        /** @var DemandHistory|null $latest */
        $latest = DemandHistory::where('coffee_product_id', $product->id)
            ->orderBy('demand_date', 'desc')
            ->first();

        if ($latest) {
            return [Carbon::parse($latest->demand_date), (float) $latest->demand_qty_tonnes];
        }

        // Fallback: today & a default demand of 0 tonnes
        return [now(), 0.00];
    }

    /**
     * Fetch the most recently generated forecast for a product.
     */
    public function getLatestForecast(CoffeeProduct $product, int $horizon = 7): Collection
    {
        return DemandForecast::where('coffee_product_id', $product->id)
            ->where('horizon', $horizon)
            ->whereDate('generated_at', now()->toDateString())
            ->orderBy('predicted_date')
            ->get(['predicted_date', 'predicted_demand_tonnes']);
    }
} 