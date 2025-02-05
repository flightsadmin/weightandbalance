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
        Schema::create('passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['male', 'female', 'child', 'infant'])->nullable();
            $table->string('seat_number');
            $table->string('ticket_number')->nullable();
            $table->string('acceptance_status')->default('booked');
            $table->string('boarding_status')->default('boarding');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['flight_id', 'seat_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passengers');
    }
};
