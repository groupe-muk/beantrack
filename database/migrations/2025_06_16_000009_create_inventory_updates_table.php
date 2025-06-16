<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_updates', function (Blueprint $table) {
            $table->string('id', 7)->primary();
            $table->string('inventory_id', 6);
            $table->decimal('quantity_change', 10, 2);
            $table->string('reason', 255);
            $table->string('updated_by', 6);
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('inventory_id')->references('id')->on('inventory')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
        DB::unprepared("CREATE TRIGGER before_inventoryupdates_insert BEFORE INSERT ON inventory_updates FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 3) AS UNSIGNED) INTO last_id FROM inventory_updates ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('IU', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('inventory_updates');
        DB::unprepared('DROP TRIGGER IF EXISTS before_inventoryupdates_insert');
    }
};
