<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserProfile>
 */
class UserProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
          return [
            'user_id' => User::factory(),
            'display_name' => $this->faker->name(),
            'profile_image' => $this->faker->imageUrl(400, 400, 'people'),
            'cover_image' => $this->faker->imageUrl(1200, 400, 'nature'),
            'bio' => $this->faker->paragraph(),
            'genre' => $this->faker->randomElement(['Pop', 'Rock', 'Jazz', 'Hip-hop', 'Classical']),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'country' => $this->faker->country(),
            'instagram' => 'https://instagram.com/' . $this->faker->userName(),
            'facebook' => 'https://facebook.com/' . $this->faker->userName(),
            'youtube' => 'https://youtube.com/' . $this->faker->userName(),
            'website' => $this->faker->url(),
            'is_verified' => $this->faker->boolean(30), // 30% chance verified
        ];
    }
}
