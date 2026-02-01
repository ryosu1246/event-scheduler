<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            マイページ
        </h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

        {{-- 参加イベント一覧 --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">参加イベント（⚪︎回答のみ）</h3>

            @if ($responses->isEmpty())
                <p>参加中のイベントはありません。</p>
            @else
                <ul class="list-disc list-inside">
                    @foreach ($responses as $response)
                        <li>
                            {{ $response->eventSchedule->event->title ?? '不明なイベント' }}
                            （{{ $response->eventSchedule->date }}）
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            @csrf
            @method('patch')

            <div>
                <label for="profile_image" class="block text-sm font-medium text-gray-700">プロフィール画像</label>
                <div class="mt-2 flex items-center">
                    <img src="{{ Auth::user()->profile_image_url }}" alt="プロフィール画像" class="h-16 w-16 rounded-full object-cover">
                    <input id="profile_image" name="profile_image" type="file" class="ml-4 border-gray-300 rounded-md shadow-sm" accept="image/*">
                </div>
                @error('profile_image')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="bg-amber-800 hover:bg-amber-900 text-white px-4 py-2 rounded-md shadow-sm font-medium transition">更新</button>
                @if (session('status') === 'profile-updated')
                    <p class="text-sm text-gray-600">プロフィールを更新しました。</p>
                @endif
            </div>
        </form>

        {{-- プロフィール編集 --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            @include('profile.partials.update-profile-information-form')
        </div>

        {{-- パスワード変更 --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            @include('profile.partials.update-password-form')
        </div>

        {{-- アカウント削除 --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            @include('profile.partials.delete-user-form')
        </div>

    </div>
</x-app-layout>
