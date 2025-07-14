<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demand_histories', function (Blueprint $table) {
            $table->string('id', 7)->primary();
            $table->string('coffee_product_id', 6);
            $table->date('demand_date');
            $table->decimal('demand_qty_tonnes', 8, 4);
            $table->timestamps();

            $table->foreign('coffee_product_id')->references('id')->on('coffee_product')->onDelete('cascade');
            $table->unique(['coffee_product_id', 'demand_date'], 'uniq_demand_histories_product_date');
        });

        // Trigger to auto-generate IDs like DH00001
        DB::unprepared("CREATE TRIGGER before_demandhistories_insert BEFORE INSERT ON demand_histories FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 3) AS UNSIGNED) INTO last_id FROM demand_histories ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('DH', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }

    public function down(): void
    {
        Schema::dropIfExists('demand_histories');
        DB::unprepared('DROP TRIGGER IF EXISTS before_demandhistories_insert');
    }
}; 