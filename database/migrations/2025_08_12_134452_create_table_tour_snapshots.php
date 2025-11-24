<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tour_snapshots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tour_id')->index();
            $table->string('tour_name')->nullable()->index();
            // placeholders for generated columns (add with raw SQL below)
            $table->decimal('price_total', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->decimal('flight_price', 12, 2)->nullable();
            $table->integer('start_city')->nullable();
            $table->integer('end_city')->nullable();
            $table->string('start_city_name')->nullable();
            $table->string('end_city_name')->nullable();
            $table->string('countries_list')->nullable()->index();
            $table->json('payload');
            $table->timestamps();
            $table->timestamp('snapshot_at')->nullable()->index();
            $table->integer('type')->nullable();
        });

        // If you prefer to compute generated columns from JSON directly:
        DB::statement("
            ALTER TABLE tour_snapshots
            MODIFY COLUMN price_total DECIMAL(10,2) 
                GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(payload, '$.price_total')) + 0) STORED,
            MODIFY COLUMN total_price DECIMAL(10,2) 
                GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(payload, '$.totalPrice')) + 0) STORED,
            MODIFY COLUMN flight_price DECIMAL(12,2)
                GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(payload, '$.flight.price')) + 0) STORED;
        ");

        // Index generated columns for fast queries:
        DB::statement("CREATE INDEX idx_total_price ON tour_snapshots (total_price)");
        DB::statement("CREATE INDEX idx_flight_price ON tour_snapshots (flight_price)");
    }

     /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tour_snapshots');
    }
};