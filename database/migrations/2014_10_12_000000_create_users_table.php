<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedBigInteger('profile_id');
            $table->string('phone')->nullable();
            $table->unsignedBigInteger('job_id')->nullable();
            $table->string('country')->nullable();
            $table->string('role');
            $table->string('active')->default(1);
            $table->boolean('suscribed')->nullable();
            $table->text('hear')->nullable();
            $table->datetime('last_login')->nullable();
            $table->longtext('internal_notes')->nullable();
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
        Schema::dropIfExists('users');
    }
}
