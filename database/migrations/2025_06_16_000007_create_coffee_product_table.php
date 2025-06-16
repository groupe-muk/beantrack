<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coffee_product', function (Blueprint $table) {
            $table->string('id', 6)->primary();
            $table->string('raw_coffee_id', 7);
            $table->string('category', 50);
            $table->string('name', 100);
            $table->string('product_form', 50);
            $table->string('roast_level', 20)->nullable();
            $table->date('production_date')->nullable();
            $table->timestamps();
            $table->foreign('raw_coffee_id')->references('id')->on('raw_coffee')->onDelete('cascade');
        });
        DB::unprepared("CREATE TRIGGER before_coffeeproduct_insert BEFORE INSERT ON coffee_product FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 2) AS UNSIGNED) INTO last_id FROM coffee_product ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('P', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('coffee_product');
        DB::unprepared('DROP TRIGGER IF EXISTS before_coffeeproduct_insert');
    }
};
