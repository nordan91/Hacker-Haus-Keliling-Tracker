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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained()->onDelete('set null');
            $table->string('invoice_number', 30)->unique();                      // e.g. HT-20261108-0001
            $table->enum('payment_method', ['cash', 'qris']);
            $table->enum('payment_status', ['paid', 'pending', 'validated', 'rejected'])->default('paid');
            $table->unsignedInteger('total_amount');                              // in Rupiah
            $table->decimal('latitude', 10, 7)->nullable();                      // GPS at sale time
            $table->decimal('longitude', 10, 7)->nullable();                     // GPS at sale time
            $table->string('location_name')->nullable();                         // denormalized label
            $table->timestamp('transacted_at');                                  // exact sale timestamp
            $table->timestamps();

            $table->index('payment_method');
            $table->index('payment_status');
            $table->index('transacted_at');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

