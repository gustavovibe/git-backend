<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('booking_id');
            $table->date('departure');
            $table->date('start');
            $table->date('arrival');
            $table->date('end');
            $table->integer('duration');
            $table->integer('tour_length');
            $table->string('tour_name');
            $table->unsignedBigInteger('tour_id');
            $table->integer('operator');
            $table->string('start_city');
            $table->string('end_city');
            $table->string('booking_status');
            $table->string('tourradar_id');
            $table->string('tourradar_status');
            $table->string('tourradar_reason');
            $table->text('tourradar_text');
            $table->string('duffel_id');
            $table->string('origin');
            $table->string('f_destination');
            $table->string('f_return');
            $table->integer('f_duration');
            $table->integer('destination_stops');
            $table->integer('return_stops');
            $table->integer('total_stops');
            $table->string('destination_carrier');
            $table->string('return_carrier');
            $table->integer('checked_bags');
            $table->integer('travelers_number');
            $table->string('reference');
            $table->string('currency');
            $table->decimal('paid', 10, 2);
            $table->decimal('p_flight', 10, 2);
            $table->decimal('p_tour', 10, 2);
            $table->decimal('discounted', 10, 2);
            $table->string('promo');
            $table->string('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
