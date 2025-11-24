<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->string('booking_id')->nullable(); // Adding booking_id as a nullable string 
            $table->json('tour'); // JSON field for $RequestTour
            $table->json('flight'); // JSON field for $RequestFlight
            $table->text('new_url'); // String field for $newUrl
            $table->text('url'); // String field for $url
            $table->string('status')->default('pending'); // Adding status
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attempts');
    }
}
