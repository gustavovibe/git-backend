<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlightsToursTable extends Migration
{
    public function up()
    {
        Schema::create('flights_tours', function (Blueprint $table) {
            $table->id();
            $table->json('flight');
            $table->json('tour');
            $table->unsignedBigInteger('id_order');
            $table->timestamps();
            $table->foreign('id_order')->references('booking_id')->on('orders')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('flights_tours');
    }
}
