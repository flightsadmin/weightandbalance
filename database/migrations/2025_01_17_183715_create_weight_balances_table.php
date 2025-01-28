<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('weight_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained()->onDelete('cascade');
            $table->integer('zero_fuel_weight');
            $table->integer('takeoff_fuel_weight');
            $table->integer('takeoff_weight');
            $table->integer('landing_fuel_weight');
            $table->integer('landing_weight');
            $table->integer('passenger_weight_total');
            $table->integer('baggage_weight_total');
            $table->integer('cargo_weight_total');
            $table->integer('crew_weight_total');
            $table->decimal('center_of_gravity', 10, 2);
            $table->boolean('within_limits')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weight_balances');
    }
};
