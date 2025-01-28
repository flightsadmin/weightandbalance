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
        Schema::create('fuel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained()->onDelete('cascade');
            $table->integer('taxi_fuel');
            $table->integer('trip_fuel');
            $table->integer('contingency_fuel');
            $table->integer('alternate_fuel');
            $table->integer('final_reserve_fuel');
            $table->integer('additional_fuel')->default(0);
            $table->integer('total_fuel');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel');
    }
};
