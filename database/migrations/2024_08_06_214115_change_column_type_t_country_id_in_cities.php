<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnTypeTCountryIdInCities extends Migration
{
    public function up()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->string('t_country_id')->change();
        });
    }

    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->integer('t_country_id')->change();
        });
    }
}
