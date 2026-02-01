<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon_path',
    ];

    protected $appends = ['icon_url'];

    public function getIconUrlAttribute()
    {
        if ($this->icon_path) {
            return Storage::url($this->icon_path);
        }
        return asset('images/default-group-icon.svg');
    }

    // グループに所属するユーザー（member/admin）
    public function members()
    {
        return $this->belongsToMany(User::class, 'group_user_roles')
            ->withPivot('role')
            ->wherePivotIn('role', ['admin', 'member'])
            ->withTimestamps();
    }

    // グループの全ユーザー（invited含む）
    public function allUsers()
    {
        return $this->belongsToMany(User::class, 'group_user_roles')
            ->withPivot('role')
            ->withTimestamps();
    }

    // 管理者のみ
    public function admins()
    {
        return $this->belongsToMany(User::class, 'group_user_roles')
            ->withPivot('role')
            ->wherePivot('role', 'admin')
            ->withTimestamps();
    }

    // 招待中のユーザー
    public function invitedUsers()
    {
        return $this->belongsToMany(User::class, 'group_user_roles')
            ->withPivot('role')
            ->wherePivot('role', 'invited')
            ->withTimestamps();
    }

    // グループのイベント
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    // 特定ユーザーが管理者かどうか
    public function isAdmin(User $user): bool
    {
        return $this->admins()->where('user_id', $user->id)->exists();
    }

    // 特定ユーザーがメンバーかどうか
    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    // 特定ユーザーが招待中かどうか
    public function isInvited(User $user): bool
    {
        return $this->invitedUsers()->where('user_id', $user->id)->exists();
    }
}
