<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\DemandHistory;

class UpdateDailyDemand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * --from=YYYY-MM-DD  (optional) start date (inclusive)
     * --to=YYYY-MM-DD    (optional) end date   (inclusive)
     *
     * Examples:
     *  php artisan demand:update-daily            # runs for yesterday only
     *  php artisan demand:update-daily --from=2025-06-01 --to=2025-06-30
     */
    protected $signature = 'demand:update-daily {--from=} {--to=}';

    /**
     * The console command description.
     */
    protected $description = 'Aggregate confirmed wholesaler orders into DemandHistory rows (quantity stored in kilos ⇒ converted to tonnes).';

    public function handle(): int
    {
        $from = $this->option('from');
        $to   = $this->option('to');

        $startDate = $from ? Carbon::parse($from) : Carbon::yesterday();
        $endDate   = $to   ? Carbon::parse($to)   : $startDate;

        if ($startDate->gt($endDate)) {
            $this->error('--from date cannot be after --to date.');
            return self::FAILURE;
        }

        $dates = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dates[] = $date->toDateString();
        }

        DB::beginTransaction();
        try {
            foreach ($dates as $date) {
                $this->info("Processing {$date} …");

                $aggregates = Order::query()
                    ->whereDate('order_date', $date)
                    ->where('status', 'confirmed')
                    ->whereNotNull('wholesaler_id')
                    ->whereNull('supplier_id')
                    ->selectRaw('coffee_product_id, SUM(quantity) AS total_kilos')
                    ->groupBy('coffee_product_id')
                    ->get();

                foreach ($aggregates as $row) {
                    // quantity stored in kilos → convert to tonnes
                    $tonnes = ((float) $row->total_kilos) / 1000.0;

                    DemandHistory::updateOrCreate(
                        [
                            'coffee_product_id' => $row->coffee_product_id,
                            'demand_date'       => $date,
                        ],
                        [
                            'demand_qty_tonnes' => $tonnes,
                            'source'            => 'orders', // optional field for traceability
                        ]
                    );
                }
            }
            DB::commit();
            $this->info('Demand history successfully updated.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed updating daily demand', ['error' => $e->getMessage()]);
            $this->error('An error occurred: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
