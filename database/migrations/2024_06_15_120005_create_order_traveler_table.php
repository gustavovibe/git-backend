<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTravelerTable extends Migration
{
    public function up()
    {
        Schema::create('order_traveler', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('traveler_id');
            $table->timestamps();
            $table->foreign('booking_id')->references('booking_id')->on('orders')->onDelete('cascade');
            $table->foreign('traveler_id')->references('traveler_id')->on('travelers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_traveler');
    }
}
