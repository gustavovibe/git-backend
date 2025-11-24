<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('value');
            $table->text('description')->nullable();
            $table->string('code');
            $table->string('payment_type');
            $table->string('pax_restriction')->nullable();
            $table->integer('beds_number');
            $table->boolean('is_shared');
            $table->json('price_tiers')->nullable();
            // Foreign key referencing departures.id
            $table->unsignedBigInteger('departure');
            $table->foreign('departure')->references('id')->on('departures')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('accommodations');
    }
};