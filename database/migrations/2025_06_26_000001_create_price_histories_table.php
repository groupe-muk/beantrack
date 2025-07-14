<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_histories', function (Blueprint $table) {
            $table->string('id', 7)->primary();
            $table->string('coffee_product_id', 6);
            $table->date('market_date');
            $table->decimal('price_per_lb', 8, 4);
            $table->timestamps();

            $table->foreign('coffee_product_id')->references('id')->on('coffee_product')->onDelete('cascade');
            $table->unique(['coffee_product_id', 'market_date'], 'uniq_price_histories_product_date');
        });

        // Trigger to auto-generate IDs like PH00001
        DB::unprepared("CREATE TRIGGER before_pricehistories_insert BEFORE INSERT ON price_histories FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 3) AS UNSIGNED) INTO last_id FROM price_histories ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('PH', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }

    public function down(): void
    {
        Schema::dropIfExists('price_histories');
        DB::unprepared('DROP TRIGGER IF EXISTS before_pricehistories_insert');
    }
}; 