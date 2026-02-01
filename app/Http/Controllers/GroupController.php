<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GroupController extends Controller
{
    // グループ一覧ページ表示
    public function index()
    {
        return view('groups.index');
    }

    // グループ一覧JSON取得
    public function json()
    {
        $user = Auth::user();

        // ユーザーが所属するグループを取得
        $groups = $user->groups()->with(['members', 'admins'])->get();

        // 招待されているグループを取得
        $invitations = $user->invitedGroups()->get();

        return response()->json([
            'groups' => $groups->map(function ($group) use ($user) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'icon_url' => $group->icon_url,
                    'is_admin' => $group->isAdmin($user),
                    'member_count' => $group->members->count(),
                ];
            }),
            'invitations' => $invitations->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'icon_url' => $group->icon_url,
                ];
            }),
        ]);
    }

    // グループ作成
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|image|max:2048',
        ]);

        $iconPath = null;
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('group-icons', 'public');
        }

        $group = Group::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'icon_path' => $iconPath,
        ]);

        // 作成者を管理者として登録
        $group->allUsers()->attach(Auth::id(), ['role' => 'admin']);

        return response()->json([
            'message' => 'グループを作成しました',
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'icon_url' => $group->icon_url,
                'is_admin' => true,
                'member_count' => 1,
            ],
        ], 201);
    }

    // グループ詳細取得
    public function show(Group $group)
    {
        $user = Auth::user();

        // メンバーでなければアクセス拒否
        if (!$group->isMember($user) && !$group->isInvited($user)) {
            abort(403, 'このグループにアクセスする権限がありません');
        }

        return response()->json([
            'id' => $group->id,
            'name' => $group->name,
            'description' => $group->description,
            'icon_url' => $group->icon_url,
            'is_admin' => $group->isAdmin($user),
        ]);
    }

    // グループ更新（管理者のみ）
    public function update(Request $request, Group $group)
    {
        $user = Auth::user();

        if (!$group->isAdmin($user)) {
            abort(403, 'このグループを編集する権限がありません');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('icon')) {
            // 古いアイコンを削除
            if ($group->icon_path) {
                Storage::disk('public')->delete($group->icon_path);
            }
            $group->icon_path = $request->file('icon')->store('group-icons', 'public');
        }

        $group->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $group->save();

        return response()->json([
            'message' => 'グループを更新しました',
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'icon_url' => $group->icon_url,
            ],
        ]);
    }

    // グループ削除（管理者のみ）
    public function destroy(Group $group)
    {
        $user = Auth::user();

        if (!$group->isAdmin($user)) {
            abort(403, 'このグループを削除する権限がありません');
        }

        // アイコンを削除
        if ($group->icon_path) {
            Storage::disk('public')->delete($group->icon_path);
        }

        // グループを削除（cascade削除でイベントも削除される）
        $group->delete();

        return response()->json(['message' => 'グループを削除しました']);
    }

    // メンバー一覧取得
    public function members(Group $group)
    {
        $user = Auth::user();

        if (!$group->isMember($user)) {
            abort(403, 'このグループにアクセスする権限がありません');
        }

        $members = $group->members()->get()->map(function ($member) use ($group) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'profile_image_url' => $member->profile_image_url,
                'role' => $member->pivot->role,
            ];
        });

        return response()->json(['members' => $members]);
    }

    // ユーザー招待（管理者のみ）
    public function invite(Request $request, Group $group)
    {
        $currentUser = Auth::user();

        if (!$group->isAdmin($currentUser)) {
            abort(403, 'メンバーを招待する権限がありません');
        }

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'ユーザーが見つかりません'], 404);
        }

        // 既にメンバーまたは招待中かチェック
        if ($group->allUsers()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'このユーザーは既にメンバーまたは招待中です'], 422);
        }

        // 招待として追加
        $group->allUsers()->attach($user->id, ['role' => 'invited']);

        return response()->json(['message' => '招待を送信しました']);
    }

    // 招待承認
    public function accept(Group $group)
    {
        $user = Auth::user();

        if (!$group->isInvited($user)) {
            abort(403, 'この招待は存在しません');
        }

        // roleをmemberに更新
        DB::table('group_user_roles')
            ->where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->update(['role' => 'member', 'updated_at' => now()]);

        return response()->json(['message' => 'グループに参加しました']);
    }

    // 招待拒否
    public function decline(Group $group)
    {
        $user = Auth::user();

        if (!$group->isInvited($user)) {
            abort(403, 'この招待は存在しません');
        }

        // 招待を削除
        $group->allUsers()->detach($user->id);

        return response()->json(['message' => '招待を拒否しました']);
    }

    // グループから退出
    public function leave(Group $group)
    {
        $user = Auth::user();

        if (!$group->isMember($user)) {
            abort(403, 'このグループのメンバーではありません');
        }

        $memberCount = $group->members()->count();

        // ケース1: 最後の1人（グループ削除）
        if ($memberCount === 1) {
            if ($group->icon_path) {
                Storage::disk('public')->delete($group->icon_path);
            }
            $group->delete();

            return response()->json([
                'message' => 'グループから退出しました。メンバーがいなくなったためグループは削除されました。',
                'group_deleted' => true,
            ]);
        }

        // ケース2: 唯一の管理者が退会（新管理者をランダム選出）
        if ($group->isAdmin($user) && $group->admins()->count() === 1) {
            $newAdmin = $group->members()
                ->where('user_id', '!=', $user->id)
                ->inRandomOrder()
                ->first();

            if ($newAdmin) {
                DB::table('group_user_roles')
                    ->where('user_id', $newAdmin->id)
                    ->where('group_id', $group->id)
                    ->update(['role' => 'admin', 'updated_at' => now()]);
            }
        }

        // メンバーから削除
        $group->allUsers()->detach($user->id);

        return response()->json([
            'message' => 'グループから退出しました',
            'group_deleted' => false,
        ]);
    }

    // メンバー削除（管理者のみ）
    public function removeMember(Request $request, Group $group)
    {
        $currentUser = Auth::user();

        if (!$group->isAdmin($currentUser)) {
            abort(403, 'メンバーを削除する権限がありません');
        }

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $targetUserId = $validated['user_id'];

        // 自分自身は削除できない
        if ($targetUserId == $currentUser->id) {
            return response()->json(['message' => '自分自身を削除することはできません'], 422);
        }

        // メンバーかチェック
        if (!$group->allUsers()->where('user_id', $targetUserId)->exists()) {
            return response()->json(['message' => 'このユーザーはメンバーではありません'], 404);
        }

        // メンバーを削除
        $group->allUsers()->detach($targetUserId);

        return response()->json(['message' => 'メンバーを削除しました']);
    }

    // グループ内イベント一覧
    public function events(Group $group)
    {
        $user = Auth::user();

        if (!$group->isMember($user)) {
            abort(403, 'このグループにアクセスする権限がありません');
        }

        return view('events.index', [
            'groupId' => $group->id,
            'groupName' => $group->name,
            'groupDescription' => $group->description,
            'groupIconUrl' => $group->icon_url,
            'isAdmin' => $group->isAdmin($user),
            'memberCount' => $group->members()->count(),
        ]);
    }

    // 招待トークン生成（管理者のみ）
    public function generateInviteToken(Group $group)
    {
        $user = Auth::user();

        if (!$group->isAdmin($user)) {
            abort(403, '招待リンクを生成する権限がありません');
        }

        // 既存の有効なトークンがあれば削除（新しいものを生成）
        GroupInvitation::where('group_id', $group->id)
            ->where('inviter_id', $user->id)
            ->delete();

        // 新しい招待トークンを作成（24時間有効）
        $invitation = GroupInvitation::create([
            'group_id' => $group->id,
            'inviter_id' => $user->id,
            'token' => GroupInvitation::generateToken(),
            'expires_at' => now()->addHours(24),
        ]);

        return response()->json([
            'invite_url' => $invitation->invite_url,
            'token' => $invitation->token,
            'expires_at' => $invitation->expires_at->toIso8601String(),
        ]);
    }

    // 招待リンクの処理
    public function showInvite($token)
    {
        $invitation = GroupInvitation::with(['group', 'inviter'])
            ->where('token', $token)
            ->first();

        // 無効なトークン
        if (!$invitation || !$invitation->isValid()) {
            abort(404, '招待リンクが無効または期限切れです');
        }

        $user = Auth::user();

        // 未ログインの場合はログインページへ
        if (!$user) {
            return redirect()->route('login', ['redirect' => url()->current()]);
        }

        // 既にメンバーの場合はグループページへ
        if ($invitation->group->isMember($user)) {
            return redirect()->route('groups.events', $invitation->group->id);
        }

        // 招待モーダル表示用のデータをセッションに保存してグループ一覧へ
        session([
            'pending_invitation' => [
                'token' => $token,
                'group_id' => $invitation->group->id,
                'group_name' => $invitation->group->name,
                'group_icon_url' => $invitation->group->icon_url,
                'inviter_name' => $invitation->inviter->name,
            ]
        ]);

        return redirect()->route('groups.index');
    }

    // トークンで参加
    public function joinByToken($token)
    {
        $invitation = GroupInvitation::with('group')
            ->where('token', $token)
            ->first();

        if (!$invitation || !$invitation->isValid()) {
            return response()->json(['message' => '招待リンクが無効または期限切れです'], 404);
        }

        $user = Auth::user();

        // 既にメンバーの場合
        if ($invitation->group->isMember($user)) {
            return response()->json([
                'message' => '既にこのグループのメンバーです',
                'redirect' => route('groups.events', $invitation->group->id),
            ]);
        }

        // 既に招待中（group_user_roles経由）の場合は削除
        $invitation->group->allUsers()->detach($user->id);

        // メンバーとして追加
        $invitation->group->allUsers()->attach($user->id, ['role' => 'member']);

        // セッションから招待情報を削除
        session()->forget('pending_invitation');

        return response()->json([
            'message' => 'グループに参加しました',
            'redirect' => route('groups.events', $invitation->group->id),
        ]);
    }

    // トークンで招待拒否
    public function declineByToken($token)
    {
        // セッションから招待情報を削除
        session()->forget('pending_invitation');

        return response()->json(['message' => '招待を拒否しました']);
    }

    // 保留中の招待情報を取得
    public function getPendingInvitation()
    {
        $invitation = session('pending_invitation');

        if (!$invitation) {
            return response()->json(['invitation' => null]);
        }

        return response()->json(['invitation' => $invitation]);
    }
}
