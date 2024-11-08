<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TravelGuideGallery extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('travel_guide_gallery', function (Blueprint $table) {
          $table->id();
          $table->unsignedBigInteger('t_id');
          $table->string('name');
          $table->string('title');
          $table->string('unsplash_id');
          $table->string('url');
          $table->string('author');
          $table->string('author_url');
          $table->timestamps(); // Crea las columnas 'created_at' y 'updated_at'
      });
      
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('travel_guide_gallery');
    }
}
