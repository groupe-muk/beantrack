<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_forecasts', function (Blueprint $table) {
            $table->string('id', 7)->primary();
            $table->string('coffee_product_id', 6);
            $table->date('predicted_date');
            $table->decimal('predicted_price', 8, 4);
            $table->unsignedTinyInteger('horizon')->default(7); // days into the future
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();

            $table->foreign('coffee_product_id')->references('id')->on('coffee_product')->onDelete('cascade');
            $table->unique(['coffee_product_id', 'predicted_date'], 'uniq_price_forecasts_product_date');
        });

        // Trigger to auto-generate IDs like PF00001
        DB::unprepared("CREATE TRIGGER before_priceforecasts_insert BEFORE INSERT ON price_forecasts FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 3) AS UNSIGNED) INTO last_id FROM price_forecasts ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('PF', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }

    public function down(): void
    {
        Schema::dropIfExists('price_forecasts');
        DB::unprepared('DROP TRIGGER IF EXISTS before_priceforecasts_insert');
    }
}; 