<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flight_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seat_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_blocked')->default(false);
            $table->string('blocked_reason')->nullable();
            $table->timestamps();

            $table->unique(['flight_id', 'seat_id']);
        });

        // Remove is_blocked from seats table as it's now flight-specific
        Schema::table('seats', function (Blueprint $table) {
            $table->dropColumn('is_blocked');
        });
    }

    public function down(): void
    {
        Schema::table('seats', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('is_exit');
        });

        Schema::dropIfExists('flight_seats');
    }
}; 