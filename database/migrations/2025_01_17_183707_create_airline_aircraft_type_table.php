<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('airline_aircraft_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')->constrained()->cascadeOnDelete();
            $table->foreignId('aircraft_type_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['airline_id', 'aircraft_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('airline_aircraft_type');
    }
};