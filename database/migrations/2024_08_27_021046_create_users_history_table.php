<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('users_history', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('action');
            $table->string('item');
            $table->datetime('action_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users_history');
    }
}
