<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->json('passengers')->nullable();

        });
    }    

    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->dropColumn('passengers');
        });
    }
};
