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
        Schema::table('wishlists', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('traveler_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
            $table->dropForeign(['traveler_id']);
            $table->dropColumn('traveler_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            $table->unsignedBigInteger('traveler_id')->after('user_id')->nullable();
            $table->foreign('traveler_id')->references('traveler_id')->on('travelers')->onDelete('cascade');
            $table->index('traveler_id');
            $table->dropColumn('user_id');  
        });
    }
};
