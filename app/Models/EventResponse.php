<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventResponse extends Model
{
    use HasFactory;

    protected $table = 'event_responses';

    protected $fillable = [
        'event_schedule_id',
        'user_id',
        'response', // ENUM('yes','maybe','no')
    ];

    public function eventSchedule()
    {
        return $this->belongsTo(EventSchedule::class, 'event_schedule_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
