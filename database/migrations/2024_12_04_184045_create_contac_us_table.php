<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContacUsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contac_us', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last');
            $table->string('email');
            $table->integer('topic');
            $table->string('booking')->nullable();
            $table->string('link')->nullable();
            $table->longText('message');
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
        Schema::dropIfExists('contac_us');
    }
}
