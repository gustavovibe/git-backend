<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTravelersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travelers', function (Blueprint $table) {
            $table->id('traveler_id');
            $table->string('title');
            $table->string('gender');
            $table->string('name');
            $table->string('last');
            $table->date('birth');
            $table->Integer('passport');
            $table->string('place');
            $table->date('issue');
            $table->date('expire');
            $table->string('mail');
            $table->string('phone');
            $table->string('address');
            $table->string('country');
            $table->boolean('lead');
            $table->unsignedBigInteger('order_id');
            $table->timestamps();
            $table->foreign('order_id')->references('booking_id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('travelers');
    }
}
