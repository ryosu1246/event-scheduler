<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventResponse;
use App\Models\EventSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventResponseController extends Controller
{
    public function store(Request $request, $eventId)
    {
        $data = $request->validate([
            'responses' => 'required|array',
        ]);

        $userId = Auth::id();

        foreach ($data['responses'] as $scheduleId => $response) {
            EventResponse::updateOrCreate(
                [
                    'event_schedule_id' => $scheduleId,
                    'user_id' => $userId,
                ],
                [
                    'response' => $response,
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    // public function summary($eventId)
    // {
    //     // $event = Event::findOrFail($eventId);

    //     // $summary = [
    //     //     'circle' => $event->responses()->where('response', 'circle')->count(),
    //     //     'triangle' => $event->responses()->where('response', 'triangle')->count(),
    //     //     'cross' => $event->responses()->where('response', 'cross')->count(),
    //     // ];

    //     // return response()->json($summary);

    //     $schedules = \App\Models\EventSchedule::where('event_id', $eventId)->get();

    //     $summaries = [];

    //     foreach ($schedules as $schedule) {
    //         $summaries[] = [
    //             'event_schedule_id' => $schedule->id,
    //             'circle' => \App\Models\EventResponse::where('event_schedule_id', $schedule->id)
    //                 ->where('response', 'yes')
    //                 ->count(),
    //             'triangle' => \App\Models\EventResponse::where('event_schedule_id', $schedule->id)
    //                 ->where('response', 'maybe')
    //                 ->count(),
    //             'cross' => \App\Models\EventResponse::where('event_schedule_id', $schedule->id)
    //                 ->where('response', 'no')
    //                 ->count(),
    //         ];
    //     }

    // return response()->json($summaries);
    // }

    public function summary(Event $event)
    {
        $summary = $event->schedules->map(function ($schedule) {
            return [
                'event_schedule_id' => $schedule->id,
                'yes' => $schedule->responses()->where('response', 'yes')->count(),
                'maybe' => $schedule->responses()->where('response', 'maybe')->count(),
                'no' => $schedule->responses()->where('response', 'no')->count(),
            ];
        });

        return response()->json($summary);
    }

    public function respondents(EventSchedule $schedule)
    {
        $responses = $schedule->responses()
            ->with('user:id,name,profile_image')
            ->get()
            ->map(function ($response) {
                return [
                    'user_id' => $response->user_id,
                    'name' => $response->user->name,
                    'profile_image' => $response->user->profile_image_url ?? null,
                    'response' => $response->response,
                ];
            });

        return response()->json($responses);
    }
}
