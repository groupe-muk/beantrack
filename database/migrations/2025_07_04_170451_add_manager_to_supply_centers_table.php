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
        Schema::table('supply_centers', function (Blueprint $table) {
           $table->string('manager', 255)->nullable()->after('location'); //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supply_centers', function (Blueprint $table) {
            $table->dropColumn('manager');//
        });
    }
};
