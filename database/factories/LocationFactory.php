<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Film;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'film_id' => Film::factory(),
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'description' => fake()->text(),
            'upvotes_count' => 0,
        ];
    }
}
