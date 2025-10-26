<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Venue;
use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Database\Factories\UserProfileFactory;
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

      User::factory(10)->create()->each(function ($user) {
            Venue::factory(3)->create(['user_id' => $user->id])->each(function ($venue) use ($user) {
                Event::factory(2)->create([
                    'user_id' => $user->id,
                    'venue_id' => $venue->id,
                ]);
            });
        });
    }
}
