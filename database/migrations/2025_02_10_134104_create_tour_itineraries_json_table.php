<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('tour_itineraries_json', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('tour_id');
      $table->text('json_data');
      $table->timestamp('dt_added')->useCurrent();
      $table->timestamp('dt_updated')->useCurrent()->useCurrentOnUpdate();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('tour_itineraries_json');
  }
};
