<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->string('id', 6)->primary();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['inventory', 'order_summary', 'performance', 'adhoc']);
            $table->string('recipient_id', 6);
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'once']);
            $table->enum('format', ['pdf', 'excel', 'csv', 'dashboard'])->default('pdf');
            $table->text('recipients')->nullable();
            $table->time('schedule_time')->nullable();
            $table->string('schedule_day')->nullable();
            $table->enum('status', ['active', 'paused', 'failed', 'processing', 'completed'])->default('active');
            $table->json('content');
            $table->timestamp('last_sent')->nullable();
            $table->timestamps();
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
        });
        DB::unprepared("CREATE TRIGGER before_reports_insert BEFORE INSERT ON reports FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 2) AS UNSIGNED) INTO last_id FROM reports ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('R', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('reports');
        DB::unprepared('DROP TRIGGER IF EXISTS before_reports_insert');
    }
};
