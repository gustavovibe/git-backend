<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnUserIdFromTravelers extends Migration
{
    public function up()
    {
        Schema::table('travelers', function (Blueprint $table) {
            $table->Integer('user_id')->after('country');
        });
    }

    public function down()
    {
        Schema::table('travelers', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
}
