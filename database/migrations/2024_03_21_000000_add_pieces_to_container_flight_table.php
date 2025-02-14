<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('container_flight', function (Blueprint $table) {
            $table->integer('pieces')->nullable()->after('type');
        });
    }

    public function down()
    {
        Schema::table('container_flight', function (Blueprint $table) {
            $table->dropColumn('pieces');
        });
    }
};