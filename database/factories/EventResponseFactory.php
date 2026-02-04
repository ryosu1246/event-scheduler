<?php

namespace Database\Factories;

use App\Models\EventResponse;
use App\Models\EventSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventResponseFactory extends Factory
{
    protected $model = EventResponse::class;

    public function definition(): array
    {
        return [
            'event_schedule_id' => EventSchedule::factory(),
            'user_id' => User::factory(),
            'response' => fake()->randomElement(['yes', 'maybe', 'no']),
        ];
    }
}
