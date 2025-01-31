<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('aircraft_types', function (Blueprint $table) {
            $table->id();
            $table->string('code');  // e.g., 'B738', 'A320'
            $table->string('name');  // e.g., 'Boeing 737-800', 'Airbus A320'
            $table->string('manufacturer');  // e.g., 'Boeing', 'Airbus'

            // Basic specifications
            $table->integer('max_deck_crew')->default(2);
            $table->integer('max_cabin_crew')->default(2);
            $table->integer('max_passengers')->default(0);
            $table->integer('cargo_capacity')->default(0); // in kg
            $table->integer('max_fuel_capacity')->default(0); // in liters
            $table->integer('empty_weight')->default(0); // in kg
            $table->integer('max_zero_fuel_weight')->default(0); // in kg
            $table->integer('max_takeoff_weight')->default(0); // in kg
            $table->integer('max_landing_weight')->default(0); // in kg

            // Basic dimensions
            $table->integer('max_range')->default(0); // in nautical miles
            $table->string('category')->nullable(); // Narrow-body, Wide-body, Regional
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aircraft_types');
    }
};