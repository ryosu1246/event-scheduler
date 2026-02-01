<?php

use App\Models\Event;
use App\Models\Group;
use App\Models\User;

it('グループメンバーがイベントを作成できる', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->allUsers()->attach($user->id, ['role' => 'member']);

    $response = $this->actingAs($user)->post(route('events.store'), [
        'title' => 'テストイベント',
        'description' => 'テスト説明',
        'dates' => json_encode(['2026-03-01', '2026-03-02']),
        'group_id' => $group->id,
    ]);

    $response->assertRedirect(route('groups.events', $group->id));
    $this->assertDatabaseHas('events', [
        'title' => 'テストイベント',
        'group_id' => $group->id,
        'created_by' => $user->id,
    ]);
});

it('非メンバーはイベントを作成できない', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();

    $response = $this->actingAs($user)->post(route('events.store'), [
        'title' => 'テストイベント',
        'description' => 'テスト説明',
        'dates' => json_encode(['2026-03-01']),
        'group_id' => $group->id,
    ]);

    $response->assertStatus(403);
});
