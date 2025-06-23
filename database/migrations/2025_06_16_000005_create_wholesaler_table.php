<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wholesaler', function (Blueprint $table) {
            $table->string('id', 7)->primary();
            $table->string('user_id', 6);
            $table->string('name', 255);
            $table->string('contact_person', 100);
            $table->string('email', 191)->unique();
            $table->string('phone', 20);
            $table->string('address', 255);
            $table->string('distribution_region', 100);
            $table->string('registration_number', 50)->unique();
            $table->date('approved_date')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        DB::unprepared("CREATE TRIGGER before_wholesaler_insert BEFORE INSERT ON wholesaler FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 4) AS UNSIGNED) INTO last_id FROM wholesaler ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('WHL', LPAD(COALESCE(last_id + 1, 1), 4, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('wholesaler');
        DB::unprepared('DROP TRIGGER IF EXISTS before_wholesaler_insert');
    }
};
