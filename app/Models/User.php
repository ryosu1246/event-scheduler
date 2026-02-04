<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function scheduleResponses()
    {
        return $this->hasMany(EventScheduleResponse::class);
    }

    // ユーザーが所属するグループ（member/admin）
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user_roles')
            ->withPivot('role')
            ->wherePivotIn('role', ['admin', 'member'])
            ->withTimestamps();
    }

    // 招待されているグループ
    public function invitedGroups()
    {
        return $this->belongsToMany(Group::class, 'group_user_roles')
            ->withPivot('role')
            ->wherePivot('role', 'invited')
            ->withTimestamps();
    }

    // 管理者として所属するグループ
    public function adminGroups()
    {
        return $this->belongsToMany(Group::class, 'group_user_roles')
            ->withPivot('role')
            ->wherePivot('role', 'admin')
            ->withTimestamps();
    }

    protected $appends = ['profile_image_url'];

    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return Storage::url($this->profile_image);
        }

        return asset('images/default-avatar.png'); // デフォルト画像
    }
}
