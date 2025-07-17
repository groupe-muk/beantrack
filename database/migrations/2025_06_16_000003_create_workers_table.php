<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->string('id', 6)->primary();
            $table->string('supplycenter_id', 7)->nullable();
            $table->string('warehouse_id', 7)->nullable();
            $table->foreign('supplycenter_id')->references('id')->on('supply_centers')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->string('name', 255);
            $table->string('role', 100)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->string('email', 191)->unique()->nullable();
            $table->string('shift');
            $table->timestamps();
        });
        
        // Add the check constraint using raw SQL after table creation
        DB::unprepared("ALTER TABLE workers ADD CONSTRAINT chk_worker_location CHECK ((supplycenter_id IS NOT NULL AND warehouse_id IS NULL) OR (supplycenter_id IS NULL AND warehouse_id IS NOT NULL))");
        
        DB::unprepared("CREATE TRIGGER before_workers_insert BEFORE INSERT ON workers FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 2) AS UNSIGNED) INTO last_id FROM workers ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('W', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('workers');
        DB::unprepared('DROP TRIGGER IF EXISTS before_workers_insert');
        // The constraint will be dropped automatically when the table is dropped
    }
};
