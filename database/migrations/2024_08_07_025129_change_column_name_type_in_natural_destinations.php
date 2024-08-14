<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnNameTypeInNaturalDestinations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('natural_destinations', function (Blueprint $table) {
            $table->renameColumn('type', 'destination_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('natural_destinations', function (Blueprint $table) {
            $table->renameColumn('destination_type', 'type');
        });
    }
}
