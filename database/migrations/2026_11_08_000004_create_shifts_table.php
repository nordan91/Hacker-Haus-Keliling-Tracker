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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');   // driver
            $table->date('shift_date');                                          // working date
            $table->enum('status', ['open', 'closed'])->default('open');         // shift lifecycle
            $table->text('notes')->nullable();                                   // optional remarks
            $table->timestamp('started_at')->nullable();                         // actual start time
            $table->timestamp('ended_at')->nullable();                           // actual end time
            $table->timestamps();

            // One open shift per driver per day
            $table->unique(['user_id', 'shift_date']);
            $table->index('status');
            $table->index('shift_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};

