<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGravitecSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gravitec_subscribers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reg_id');
            $table->unsignedBigInteger('user_id');
            $table->string('alias');
            $table->tinyInteger('is_subscribed');
            $table->json('sub_data');
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
        Schema::dropIfExists('gravitec_subscribers');
    }
}
