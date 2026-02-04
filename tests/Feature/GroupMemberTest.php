<?php

use App\Models\Group;
use App\Models\User;

it('メンバーがグループを退会できる', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::factory()->create();

    $group->allUsers()->attach($admin->id, ['role' => 'admin']);
    $group->allUsers()->attach($member->id, ['role' => 'member']);

    $response = $this->actingAs($member)->postJson(route('groups.leave', $group->id));

    $response->assertOk();
    $this->assertDatabaseMissing('group_user_roles', [
        'group_id' => $group->id,
        'user_id' => $member->id,
    ]);
});

it('最後の1人が退会するとグループが削除される', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();

    $group->allUsers()->attach($user->id, ['role' => 'admin']);

    $response = $this->actingAs($user)->postJson(route('groups.leave', $group->id));

    $response->assertOk()->assertJson(['group_deleted' => true]);
    $this->assertDatabaseMissing('groups', ['id' => $group->id]);
});
