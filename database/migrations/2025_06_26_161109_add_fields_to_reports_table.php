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
        Schema::table('reports', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->text('description')->nullable()->after('name');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'once'])->default('monthly')->change();
            $table->enum('format', ['pdf', 'excel', 'csv', 'dashboard'])->default('pdf')->after('frequency');
            $table->text('recipients')->nullable()->after('format');
            $table->time('schedule_time')->nullable()->after('recipients');
            $table->string('schedule_day')->nullable()->after('schedule_time');
            $table->enum('status', ['active', 'paused', 'failed', 'processing', 'completed'])->default('active')->after('schedule_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn([
                'name', 
                'description', 
                'format', 
                'recipients', 
                'schedule_time', 
                'schedule_day', 
                'status'
            ]);
        });
    }
};
