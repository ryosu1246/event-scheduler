<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    // 一覧表示
    public function index()
    {
        return view('events.index');
    }

    // イベント作成フォーム表示
    public function create()
    {
        return view('events.create');
    }

    // 保存処理
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'dates' => 'required', // JSON形式で受け取る
            'venue_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'group_id' => 'required|integer|exists:groups,id',
        ]);

        $user = Auth::user();
        $groupId = $validated['group_id'];

        // ユーザーがグループのメンバーかチェック
        $group = \App\Models\Group::findOrFail($groupId);
        if (! $group->isMember($user)) {
            abort(403, 'このグループにイベントを作成する権限がありません');
        }

        $venue = null;

        if (! empty($validated['venue_name'])) {
            $venue = Venue::firstOrCreate(
                ['name' => $validated['venue_name']],
                [
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ]
            );
        }

        $event = Event::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'group_id' => $groupId,
            'created_by' => auth()->id(),
            'venue_id' => $venue?->id,
        ]);

        $dates = json_decode($validated['dates'], true);

        foreach ($dates as $date) {
            $event->schedules()->create([
                'date' => $date,
            ]);
        }

        return redirect()->route('groups.events', $groupId)->with('success', 'イベントを作成しました');
    }

    public function show($id)
    {
        $event = Event::with(['schedules.responses', 'venue'])->findOrFail($id);

        // venue の lat/lng を統一
        if ($event->venue) {
            $event->venue->lat = $event->venue->latitude;
            $event->venue->lng = $event->venue->longitude;
            unset($event->venue->latitude, $event->venue->longitude); // 重複を防ぐ
        }

        // 集計処理
        $event->schedules->each(function ($schedule) {
            $schedule->response_summary = [
                'yes' => $schedule->responses->where('response', 'yes')->count(),
                'maybe' => $schedule->responses->where('response', 'maybe')->count(),
                'no' => $schedule->responses->where('response', 'no')->count(),
            ];
        });

        // Dates を配列として抽出
        $dates = $event->schedules->pluck('date');

        return response()->json([
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'dates' => $event->schedules->pluck('date'), // 配列で返す
            'venue' => $event->venue ? [
                'id' => $event->venue->id,
                'name' => $event->venue->name,
                'lat' => $event->venue->latitude,
                'lng' => $event->venue->longitude,
            ] : null,
        ]);
    }

    public function summary($eventId)
    {
        $event = Event::with('schedules.responses')->findOrFail($eventId);

        $summary = [];

        foreach ($event->schedules as $schedule) {
            $yes = $schedule->responses->where('response', 'yes')->count();
            $maybe = $schedule->responses->where('response', 'maybe')->count();
            $no = $schedule->responses->where('response', 'no')->count();

            $summary[$schedule->id] = [
                'yes' => $yes,
                'maybe' => $maybe,
                'no' => $no,
            ];
        }

        return response()->json($summary);
    }

    public function json(Request $request)
    {
        $user = Auth::user();
        $groupId = $request->query('group_id');

        $query = Event::with('schedules.responses', 'venue');

        // グループIDが指定されている場合はフィルタリング
        if ($groupId) {
            // ユーザーがそのグループのメンバーかチェック
            $group = \App\Models\Group::findOrFail($groupId);
            if (! $group->isMember($user)) {
                abort(403, 'このグループにアクセスする権限がありません');
            }
            $query->where('group_id', $groupId);
        } else {
            // グループIDが指定されていない場合は、ユーザーが所属する全グループのイベントを取得
            $userGroupIds = $user->groups()->pluck('groups.id');
            $query->whereIn('group_id', $userGroupIds);
        }

        $events = $query->get();

        // Laravel側で日付フィールド整形＋回答済みフラグ付与
        foreach ($events as $event) {
            $event->is_responded = false; // デフォルトは未回答

            foreach ($event->schedules as $schedule) {
                if (isset($schedule->start_datetime)) {
                    $schedule->date = $schedule->start_datetime->format('Y-m-d H:i');
                } elseif (isset($schedule->date)) {
                    $schedule->date = $schedule->date;
                }

                // 現在のユーザーがこのスケジュールに回答済みか確認
                if ($user && $schedule->responses->contains('user_id', $user->id)) {
                    $event->is_responded = true;
                }
            }
        }

        return response()->json($events);
    }

    public function update(Request $request, Event $event)
    {
        \Log::info('REQUEST ALL for update:', $request->all());

        if ($event->created_by !== auth()->id()) {
            abort(403, 'このイベントを編集する権限がありません');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'dates' => 'nullable|array',
            'dates.*' => 'date',
            'venue.name' => 'nullable|string|max:255',
            'venue.lat' => 'nullable|numeric',
            'venue.lng' => 'nullable|numeric',
        ]);

        $venueData = $validated['venue'] ?? null;

        // --- 会場更新または削除 ---
        if ($venueData && ! empty($venueData['name'])) {
            // 既存会場を検索 or 新規作成
            $venue = Venue::firstOrNew(['name' => $venueData['name']]);

            // 緯度経度が送られてきたら上書き
            if (array_key_exists('lat', $venueData) && array_key_exists('lng', $venueData)) {
                $venue->latitude = $venueData['lat'];
                $venue->longitude = $venueData['lng'];
            }

            $venue->save();

            $event->venue()->associate($venue);
        } else {
            // 会場名が空なら紐付け解除
            $event->venue()->dissociate();
        }

        // イベント本体の更新
        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'venue_id' => $venue ? $venue->id : null, // 会場がなければ null
        ]);

        if (! empty($validated['dates'])) {
            $event->schedules()->delete();
            foreach ($validated['dates'] as $date) {
                $event->schedules()->create(['date' => $date]);
            }
        } else {
            // 空の場合は削除だけするか、必要に応じてスキップ
            $event->schedules()->delete();
        }

        // --- 日程の再登録 ---
        $event->schedules()->delete();
        foreach ($validated['dates'] as $date) {
            $event->schedules()->create(['date' => $date]);
        }

        \Log::info('Event updated successfully', [
            'event_id' => $event->id,
            'venue_id' => $event->venue_id,
        ]);

        return response()->json(['message' => 'イベントを更新しました']);
    }

    public function destroy(Event $event)
    {
        if ($event->created_by !== auth()->id()) {
            abort(403, 'このイベントを削除する権限がありません');
        }

        $event->delete();

        return response()->json(['message' => 'イベントを削除しました']);
    }
}
