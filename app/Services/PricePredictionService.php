<?php

namespace App\Services;

use App\Models\CoffeeProduct;
use App\Models\PriceForecast;
use App\Models\PriceHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Exception;

class PricePredictionService
{
    protected string $url;
    protected int $timeout;

    public function __construct()
    {
        $this->url = config('services.price_prediction.url');
        $this->timeout = (int) config('services.price_prediction.timeout', 10);
    }

    /**
     * Generate forecast for a product and persist results.
     *
     * @param  CoffeeProduct  $product
     * @param  int  $horizon
     * @return Collection  rows of [date, predicted_price]
     */
    public function generateAndStoreForecast(CoffeeProduct $product, int $horizon = 7): Collection
    {
        [$startDate, $startPrice] = $this->determineStartingPoint($product);

        // Build payload expected by ML micro-service
        $payload = [
            'start_date'  => $startDate->toDateString(),
            'start_price' => (float) $startPrice,
        ];

        Log::info('Calling price prediction service', [
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
            Log::error('Price prediction request failed', [
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
            return PriceForecast::updateOrCreate(
                [
                    'coffee_product_id' => $product->id,
                    'predicted_date'    => Carbon::parse($row['date'])->toDateString(),
                ],
                [
                    'predicted_price' => $row['predicted_price'],
                    'horizon'         => $horizon,
                    'generated_at'    => now(),
                ]
            );
        });

        return $collection;
    }

    /**
     * Determine starting date & price for forecast: use last market price if available, otherwise today + fallback price.
     */
    protected function determineStartingPoint(CoffeeProduct $product): array
    {
        /** @var PriceHistory|null $latest */
        $latest = PriceHistory::where('coffee_product_id', $product->id)
            ->orderBy('market_date', 'desc')
            ->first();

        if ($latest) {
            return [Carbon::parse($latest->market_date), (float) $latest->price_per_lb];
        }

        // Fallback: today & a default price
        return [now(), 1.00];
    }

    /**
     * Fetch the most recently generated forecast for a product.
     */
    public function getLatestForecast(CoffeeProduct $product, int $horizon = 7): Collection
    {
        return PriceForecast::where('coffee_product_id', $product->id)
            ->where('horizon', $horizon)
            ->whereDate('generated_at', now()->toDateString())
            ->orderBy('predicted_date')
            ->get(['predicted_date', 'predicted_price']);
    }
} 