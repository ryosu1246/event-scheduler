<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['event_id', 'date'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function responses()
    {
        return $this->hasMany(EventResponse::class, 'event_schedule_id');
    }

}
