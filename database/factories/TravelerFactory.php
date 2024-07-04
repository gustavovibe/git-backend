<?php

namespace Database\Factories;

use App\Models\Traveler; // Import the Traveler model
use Illuminate\Database\Eloquent\Factories\Factory;

class TravelerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Traveler::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $gender = $this->faker->randomElement(['F', 'M']);
        return [
            'title' => $this->faker->title($this->faker->randomElement(['male', 'female'])),
            'gender' => $gender,
            'name' => $this->faker->firstName($this->faker->randomElement(['male', 'female'])),
            'last' => $this->faker->lastName(),
            'birth' => $this->faker->date($format = 'Y-m-d', $max = 'now'),
            'passport' => $this->faker->numberBetween(0, 100),
            'place' => $this->faker->country(),
            'issue' => $this->faker->date($format = 'Y-m-d', $max = 'now'),
            'expire' => $this->faker->date($format = 'Y-m-d', $max = 'now'),
            'mail' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'country' => $this->faker->numberBetween(0, 100),
            'lead' => $this->faker->numberBetween(0, 100)
        ];
    }
}
