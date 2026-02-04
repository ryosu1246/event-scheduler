<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <!-- 左: 戻るボタン + グループ名 -->
            <div class="flex items-center gap-3">
                @if(isset($groupId))
                    <a href="{{ route('groups.index') }}" class="text-stone-400 hover:text-stone-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                @endif
                <div>
                    <h2 class="font-semibold text-lg text-stone-800 leading-tight">
                        @if(isset($groupName))
                            {{ $groupName }}
                        @else
                            イベント一覧
                        @endif
                    </h2>
                    @if(isset($groupDescription) && $groupDescription)
                        <p class="text-xs text-stone-500 mt-0.5">{{ $groupDescription }}</p>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    @if(isset($groupId))
    <x-slot name="headerActions">
        <button id="open-member-modal-title"
            class="flex items-center gap-1 px-3 py-1.5 text-sm text-stone-600 hover:bg-stone-100 rounded-full transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            {{ $memberCount ?? 0 }}
        </button>
        <button id="open-manage-modal"
            class="p-2 text-stone-400 hover:text-stone-600 hover:bg-stone-100 rounded-full transition"
            title="グループ設定">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </button>
    </x-slot>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- 成功メッセージ --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- イベント作成フォーム（グループIDがある場合のみ表示） --}}
            @if(isset($groupId))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8">
                <form action="{{ route('events.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="group_id" value="{{ $groupId }}">

                    <h3 class="text-lg font-semibold mb-4">イベント作成</h3>
                    <div>
                        <label for="title" class="block font-medium">イベント名</label>
                        <input type="text" name="title" id="title" required class="border border-stone-300 p-2 w-full rounded-md focus:border-amber-500 focus:ring-amber-500">
                    </div>

                    <div class="mt-4">
                        <label for="description" class="block font-medium">コメント</label>
                        <textarea name="description" id="description" class="border border-stone-300 p-2 w-full rounded-md focus:border-amber-500 focus:ring-amber-500"></textarea>
                    </div>

                    <div id="app" class="mt-4">
                        {{-- イベント日程 Vueコンポーネント --}}
                        <event-schedule></event-schedule>
                    </div>

                    {{--  イベント会場 Vueコンポーネント --}}
                    <div id="vue-event-venue" class="mt-4">
                        <event-venue></event-venue>
                    </div>

                    <button type="submit" class="mt-4 px-4 py-2 bg-amber-800 hover:bg-amber-900 text-white rounded-md shadow-sm font-medium transition">
                        作成
                    </button>
                </form>
            </div>
            @endif

            {{-- イベント一覧+モーダル --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">イベントリスト</h3>

                <div id="vue-event-list" class="mt-12" data-group-id="{{ $groupId ?? '' }}"></div>

            </div>
        </div>
    </div>
    <script>
      window.currentUserId = {{ auth()->id() ?? 'null' }};
      window.groupId = {{ $groupId ?? 'null' }};
      window.groupInfo = {
        id: {{ $groupId ?? 'null' }},
        name: @json($groupName ?? ''),
        description: @json($groupDescription ?? ''),
        iconUrl: @json($groupIconUrl ?? ''),
        isAdmin: {{ isset($isAdmin) && $isAdmin ? 'true' : 'false' }},
        memberCount: {{ $memberCount ?? 0 }},
      };
    </script>
</x-app-layout>
