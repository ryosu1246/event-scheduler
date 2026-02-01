<?php

use App\Models\Event;
use App\Models\EventSchedule;
use App\Models\Group;
use App\Models\User;

it('ログインユーザーが出欠回答を送信できる', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->allUsers()->attach($user->id, ['role' => 'member']);

    $event = Event::factory()->create(['group_id' => $group->id, 'created_by' => $user->id]);
    $schedule = EventSchedule::factory()->create(['event_id' => $event->id]);

    $response = $this->actingAs($user)->postJson(route('events.responses.store', $event->id), [
        'responses' => [
            $schedule->id => 'yes',
        ],
    ]);

    $response->assertOk()->assertJson(['success' => true]);
    $this->assertDatabaseHas('event_responses', [
        'event_schedule_id' => $schedule->id,
        'user_id' => $user->id,
        'response' => 'yes',
    ]);
});

it('回答者一覧を取得できる', function () {
    $user = User::factory()->create();
    $schedule = EventSchedule::factory()->create();

    \App\Models\EventResponse::factory()->create([
        'event_schedule_id' => $schedule->id,
        'user_id' => $user->id,
        'response' => 'yes',
    ]);

    $response = $this->actingAs($user)->getJson(route('schedules.respondents', $schedule->id));

    $response->assertOk();
    $response->assertJsonFragment(['user_id' => $user->id, 'response' => 'yes']);
});
