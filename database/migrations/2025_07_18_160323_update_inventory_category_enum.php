<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the ENUM to include 'specialty' category
        DB::statement("ALTER TABLE inventory MODIFY COLUMN category ENUM('premium', 'standard', 'specialty') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM values
        DB::statement("ALTER TABLE inventory MODIFY COLUMN category ENUM('premium', 'standard') NOT NULL");
    }
};
