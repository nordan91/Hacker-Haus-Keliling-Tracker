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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                      // e.g. "SMAN 71", "Pasar Minggu"
            $table->string('address')->nullable();                       // full address text
            $table->decimal('latitude', 10, 7);                          // GPS latitude
            $table->decimal('longitude', 10, 7);                         // GPS longitude
            $table->unsignedInteger('radius_meters')->default(100);      // geofence matching radius
            $table->timestamps();

            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};

