<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('attempts', function (Blueprint $table) {
            $table->json('duffel_res')->nullable();
            $table->json('tourradar_res')->nullable();
            $table->text('offer_id'); 
            $table->text('payment_id'); 
            $table->text('expiration'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->json('duffel_res');
            $table->json('tourradar_res');
        });
    }
}

