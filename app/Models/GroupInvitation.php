<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GroupInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'inviter_id',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * グループとのリレーション
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * 招待者（ユーザー）とのリレーション
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    /**
     * 招待が有効かどうかをチェック
     */
    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }

    /**
     * 有効な招待のみ取得するスコープ
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * 新しいトークンを生成
     */
    public static function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * 招待URLを取得
     */
    public function getInviteUrlAttribute(): string
    {
        return url('/invite/' . $this->token);
    }
}
