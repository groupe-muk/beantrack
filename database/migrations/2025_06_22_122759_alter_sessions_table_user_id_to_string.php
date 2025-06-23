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
        Schema::table('sessions', function (Blueprint $table) {
            // Since `user_id` does not exist in your `sessions` table according to SHOW CREATE TABLE,
            // we will simply add it with the desired type. No drop operations are needed for it.
            $table->string('user_id', 6)->nullable()->after('id'); // Add the column as varchar(6)

            // Add an index to the new 'user_id' column for performance.
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations (for `php artisan migrate:rollback`).
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Drop the index added in the `up` method.
            $table->dropIndex(['user_id']);

            // Drop the 'user_id' column.
            $table->dropColumn('user_id');
        });
    }
};