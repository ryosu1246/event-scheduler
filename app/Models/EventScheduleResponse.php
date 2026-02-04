<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventScheduleResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_schedule_id',
        'user_id',
        'status',
    ];

    public function schedule()
    {
        return $this->belongsTo(EventSchedule::class, 'event_schedule_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
