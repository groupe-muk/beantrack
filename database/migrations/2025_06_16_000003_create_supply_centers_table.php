<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_centers', function (Blueprint $table) {
            $table->string('id', 7)->primary();
            $table->string('name', 255);
            $table->string('location', 255);
            $table->decimal('capacity', 10, 2);
            $table->timestamps();
        });
        DB::unprepared("CREATE TRIGGER before_supplycenters_insert BEFORE INSERT ON supply_centers FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 3) AS UNSIGNED) INTO last_id FROM supply_centers ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('SC', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('supply_centers');
        DB::unprepared('DROP TRIGGER IF EXISTS before_supplycenters_insert');
    }
};
