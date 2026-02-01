<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventScheduleFactory extends Factory
{
    protected $model = EventSchedule::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'date' => fake()->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d'),
        ];
    }
}
