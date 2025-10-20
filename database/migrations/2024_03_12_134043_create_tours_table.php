<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateToursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tour_id')->index();
            $table->string('tour_name');
            $table->string('locale');
            $table->string('language');
            $table->integer('is_active');
            $table->integer('tour_length_days');
            $table->unsignedBigInteger('start_city');
            $table->unsignedBigInteger('end_city');
            $table->integer('is_instant_confirmable');
            $table->decimal('price_total', 8, 2);
            $table->string('price_currency');
            $table->decimal('price_promotion', 8, 2)->nullable();
            $table->integer('reviews_count')->nullable();
            $table->decimal('ratings_overall', 8, 2)->nullable();
            $table->decimal('ratings_operator', 8, 2)->nullable();
            $table->longText('description');
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->integer('max_group_size');
            $table->string('main_image')->nullable();
            $table->string('main_thumbnail')->nullable();
            $table->string('map_image')->nullable();
            $table->string('map_thumbnail')->nullable();
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
        Schema::dropIfExists('tours');
    }
}
