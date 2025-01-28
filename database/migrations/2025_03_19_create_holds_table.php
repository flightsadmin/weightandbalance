<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aircraft_type_id')->constrained()->cascadeOnDelete();
            $table->string('name');        // e.g., 'Aft Hold'
            $table->string('code', 2);     // e.g., 'AH'
            $table->integer('position')->nullable();
            $table->integer('max_weight')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hold_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hold_id')->constrained()->cascadeOnDelete();
            $table->string('code')->nullable();        // e.g., '31L'
            $table->integer('row')->nullable();        // e.g., 3
            $table->string('side', 1)->nullable(); // L, R, or null for center
            $table->integer('max_weight')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holds');
    }
};