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
        // Check if updated_at column exists first to avoid errors
        if (!Schema::hasColumn('messages', 'updated_at')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
            
            // Update all existing records to have the same updated_at as created_at
            DB::statement('UPDATE messages SET updated_at = created_at');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't want to drop updated_at in down migration
        // as it's part of Laravel's standard timestamp columns
    }
};
