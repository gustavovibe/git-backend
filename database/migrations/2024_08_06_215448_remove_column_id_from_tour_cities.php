<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnIdFromTourCities extends Migration
{
    public function up()
    {
        Schema::table('tour_cities', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }

    public function down()
    {
        Schema::table('tour_cities', function (Blueprint $table) {
            $table->id();
        });
    }
}
