<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Ensure this is included

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First check if the column exists before attempting modifications
        if (Schema::hasColumn('sessions', 'user_id')) {
            // Handle indexes that might interfere with the change
            Schema::table('sessions', function (Blueprint $table) {
                try {
                    $table->dropIndex(['user_id']);
                } catch (\Exception $e) {
                    // Index might not exist or have a different name
                }
            });
            
            // Check for foreign keys directly in the database
            $foreignKeyExists = DB::select("
                SELECT * FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                AND TABLE_NAME = 'sessions' 
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                AND CONSTRAINT_NAME = 'sessions_user_id_foreign'
            ");
            
            if (!empty($foreignKeyExists)) {
                DB::statement('ALTER TABLE sessions DROP FOREIGN KEY sessions_user_id_foreign');
            }
            
            // Now modify the column type - using DB statement for more direct control
            // This avoids issues with Laravel's schema builder
            DB::statement('ALTER TABLE sessions MODIFY user_id VARCHAR(6) NULL');
            
            // Re-add the index
            Schema::table('sessions', function (Blueprint $table) {
                $table->index('user_id');
            });
        } else {
            // If user_id column doesn't exist, add it
            Schema::table('sessions', function (Blueprint $table) {
                $table->string('user_id', 6)->nullable()->after('id');
                $table->index('user_id');
            });
        }
    }

    /**
     * Reverse the migrations (for `php artisan migrate:rollback`).
     */
    public function down(): void
    {
        if (Schema::hasColumn('sessions', 'user_id')) {
            // Drop any existing index
            try {
                Schema::table('sessions', function (Blueprint $table) {
                    $table->dropIndex(['user_id']);
                });
            } catch (\Exception $e) {
                // Index might not exist, continue
            }

            // Directly change the type with SQL for more control
            DB::statement('ALTER TABLE sessions MODIFY user_id BIGINT UNSIGNED NULL');
            
            // Re-add the index
            Schema::table('sessions', function (Blueprint $table) {
                $table->index('user_id');
            });
            
            // Note: We don't add foreign key constraints because it's not
            // clear what this would reference in the users table.
        }
    }
};