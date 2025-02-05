<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('crew_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aircraft_type_id')->constrained()->cascadeOnDelete();
            $table->integer('crew_count');
            $table->json('distribution'); // Stores the distribution data for each location
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('crew_distributions');
    }
};
