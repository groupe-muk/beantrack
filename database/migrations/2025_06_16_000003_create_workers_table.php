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
            $table->string('supplycenter_id', 7);
            $table->foreign('supplycenter_id')->references('id')->on('supply_centers')->onDelete('cascade');
            $table->string('name', 255);
            $table->string('role', 100)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->string('email', 191)->unique()->nullable();
            $table->string('shift');
            $table->timestamps();
        });
        DB::unprepared("CREATE TRIGGER before_workers_insert BEFORE INSERT ON workers FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 2) AS UNSIGNED) INTO last_id FROM workers ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('W', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('workers');
        DB::unprepared('DROP TRIGGER IF EXISTS before_workers_insert');
    }
};
