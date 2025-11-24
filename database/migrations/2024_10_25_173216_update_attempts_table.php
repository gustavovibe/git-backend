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
            $table->json('duffel_res')->nullable(); // JSON column for Duffel responses, nullable
            $table->json('tourradar_res')->nullable(); // JSON column for TourRadar responses, nullable
            $table->string('order_id')->nullable(); // Short text field, nullable
            $table->string('payment_id')->nullable(); // Short text field, nullable
            $table->timestamp('expiration')->nullable(); // Nullable timestamp
            $table->string('checkout_session')->nullable(); // Short text field, nullable
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
            $table->string('order_id');
            $table->string('payment_id');
            $table->timestamp('expiration');
            $table->string('checkout_session')->nullable(); // Short text field, nullable
        });
    }
}

