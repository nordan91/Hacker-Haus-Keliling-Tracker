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
        Schema::create('daily_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('start_qty');                // how many loaded onto the cart
            $table->unsignedInteger('sold_qty')->default(0);     // cached: sum of transaction_details.qty
            $table->unsignedInteger('remaining_qty')->default(0); // cached: start_qty - sold_qty
            $table->timestamps();

            // One inventory entry per product per shift
            $table->unique(['shift_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_inventories');
    }
};

