# Machine-Learning Price Forecasting – How It Works

> "Think of this feature as asking a weather app: **what will the coffee price look like next week?**  
> We look at yesterday's weather (historical prices), send it to a meteorologist (the ML service), get the forecast back, jot it in our diary (the database), and finally draw a neat chart on the dashboard."

---

## 1. Why do we bother?
Coffee prices fluctuate daily.  Buyers and suppliers need foresight to plan inventory and budgets.  Our ML forecasting answers the question *"What will the price be over the next 7 days?"* and visualises the answer side-by-side with the last two weeks of real data.

## 2. The moving parts

| Layer | File(s) / Table(s) | Analogy | Key responsibility |
|-------|-------------------|---------|--------------------|
| Historical prices | `price_histories` table (`PriceHistory` model) | **Diary entries** of what really happened | Stores the market price for each day and product |
| Forecast storage | `price_forecasts` table (`PriceForecast` model + migration) | **Sticky notes** we add to the diary for future days | Persists every predicted date ↔ price pair |
| Prediction engine | `PricePredictionService.php` | **Courier** that talks to the fortune teller | Gathers the latest real price, calls the ML micro-service, and writes results back |
| External ML service | URL in `config/services.php` → `PRICE_PREDICTION_URL` | **Fortune teller** | Receives `start_date` + `start_price` and returns a JSON list of futures |
| Controller glue | `dashboardController::getPriceForecastChartData()` | **Chef** assembling ingredients | Merges 14 real days + 7 predicted days for the chart |
| UI component | `resources/views/components/ml-prediction-graph-card.blade.php` | **Painter** | Renders the dual-line "Actual vs Predicted" chart with ApexCharts |

---

## 3. End-to-end flow (step-by-step)

1. **User opens the Admin dashboard** → `dashboardController@index` runs.
2. The controller needs chart data, so it calls `getPriceForecastChartData()`.
3. Inside that method we:
   a. **Fetch the last 14 diary entries** from `price_histories` for the first coffee product.  
   b. **Ask** `PricePredictionService` for a 7-day forecast:
      - If a forecast for *today* already exists in `price_forecasts`, we reuse it (`getLatestForecast`).
      - Otherwise we **generate** one (`generateAndStoreForecast`).
4. `PricePredictionService` determines the *starting point* – the most recent diary entry, or today + £1.00 fallback.  (See code below.)

```15:33:app/Services/PricePredictionService.php
    protected function determineStartingPoint(CoffeeProduct $product): array
    {
        $latest = PriceHistory::where('coffee_product_id', $product->id)
            ->orderBy('market_date', 'desc')
            ->first();
        if ($latest) {
            return [Carbon::parse($latest->market_date), (float) $latest->price_per_lb];
        }
        return [now(), 1.00];
    }
```

5. **Payload is built** (`start_date` & `start_price`) and sent via HTTP POST to the ML service:

```23:36:app/Services/PricePredictionService.php
        $payload = [
            'start_date'  => $startDate->toDateString(),
            'start_price' => (float) $startPrice,
        ];
        $response = Http::timeout($this->timeout)->post($this->url, $payload);
```

6. The ML service replies with JSON:
```json
{
  "forecast": [
    { "date": "2025-07-01", "predicted_price": 2.35 },
    ... six more days ...
  ]
}
```
7. Each row is **upserted** into `price_forecasts` so repeated requests are idempotent (no duplicates).

```43:59:app/Services/PricePredictionService.php
    PriceForecast::updateOrCreate([
        'coffee_product_id' => $product->id,
        'predicted_date'    => Carbon::parse($row['date'])->toDateString(),
    ], [
        'predicted_price' => $row['predicted_price'],
        'horizon'         => $horizon,
        'generated_at'    => now(),
    ]);
```

8. Back in the controller we combine arrays so that the chart's X-axis is continuous; past days have *Actual* values, future days have *Predicted* values (others are `null`).

```695:728:app/Http/Controllers/dashboardController.php
        $series = [
            ['name' => 'Actual',    'data' => $actualData],
            ['name' => 'Predicted', 'data' => $predictedData],
        ];
```

9. Finally the Blade component draws two coloured lines, adding a dashed stroke for the forecast and a vertical "Forecast Start" annotation.

---

## 4. Configuration & environment variables

Set these in your `.env` (or rely on the defaults shown):

```dotenv
# URL where the Python/R/… ML micro-service listens
PRICE_PREDICTION_URL=http://localhost:8080/forecast

# Socket timeout in seconds
PRICE_PREDICTION_TIMEOUT=10
```

**Tip**: when running locally without the micro-service, the controller will log an error but the rest of the dashboard still works.

---

## 5. Error handling & resilience

* If the HTTP call fails or returns an unexpected shape, we log the issue and propagate an exception; downstream callers can catch it (the dashboard swallows it and just hides the chart).
* Upserting ensures that rerunning the forecast does **not** create duplicates, and analysts can regenerate forecasts any time.
* The database migration includes a trigger to auto-generate IDs like `PF00001`, so you never have to think about keys.

---

## 6. Simple analogy recap
- **PriceHistory table**: like *past receipts* pinned on a board.
- **PricePredictionService**: the *courier* carrying your latest receipt to a *fortune teller*.
- **ML micro-service**: the *fortune teller* reading patterns and giving future receipts.
- **PriceForecast table**: sticky notes you stick next to the receipts for tomorrow and beyond.
- **Dashboard chart**: a *timeline* where solid lines show real data and dashed lines show sticky-note guesses.

With these pieces, any part of the system can ask, *"What might the price be next Tuesday?"* — and get a consistent, stored answer in milliseconds.

---

## 7. Extending or replacing the model
1. Point `PRICE_PREDICTION_URL` to a new endpoint.
2. Ensure it still accepts `{start_date, start_price}` and returns the same JSON shape.
3. If you need longer horizons, tweak `generateAndStoreForecast($product, $horizon)` and the DB `horizon` default.
4. To display more history, adjust `$historyDays` in the controller.

Happy forecasting! :crystal_ball: 