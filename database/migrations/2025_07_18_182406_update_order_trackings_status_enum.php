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
        // Update the ENUM to include all order statuses used in the system
        DB::statement("ALTER TABLE order_trackings MODIFY COLUMN status ENUM('pending', 'confirmed', 'shipped', 'in-transit', 'delivered', 'cancelled', 'rejected') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM values
        DB::statement("ALTER TABLE order_trackings MODIFY COLUMN status ENUM('shipped', 'in-transit', 'delivered') NOT NULL");
    }
};
