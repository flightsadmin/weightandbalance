<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained()->cascadeOnDelete();
            $table->string('container_number')->unique();
            $table->enum('type', ['baggage', 'cargo']);
            $table->foreignId('position_id')->nullable()->constrained('hold_positions')->nullOnDelete();
            $table->enum('status', ['empty', 'loading', 'loaded', 'unloaded'])->default('unloaded')->nullable();
            $table->integer('tare_weight')->default(60);
            $table->integer('weight')->default(0);
            $table->integer('max_weight')->default(2000);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('containers');
    }
};
