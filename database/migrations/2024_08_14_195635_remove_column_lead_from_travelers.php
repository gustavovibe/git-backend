<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnLeadFromTravelers extends Migration
{
    public function up()
    {
        Schema::table('travelers', function (Blueprint $table) {
            $table->dropColumn('lead');
        });
    }

    public function down()
    {
        Schema::table('travelers', function (Blueprint $table) {
            $table->boolean('lead');
        });
    }
}
