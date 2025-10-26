<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Venue;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 days', '+1 month');
        $end = (clone $start)->modify('+2 hours');

        return [
            'user_id' => User::factory(),
            'venue_id' => Venue::factory(),
            'title' => $this->faker->catchPhrase(),
            'description' => $this->faker->paragraph(),
            'image' => null,
            'type' => $this->faker->randomElement(['Concert', 'Festival', 'Meetup', 'Workshop']),
            'genre' => $this->faker->randomElement(['Pop', 'Rock', 'Jazz', 'EDM', 'Classical']),
            'start_time' => $start,
            'end_time' => $end,
            'entry_fee' => $this->faker->randomElement([0, 50, 100, 250, 500]),
            'max_participants' => $this->faker->numberBetween(50, 500),
            'is_featured' => $this->faker->boolean(20),
        ];
    }
}
