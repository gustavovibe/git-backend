<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGravitecNotificationsPreferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gravitec_notifications_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reg_id');
            $table->unsignedBigInteger('t_id');
            $table->tinyInteger('price_change');
            $table->tinyInteger('dates_change');
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
        Schema::dropIfExists('gravitec_notifications_preferences');
    }
}
