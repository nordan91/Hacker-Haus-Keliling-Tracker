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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // e.g. "Jasmine Tea", "Thai Tea"
            $table->string('category')->nullable();          // e.g. "Tea", "Chocolate"
            $table->unsignedInteger('price');                 // in Rupiah (no decimals), e.g. 8000
            $table->string('image_url')->nullable();         // product photo path/URL
            $table->boolean('is_active')->default(true);     // soft toggle for retirement
            $table->timestamps();

            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

