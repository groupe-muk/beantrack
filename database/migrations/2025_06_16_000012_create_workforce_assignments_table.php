<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workforce_assignments', function (Blueprint $table) {
            $table->string('id', 7)->primary();
            $table->string('worker_id', 6);
            $table->string('supply_center_id', 7);
            $table->string('role', 255);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            $table->foreign('supply_center_id')->references('id')->on('supply_centers')->onDelete('cascade');
        });
        DB::unprepared("CREATE TRIGGER before_workforceassignments_insert BEFORE INSERT ON workforce_assignments FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 3) AS UNSIGNED) INTO last_id FROM workforce_assignments ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('WA', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('workforce_assignments');
        DB::unprepared('DROP TRIGGER IF EXISTS before_workforceassignments_insert');
    }
};
