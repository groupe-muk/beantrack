<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vendor_applications', function (Blueprint $table) {
            $table->string('id', 7)->primary();
            $table->string('applicant_id', 6);
            $table->json('financial_data')->nullable();
            $table->json('references')->nullable();
            $table->json('license_data')->nullable();
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected']);
            $table->date('visit_scheduled')->nullable();
            $table->timestamps();
            $table->foreign('applicant_id')->references('id')->on('users')->onDelete('cascade');
        });
        DB::unprepared("CREATE TRIGGER before_vendorapplications_insert BEFORE INSERT ON vendor_applications FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 3) AS UNSIGNED) INTO last_id FROM vendor_applications ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('VA', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('vendor_applications');
        DB::unprepared('DROP TRIGGER IF EXISTS before_vendorapplications_insert');
    }
};
