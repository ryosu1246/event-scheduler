<?php

use App\Models\Group;
use App\Models\User;

it('isAdmin が管理者を正しく判定する', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::factory()->create();

    $group->allUsers()->attach($admin->id, ['role' => 'admin']);
    $group->allUsers()->attach($member->id, ['role' => 'member']);

    expect($group->isAdmin($admin))->toBeTrue();
    expect($group->isAdmin($member))->toBeFalse();
});

it('isMember がメンバーを正しく判定する', function () {
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $group = Group::factory()->create();

    $group->allUsers()->attach($member->id, ['role' => 'member']);

    expect($group->isMember($member))->toBeTrue();
    expect($group->isMember($outsider))->toBeFalse();
});

it('isInvited が招待中ユーザーを正しく判定する', function () {
    $invited = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::factory()->create();

    $group->allUsers()->attach($invited->id, ['role' => 'invited']);
    $group->allUsers()->attach($member->id, ['role' => 'member']);

    expect($group->isInvited($invited))->toBeTrue();
    expect($group->isInvited($member))->toBeFalse();
});
