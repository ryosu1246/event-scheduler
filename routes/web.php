<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventResponseController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// 利用規約・プライバシーポリシー
Route::view('/terms', 'legal.terms')->name('terms');
Route::view('/privacy', 'legal.privacy')->name('privacy');

Route::get('/vue-test', function () {
    return view('vue-test');
});

Route::get('/dashboard', function () {
    return redirect()->route('groups.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// 招待リンク（認証不要、内部でチェック）
Route::get('/invite/{token}', [GroupController::class, 'showInvite'])->name('invite.show');

Route::middleware('auth')->group(function () {
    // プロフィール関連（マイページで使用するため、update/destroyは維持）
    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // マイページ・管理画面
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage');
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');

    // グループ関連ルート
    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/json', [GroupController::class, 'json'])->name('groups.json');
    Route::get('/groups/pending-invitation', [GroupController::class, 'getPendingInvitation'])->name('groups.pendingInvitation');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');
    Route::put('/groups/{group}', [GroupController::class, 'update'])->name('groups.update');
    Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');
    Route::get('/groups/{group}/members', [GroupController::class, 'members'])->name('groups.members');
    Route::post('/groups/{group}/invite', [GroupController::class, 'invite'])->name('groups.invite');
    Route::post('/groups/{group}/accept', [GroupController::class, 'accept'])->name('groups.accept');
    Route::post('/groups/{group}/decline', [GroupController::class, 'decline'])->name('groups.decline');
    Route::post('/groups/{group}/leave', [GroupController::class, 'leave'])->name('groups.leave');
    Route::delete('/groups/{group}/members', [GroupController::class, 'removeMember'])->name('groups.removeMember');
    Route::get('/groups/{group}/events', [GroupController::class, 'events'])->name('groups.events');
    Route::post('/groups/{group}/generate-invite', [GroupController::class, 'generateInviteToken'])->name('groups.generateInvite');
    Route::post('/invite/{token}/join', [GroupController::class, 'joinByToken'])->name('invite.join');
    Route::post('/invite/{token}/decline', [GroupController::class, 'declineByToken'])->name('invite.decline');

    // イベント関連ルート
    Route::get('/events', function () {
        return redirect()->route('groups.index');
    })->name('events.index');
    Route::get('/events/json', [EventController::class, 'json'])->name('events.json');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::get('/events/{event}/summary', [EventResponseController::class, 'summary'])->name('events.summary');
    Route::post('/events/{event}/responses', [EventResponseController::class, 'store'])->name('events.responses.store');
    Route::get('/schedules/{schedule}/respondents', [EventResponseController::class, 'respondents'])->name('schedules.respondents');
});

require __DIR__.'/auth.php';

Route::get('/api/events-list', function () {
    return \App\Models\Event::all();
});

Route::get('/api/google-api-key', function () {
    $limit = 9000; // 月間利用上限回数
    $key = env('VITE_GOOGLE_MAPS_API_KEY');

    // 現在の年月でキャッシュキーを分ける（例：google_api_usage_2025_10）
    $monthKey = 'google_api_usage_'.now()->format('Y_m');

    // 現在の使用回数を取得
    $count = Cache::get($monthKey, 0);

    // 上限チェック
    if ($count >= $limit) {
        Log::warning('Google API 利用上限到達', [
            'monthKey' => $monthKey,
            'count' => $count,
        ]);

        return Response::json([
            'status' => 'limit_reached',
            'message' => 'Google APIの利用上限に達しました（翌月に自動リセットされます）。',
            'key' => null,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    // 使用回数を1増加（初回は有効期限を月末まで）
    if (! Cache::has($monthKey)) {
        // 例：今月末23:59:59まで
        $endOfMonth = now()->endOfMonth();
        Cache::put($monthKey, 1, $endOfMonth);
    } else {
        Cache::increment($monthKey);
    }

    return Response::json([
        'status' => 'ok',
        'count' => $count + 1,
        'limit' => $limit,
        'key' => $key,
    ], 200, [], JSON_UNESCAPED_UNICODE);
});
