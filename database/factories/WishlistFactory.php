<?php

namespace Database\Factories;

use App\Models\Wishlist;
use App\Models\Traveler;
use Illuminate\Database\Eloquent\Factories\Factory;

class WishlistFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Wishlist::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'traveler_id' => Traveler::factory(),
            'wish_id' => $this->faker->numberBetween(1, 10),
            'tour_id' => $this->faker->numberBetween(1, 100),
            'notes' => $this->faker->realText(200)
        ];
    }
}
