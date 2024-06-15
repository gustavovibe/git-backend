<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeletedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    Schema::create('orders', function (Blueprint $table) {
        $table->string('booking_id', 255)->primary(); // Assuming booking_id is the primary key
        $table->date('departure');
        $table->date('start');
        $table->date('arrival');
        $table->date('end');
        $table->tinyInteger('duration');
        $table->tinyInteger('tour_length');
        $table->string('tour_name', 255);
        $table->integer('tour_id');
        $table->integer('style');
        $table->integer('operator');
        $table->integer('start_city');
        $table->integer('end_city');
        $table->string('booking_status', 255);
        $table->string('duffel_status', 255);
        $table->string('tourradar_id', 255);
        $table->string('tourradar_status', 255);
        $table->string('tourradar_reason', 255);
        $table->text('tourradar_text');
        $table->string('duffel_id', 255);
        $table->string('source', 255);
        $table->string('device', 255);
        $table->integer('affiliate');
        $table->string('origin', 255);
        $table->tinyInteger('f_destination'); // (hours)
        $table->tinyInteger('f_return'); // (hours)
        $table->tinyInteger('f_duration'); // (hours)
        $table->tinyInteger('destination_stops');
        $table->tinyInteger('return_stops');
        $table->tinyInteger('total_stops');
        $table->string('destination_carrier', 255);
        $table->string('return_carrier', 255);
        $table->tinyInteger('checked_bags');
        $table->tinyInteger('travelers_number');
        $table->string('reference', 255);
        $table->string('method', 255);
        $table->string('currency', 255);
        $table->string('invoice', 255);
        $table->decimal('paid', 10, 2);
        $table->decimal('fees', 10, 2);
        $table->decimal('markup', 10, 2);
        $table->decimal('refunded', 10, 2);
        $table->decimal('p_flight', 10, 2);
        $table->decimal('p_tour', 10, 2);
        $table->decimal('discounted', 10, 2);
        $table->string('promo', 255);
        $table->decimal('profit', 10, 2);
        $table->decimal('ratio', 10, 2);
        $table->string('user_id', 255);
        $table->timestamps(); // created_at and updated_at
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
