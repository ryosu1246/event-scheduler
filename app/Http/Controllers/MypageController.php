<?php

namespace App\Http\Controllers;

use App\Models\EventResponse;
use Illuminate\Http\Request;

class MypageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // 「yes」だけの参加イベントを取得
        $responses = EventResponse::with(['eventSchedule.event'])
            ->where('user_id', $user->id)
            ->where('response', 'yes')
            ->orderByDesc('created_at')->get();

        return view('mypage', compact('user', 'responses'));
    }
}
