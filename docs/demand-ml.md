# Machine-Learning Demand Forecasting – How It Works

> "Think of this feature as asking a crystal ball: **what will the coffee demand look like next week?**  
> We look at yesterday's demand (historical demand), send it to a data scientist (the ML service), get the forecast back, jot it in our diary (the database), and finally draw a neat chart on the dashboard."

---

## 1. Why do we bother?
Coffee demand fluctuates daily based on market conditions, seasonality, and consumer behavior. Buyers and suppliers need foresight to plan inventory and production capacity. Our ML forecasting answers the question *"What will the demand be over the next 7 days?"* and visualises the answer side-by-side with the last two weeks of real data.

## 2. The moving parts

| Layer | File(s) / Table(s) | Analogy | Key responsibility |
|-------|-------------------|---------|--------------------|
| Historical demand | `demand_histories` table (`DemandHistory` model) | **Diary entries** of what really happened | Stores the demand quantity for each day and product |
| Forecast storage | `demand_forecasts` table (`DemandForecast` model + migration) | **Sticky notes** we add to the diary for future days | Persists every predicted date ↔ demand pair |
| Prediction engine | `DemandForecastService.php` | **Courier** that talks to the fortune teller | Gathers the latest real demand, calls the ML micro-service, and writes results back |
| External ML service | URL in `config/services.php` → `DEMAND_PREDICTION_URL` | **Fortune teller** | Receives `start_date` + `start_demand` and returns a JSON list of futures |
| Controller glue | `dashboardController::getDemandForecastChartData()` | **Chef** assembling ingredients | Merges 14 real days + 7 predicted days for the chart |
| UI component | `resources/views/components/ml-prediction-graph-card.blade.php` | **Painter** | Renders the dual-line "Actual vs Predicted" chart with ApexCharts |

---

## 3. End-to-end flow (step-by-step)

1. **User opens the Admin dashboard** → `dashboardController@index` runs.
2. The controller needs chart data, so it calls `getDemandForecastChartData()`.
3. Inside that method we:
   a. **Fetch the last 14 diary entries** from `demand_histories` for the first coffee product.  
   b. **Ask** `DemandForecastService` for a 7-day forecast:
      - If a forecast for *today* already exists in `demand_forecasts`, we reuse it (`getLatestForecast`).
      - Otherwise we **generate** one (`generateAndStoreForecast`).
4. `DemandForecastService` determines the *starting point* – the most recent diary entry, or today + 0 tonnes fallback.

```php
protected function determineStartingPoint(CoffeeProduct $product): array
{
    $latest = DemandHistory::where('coffee_product_id', $product->id)
        ->orderBy('demand_date', 'desc')
        ->first();
    if ($latest) {
        return [Carbon::parse($latest->demand_date), (float) $latest->demand_qty_tonnes];
    }
    return [now(), 0.00];
}
```

5. **Payload is built** (`start_date` & `start_demand`) and sent via HTTP POST to the ML service:

```php
$payload = [
    'start_date'   => $startDate->toDateString(),
    'start_demand' => (float) $startDemand,
];
$response = Http::timeout($this->timeout)->post($this->url, $payload);
```

6. The ML service replies with JSON:
```json
{
  "forecast": [
    { "date": "2025-07-01", "predicted_demand": 23.5 },
    ... six more days ...
  ]
}
```
7. Each row is **upserted** into `demand_forecasts` so repeated requests are idempotent (no duplicates).

```php
DemandForecast::updateOrCreate([
    'coffee_product_id' => $product->id,
    'predicted_date'    => Carbon::parse($row['date'])->toDateString(),
], [
    'predicted_demand_tonnes' => $row['predicted_demand'],
    'horizon'                 => $horizon,
    'generated_at'            => now(),
]);
```

8. Back in the controller we combine arrays so that the chart's X-axis is continuous; past days have *Actual* values, future days have *Predicted* values (others are `null`).

```php
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
DEMAND_PREDICTION_URL=http://localhost:8080/forecast

# Socket timeout in seconds
DEMAND_PREDICTION_TIMEOUT=10
```

**Tip**: when running locally without the micro-service, the controller will log an error but the rest of the dashboard still works.

---

## 5. Error handling & resilience

* If the HTTP call fails or returns an unexpected shape, we log the issue and propagate an exception; downstream callers can catch it (the dashboard swallows it and just hides the chart).
* Upserting ensures that rerunning the forecast does **not** create duplicates, and analysts can regenerate forecasts any time.
* The database migration includes a trigger to auto-generate IDs like `DF00001`, so you never have to think about keys.

---

## 6. Simple analogy recap
- **DemandHistory table**: like *past order receipts* pinned on a board.
- **DemandForecastService**: the *courier* carrying your latest demand data to a *fortune teller*.
- **ML micro-service**: the *fortune teller* reading patterns and giving future demand predictions.
- **DemandForecast table**: sticky notes you stick next to the receipts for tomorrow and beyond.
- **Dashboard chart**: a *timeline* where solid lines show real data and dashed lines show sticky-note guesses.

With these pieces, any part of the system can ask, *"What might the demand be next Tuesday?"* — and get a consistent, stored answer in milliseconds.

---

## 7. Extending or replacing the model
1. Point `DEMAND_PREDICTION_URL` to a new endpoint.
2. Ensure it still accepts `{start_date, start_demand}` and returns the same JSON shape.
3. If you need longer horizons, tweak `generateAndStoreForecast($product, $horizon)` and the DB `horizon` default.
4. To display more history, adjust `$historyDays` in the controller.

---

## 8. Data Units
The system now displays demand in **tonnes** instead of price in dollars. All database fields store values in tonnes with 4 decimal precision:
- `demand_qty_tonnes` in `demand_histories` table
- `predicted_demand_tonnes` in `demand_forecasts` table
- UI displays formatted as "X.XX tonnes"

Happy forecasting! :chart_with_upwards_trend: 