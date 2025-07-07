<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->string('id', 7)->primary();
            $table->string('name', 255);
            $table->string('location', 255);
            $table->decimal('capacity', 10, 2);
            $table->string('supplier_id', 6)->nullable();
            $table->string('wholesaler_id', 7)->nullable();
            $table->string('manager_name', 255)->nullable();
            $table->timestamps();
            
            // Foreign key constraints - supplier_id will be added later to avoid circular dependency
            // $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('wholesaler_id')->references('id')->on('wholesalers')->onDelete('cascade');
            
            // Check constraint to ensure either supplier_id or wholesaler_id is set, but not both
            $table->index(['supplier_id', 'wholesaler_id']);
        });
        
        // Add check constraint
        DB::statement("ALTER TABLE warehouses ADD CONSTRAINT chk_warehouse_owner CHECK ((supplier_id IS NULL AND wholesaler_id IS NOT NULL) OR (supplier_id IS NOT NULL AND wholesaler_id IS NULL))");
        
        // Add trigger for auto-incrementing ID
        DB::unprepared("CREATE TRIGGER before_warehouses_insert BEFORE INSERT ON warehouses FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 3) AS UNSIGNED) INTO last_id FROM warehouses ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('WH', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
        DB::unprepared('DROP TRIGGER IF EXISTS before_warehouses_insert');
    }
};
