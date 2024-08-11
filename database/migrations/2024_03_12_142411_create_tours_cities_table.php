<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateToursCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tour_cities', function (Blueprint $table) {
            $table->id();
            $table->string('city_name');
            $table->unsignedBigInteger('tour_id')->index();
            $table->unsignedBigInteger('t_city_id')->index();
            $table->timestamps();
            // $table->foreign('tour_id')
            //     ->references('tour_id')
            //     ->on('tours');
            // $table->foreign('t_city_id')
            //     ->references('city_id')
            //     ->on('cities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tour_cities');
    }
}
