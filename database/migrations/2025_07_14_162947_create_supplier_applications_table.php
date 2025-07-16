<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplier_applications', function (Blueprint $table) {
            $table->string('id', 7)->primary();
            
            // Applicant information (no user account required yet)
            $table->string('applicant_name');
            $table->string('business_name');
            $table->string('phone_number');
            $table->string('email');
            
            // File paths for uploaded documents (trading license + bank statement)
            $table->string('trading_license_path')->nullable();
            $table->string('bank_statement_path')->nullable();
            
            // Application status and workflow
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected'])->default('pending');
            $table->date('visit_scheduled')->nullable();
            
            // Validation server response data
            $table->json('financial_data')->nullable(); // Bank statement validation results
            $table->json('references')->nullable();     // Reference data from validation
            $table->json('license_data')->nullable();   // Trading license validation results
            $table->text('validation_message')->nullable(); // Detailed validation response
            $table->timestamp('validated_at')->nullable();  // When validation was completed
            
            // User account creation tracking
            $table->string('created_user_id', 6)->nullable(); // Link to user account after approval
            $table->string('status_token', 32)->nullable();   // Token for checking application status
            
            $table->timestamps();
            
            // Foreign key to user account (only set after approval)
            $table->foreign('created_user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['email', 'status_token']); // For status checking
        });
        
        // Trigger to auto-generate supplier application IDs (SA00001, SA00002, etc.)
        DB::unprepared("CREATE TRIGGER before_supplierapplications_insert BEFORE INSERT ON supplier_applications FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 3) AS UNSIGNED) INTO last_id FROM supplier_applications ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('SA', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    
    public function down(): void
    {
        Schema::dropIfExists('supplier_applications');
        DB::unprepared('DROP TRIGGER IF EXISTS before_supplierapplications_insert');
    }
};
