<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cities = [[
            2692, "Kabul", 13079, 2, "kabul_af"
        ],];

        foreach ($cities as $city) {
            DB::table('cities')->insert([
                'city_id' => $city[0],
                't_city_id' => $city[1],
                'city_name' => $city[2],
                'kiwi_id' => $city[3],
                't_country_id' => $city[4],
            ]);
        }
    }
}
