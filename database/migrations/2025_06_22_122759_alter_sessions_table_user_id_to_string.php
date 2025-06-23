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
        Schema::table('sessions', function (Blueprint $table) {
            // Step 1: Drop the existing index on 'user_id' first.
            // This prevents the "Duplicate key name" error.
            try {
                $table->dropIndex(['user_id']); // This targets the index named 'sessions_user_id_index'
            } catch (\Illuminate\Database\QueryException $e) {
                // If the index doesn't exist (e.g., if the sessions table was created without it for some reason),
                // catch the error and continue.
            }

            // Step 2: Attempt to drop the foreign key constraint.
            // This is still important if it existed, as you cannot change a column type if it has an active foreign key.
            try {
                $table->dropForeign('sessions_user_id_foreign');
            } catch (\Illuminate\Database\QueryException $e) {
                // If the foreign key doesn't exist or has a different name (common with MyISAM), catch the error.
            }

            // Step 3: Change the column type from unsignedBigInteger (default) to varchar(6).
            // This is the core purpose of this migration.
            $table->string('user_id', 6)->nullable()->change();

            // Step 4: Re-add an index on the 'user_id' column for performance after the type change.
            // A new index with the default name 'sessions_user_id_index' will be created.
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations (for `php artisan migrate:rollback`).
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Step 1: Drop the string index that was created in the `up` method.
            $table->dropIndex(['user_id']);

            // Step 2: Change the column type back to unsignedBigInteger.
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Step 3: Re-add the index for the integer column.
            $table->index('user_id');

            // IMPORTANT: Only re-add the foreign key here if your `users.id` column
            // would also be reverted to an integer type.
            // Example if needed:
            // try {
            //     $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // } catch (\Illuminate\Database\QueryException $e) { /* ... */ }
        });
    }
};