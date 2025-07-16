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
        Schema::create('customer_segment_wholesaler', function (Blueprint $table) {
            $table->id();
            $table->string('wholesaler_id', 7);
            $table->foreignId('segment_id');
            $table->json('scores')->nullable(); // Store R, F, M scores and other metrics
            $table->timestamps();
            
            $table->foreign('wholesaler_id')->references('id')->on('wholesaler')->onDelete('cascade');
            $table->foreign('segment_id')->references('id')->on('customer_segments')->onDelete('cascade');
            
            $table->unique(['wholesaler_id', 'segment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_segment_wholesaler');
    }
};
