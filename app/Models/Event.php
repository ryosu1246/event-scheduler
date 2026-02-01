<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'group_id',
        'created_by',
        'venue_id',
    ];

    // Event は複数のスケジュールを持つ
    public function schedules()
    {
        return $this->hasMany(EventSchedule::class, 'event_id', 'id');
    }

    public function responses()
    {
        return $this->hasManyThrough(
            EventResponse::class,     // 経由するモデル
            EventSchedule::class,     // 中間モデル
            'event_id',               // EventSchedule の外部キー
            'event_schedule_id',      // EventResponse の外部キー
            'id',                     // Event モデルの主キー
            'id'                      // EventSchedule モデルの主キー
        );
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
