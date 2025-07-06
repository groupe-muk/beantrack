<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier', function (Blueprint $table) {
            $table->string('id', 6)->primary();
            $table->string('user_id', 6);
            $table->string('supply_center_id', 7);
            $table->string('warehouse_id', 7)->nullable(); // Supplier's own warehouse
            $table->string('name', 255);
            $table->string('contact_person', 100);
            $table->string('email', 191)->unique();
            $table->string('phone', 20);
            $table->string('address', 255);
            $table->string('registration_number', 50)->unique();
            $table->date('approved_date')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('supply_center_id')->references('id')->on('supply_centers')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
        });
        DB::unprepared("CREATE TRIGGER before_supplier_insert BEFORE INSERT ON supplier FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 2) AS UNSIGNED) INTO last_id FROM supplier ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('S', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('supplier');
        DB::unprepared('DROP TRIGGER IF EXISTS before_supplier_insert');
    }
};
