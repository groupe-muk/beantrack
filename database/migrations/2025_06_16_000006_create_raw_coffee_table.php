<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raw_coffee', function (Blueprint $table) {
            $table->string('id', 7)->primary();
            $table->string('supplier_id', 6);
            $table->string('coffee_type', 50);
            $table->string('grade', 10);
            $table->string('screen_size', 10)->nullable();
            $table->integer('defect_count')->nullable();
            $table->date('harvest_date')->nullable();
            $table->timestamps();
            $table->foreign('supplier_id')->references('id')->on('supplier')->onDelete('cascade');
        });
        DB::unprepared("CREATE TRIGGER before_rawcoffee_insert BEFORE INSERT ON raw_coffee FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 3) AS UNSIGNED) INTO last_id FROM raw_coffee ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('RC', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('raw_coffee');
        DB::unprepared('DROP TRIGGER IF EXISTS before_rawcoffee_insert');
    }
};
