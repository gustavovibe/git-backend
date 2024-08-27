<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDestinationsTable extends Migration
{
    public function up()
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->text('overview');
            $table->text('quick_facts');
            $table->text('things_to_do');
            $table->text('travel_tips');
            $table->text('best_time_to_visit');
            $table->text('slug');
            $table->text('excerpt');
            $table->text('meta_description');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('destinations');
    }
}
