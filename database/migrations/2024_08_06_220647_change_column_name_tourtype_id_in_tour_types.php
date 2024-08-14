<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class changeColumnNameTourtypeIdInTourTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tour_types', function (Blueprint $table) {
            $table->renameColumn('tourtype_id', 'tour_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tour_types', function (Blueprint $table) {
            $table->renameColumn('tour_type_id', 'tourtype_id');
        });
    }
}
