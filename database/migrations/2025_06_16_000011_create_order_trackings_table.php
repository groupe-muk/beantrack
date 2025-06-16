<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_trackings', function (Blueprint $table) {
            $table->string('id', 7)->primary();
            $table->string('order_id', 6);
            $table->enum('status', ['shipped', 'in-transit', 'delivered']);
            $table->string('location', 255)->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
        DB::unprepared("CREATE TRIGGER before_ordertrackings_insert BEFORE INSERT ON order_trackings FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 3) AS UNSIGNED) INTO last_id FROM order_trackings ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('OT', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('order_trackings');
        DB::unprepared('DROP TRIGGER IF EXISTS before_ordertrackings_insert');
    }
};
