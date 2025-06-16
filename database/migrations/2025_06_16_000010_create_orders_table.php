<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->string('id', 6)->primary();
            $table->string('supplier_id', 6)->nullable();
            $table->string('wholesaler_id', 7)->nullable();
            $table->string('raw_coffee_id', 7)->nullable();
            $table->string('coffee_product_id', 6)->nullable();
            $table->enum('status', ['pending', 'confirmed', 'shipped', 'delivered']);
            $table->decimal('quantity', 10, 2);
            $table->decimal('total_price', 10, 2)->nullable();
            $table->timestamps();
            $table->foreign('supplier_id')->references('id')->on('supplier')->onDelete('cascade');
            $table->foreign('wholesaler_id')->references('id')->on('wholesaler')->onDelete('cascade');
            $table->foreign('raw_coffee_id')->references('id')->on('raw_coffee')->onDelete('cascade');
            $table->foreign('coffee_product_id')->references('id')->on('coffee_product')->onDelete('cascade');
        });
        DB::statement("ALTER TABLE orders ADD CONSTRAINT chk_orders_supplier_or_wholesaler CHECK ((supplier_id IS NOT NULL AND wholesaler_id IS NULL AND raw_coffee_id IS NOT NULL AND coffee_product_id IS NULL) OR (supplier_id IS NULL AND wholesaler_id IS NOT NULL AND raw_coffee_id IS NULL AND coffee_product_id IS NOT NULL))");
        DB::unprepared("CREATE TRIGGER before_orders_insert BEFORE INSERT ON orders FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 2) AS UNSIGNED) INTO last_id FROM orders ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('O', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('orders');
        DB::unprepared('DROP TRIGGER IF EXISTS before_orders_insert');
    }
};
