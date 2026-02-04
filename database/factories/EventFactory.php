<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'group_id' => Group::factory(),
            'created_by' => User::factory(),
        ];
    }
}
