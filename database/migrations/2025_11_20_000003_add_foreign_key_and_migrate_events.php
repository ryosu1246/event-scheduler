<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: group_id=nullのイベントを持つユーザーごとに「個人」グループを作成
        $usersWithNullGroupEvents = DB::table('events')
            ->whereNull('group_id')
            ->distinct()
            ->pluck('created_by');

        foreach ($usersWithNullGroupEvents as $userId) {
            // 個人グループを作成
            $groupId = DB::table('groups')->insertGetId([
                'name' => '個人',
                'description' => '個人用グループ',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // そのユーザーをadminとして登録
            DB::table('group_user_roles')->insert([
                'user_id' => $userId,
                'group_id' => $groupId,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 該当ユーザーのgroup_id=nullのイベントを更新
            DB::table('events')
                ->where('created_by', $userId)
                ->whereNull('group_id')
                ->update(['group_id' => $groupId]);
        }

        // Step 2: group_idにNOT NULL制約を追加
        Schema::table('events', function (Blueprint $table) {
            // まずgroup_idをunsignedBigIntegerに変更（既に存在する場合）
            $table->unsignedBigInteger('group_id')->nullable(false)->change();
        });

        // Step 3: 外部キー制約を追加
        Schema::table('events', function (Blueprint $table) {
            $table->foreign('group_id')
                ->references('id')
                ->on('groups')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->unsignedBigInteger('group_id')->nullable()->change();
        });
    }
};
