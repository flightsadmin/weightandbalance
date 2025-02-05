<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aircraft_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cabin_zone_id')->constrained()->cascadeOnDelete();
            $table->integer('row');
            $table->string('column', 1);
            $table->string('designation', 4);
            $table->string('type')->default('economy');
            $table->boolean('is_exit')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['aircraft_type_id', 'designation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};