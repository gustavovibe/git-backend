<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWholeTripToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->float('whole_trip')->nullable()->after('duration');
            $table->string('channel')->nullable()->after('whole_trip');
            $table->string('payment_method')->nullable()->after('channel');
            $table->string('medium')->nullable()->after('payment_method');
            $table->string('gender')->nullable()->after('medium');
            $table->string('age_group')->nullable()->after('gender');
            $table->string('group_size')->nullable()->after('age_group');
            $table->string('country')->nullable()->after('group_size');
            $table->string('carrier')->nullable()->after('country');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('whole_trip');
            $table->dropColumn('channel');
            $table->dropColumn('payment_method');
            $table->dropColumn('medium');
            $table->dropColumn('gender');
            $table->dropColumn('age_group');
            $table->dropColumn('group_size');
            $table->dropColumn('country');
            $table->dropColumn('carrier');
        });
    }
}
