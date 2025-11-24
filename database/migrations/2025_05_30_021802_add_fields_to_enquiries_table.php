<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->string('topic')->nullable();
            $table->string('booking_id')->nullable();
            $table->string('adventure_link')->nullable();
            $table->json('tour_details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropColumn('topic');
            $table->dropColumn('booking_id');
            $table->dropColumn('adventure_link');
            $table->dropColumn('tour_details');
        });
    }
};
