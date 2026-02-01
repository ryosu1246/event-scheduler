<?php

use App\Models\Group;
use App\Models\User;

it('グループを作成すると作成者がadminとして登録される', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('groups.store'), [
        'name' => 'テストグループ',
        'description' => 'テスト説明',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('groups', ['name' => 'テストグループ']);

    $group = Group::where('name', 'テストグループ')->first();
    $this->assertDatabaseHas('group_user_roles', [
        'group_id' => $group->id,
        'user_id' => $user->id,
        'role' => 'admin',
    ]);
});

it('管理者以外はグループを更新できない', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::factory()->create();

    $group->allUsers()->attach($admin->id, ['role' => 'admin']);
    $group->allUsers()->attach($member->id, ['role' => 'member']);

    $response = $this->actingAs($member)->putJson(route('groups.update', $group->id), [
        'name' => '変更後の名前',
    ]);

    $response->assertStatus(403);
});
