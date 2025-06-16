<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('analytics_data', function (Blueprint $table) {
            $table->string('id', 6)->primary();
            $table->enum('type', ['demand', 'customer_segmentation']);
            $table->json('data');
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();
        });
        DB::unprepared("CREATE TRIGGER before_analyticsdata_insert BEFORE INSERT ON analytics_data FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 2) AS UNSIGNED) INTO last_id FROM analytics_data ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('A', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('analytics_data');
        DB::unprepared('DROP TRIGGER IF EXISTS before_analyticsdata_insert');
    }
};
