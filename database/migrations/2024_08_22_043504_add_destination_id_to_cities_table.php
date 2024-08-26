<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDestinationIdToCitiesTable extends Migration
{

    public function up()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->unsignedBigInteger('destination_id')->nullable()->after('t_country_id');

            $table->foreign('destination_id')
                ->references('id')->on('destinations')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropForeign(['destination_id']);

            $table->dropColumn('destination_id');
        });
    }
}
