<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->string('id', 6)->primary();
            $table->string('raw_coffee_id', 7)->nullable();
            $table->string('coffee_product_id', 6)->nullable();
            $table->decimal('quantity_in_stock', 10, 2);
            $table->string('supply_center_id', 7);
            $table->timestamp('last_updated')->useCurrent();
            $table->timestamps();
            $table->foreign('raw_coffee_id')->references('id')->on('raw_coffee')->onDelete('cascade');
            $table->foreign('coffee_product_id')->references('id')->on('coffee_product')->onDelete('cascade');
            $table->foreign('supply_center_id')->references('id')->on('supply_centers')->onDelete('cascade');
        });
        DB::statement("ALTER TABLE inventory ADD CONSTRAINT chk_inventory_raw_or_product CHECK ((raw_coffee_id IS NULL AND coffee_product_id IS NOT NULL) OR (raw_coffee_id IS NOT NULL AND coffee_product_id IS NULL))");
        DB::unprepared("CREATE TRIGGER before_inventory_insert BEFORE INSERT ON inventory FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 2) AS UNSIGNED) INTO last_id FROM inventory ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('I', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('inventory');
        DB::unprepared('DROP TRIGGER IF EXISTS before_inventory_insert');
    }
};
