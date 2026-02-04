<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-stone-700">
        <div class="min-h-screen bg-stone-50">
            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white sticky top-0 z-50">
                    <div class="max-w-5xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between items-center">
                            <!-- 左: ページヘッダー -->
                            <div class="flex-1 min-w-0">{{ $header }}</div>

                            <!-- 中央: アプリ名 -->
                            <a href="{{ route('groups.index') }}" class="text-xl font-bold text-amber-800 hover:text-amber-900 transition shrink-0 mx-4">canrit</a>

                            <!-- 右: headerActions + アカウントドロップダウン -->
                            <div class="flex-1 min-w-0 flex items-center justify-end gap-2">
                                @isset($headerActions)
                                    {{ $headerActions }}
                                @endisset

                                @auth
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-stone-600 bg-white hover:text-stone-800 focus:outline-none transition ease-in-out duration-150">
                                            <img src="{{ Auth::user()->profile_image_url }}" alt="" class="w-8 h-8 rounded-full object-cover">
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <x-dropdown-link :href="route('mypage')">
                                            {{ __('マイページ') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('groups.index')">
                                            {{ __('マイグループ') }}
                                        </x-dropdown-link>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault();
                                                         if(confirm('ログアウトしますか？')) this.closest('form').submit();">
                                                {{ __('ログアウト') }}
                                            </x-dropdown-link>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                                @endauth
                            </div>
                        </div>
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
