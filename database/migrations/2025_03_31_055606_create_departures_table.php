<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('departures', function (Blueprint $table) {
            // If you are going to use your own id values, you can use bigInteger without autoIncrement:
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('tour_id')->nullable(); // store which tour this belongs to
            $table->date('date');
            $table->integer('availability');
            $table->string('departure_type');
            $table->boolean('is_instant_confirmable');
            $table->string('currency', 3);
            $table->string('based_on');
            $table->integer('price_base');
            $table->integer('price_addons');
            $table->integer('price_promotion');
            $table->integer('price_total_upfront');
            $table->integer('price_total');
            $table->json('promotion')->nullable();
            // JSON columns for arrays (ensure your database supports JSON type)
            $table->json('mandatory_addons')->nullable();
            $table->json('optional_extras')->nullable();
            $table->timestamps();
            $table->json('accommodations')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('departures');
    }
};