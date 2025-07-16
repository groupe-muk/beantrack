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
        Schema::create('customer_segments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('segment_type', ['rfm', 'order_size'])->default('rfm');
            $table->boolean('is_dynamic')->default(true);
            $table->timestamps();
            
            $table->unique(['name', 'segment_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_segments');
    }
};
