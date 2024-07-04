<?php

namespace Database\Seeders;

use App\Models\Traveler;
use Illuminate\Database\Seeder;

class TravelerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Traveler::factory()
            ->count(1)
            ->hasWishlists(3)
            ->create();
        Traveler::factory()
            ->count(1)
            ->hasWishlists(1)
            ->create();
        Traveler::factory()
            ->count(1)
            ->create();
    }
}
