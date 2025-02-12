<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('crew_seating', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aircraft_type_id')->constrained()->cascadeOnDelete();
            $table->string('position')->default('cabin_crew'); // deck_crew or cabin_crew
            $table->string('location');
            $table->integer('max_number');
            $table->decimal('arm', 8, 2);
            $table->decimal('index_per_kg', 8, 6);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('crew_seating');
    }
};
