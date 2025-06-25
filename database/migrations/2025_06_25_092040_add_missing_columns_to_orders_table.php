<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add missing columns that are expected by the model and views
            $table->date('order_date')->nullable()->after('status');
            $table->decimal('total_amount', 10, 2)->nullable()->after('total_price');
            $table->text('notes')->nullable()->after('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_date', 'total_amount', 'notes']);
        });
    }
};
