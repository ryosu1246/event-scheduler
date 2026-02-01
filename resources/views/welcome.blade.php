<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>canrit - イベント管理をシンプルに</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-stone-700 bg-white">
        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-50">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <a href="/" class="text-2xl font-bold text-amber-800">
                        canrit
                    </a>

                    <!-- Navigation -->
                    @if (Route::has('login'))
                        <nav class="flex items-center gap-2">
                            @auth
                                <a
                                    href="{{ url('/dashboard') }}"
                                    class="px-4 py-2 text-sm font-medium text-stone-600 hover:text-stone-900"
                                >
                                    ダッシュボード
                                </a>
                            @else
                                <a
                                    href="{{ route('login') }}"
                                    class="px-4 py-2 text-sm font-medium text-stone-600 hover:text-stone-900"
                                >
                                    ログイン
                                </a>

                                @if (Route::has('register'))
                                    <a
                                        href="{{ route('register') }}"
                                        class="px-4 py-2 text-sm font-medium text-white bg-amber-800 hover:bg-amber-900 rounded shadow-sm"
                                    >
                                        新規登録
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="py-20 sm:py-28">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-5xl sm:text-6xl font-bold text-amber-800 mb-6">
                    canrit
                </h1>
                <p class="text-xl sm:text-2xl text-stone-600 mb-10">
                    イベント管理をもっとシンプルに。
                </p>
                @if (Route::has('register'))
                    <a
                        href="{{ route('register') }}"
                        class="inline-block px-8 py-4 text-lg font-semibold text-white bg-amber-800 hover:bg-amber-900 rounded shadow-md hover:shadow-lg"
                    >
                        無料ではじめる
                    </a>
                @endif
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-16 sm:py-20">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-center text-stone-800 mb-12">
                    主な機能
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Feature 1: Schedule -->
                    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg">
                        <div class="w-12 h-12 bg-stone-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-stone-800 mb-2">日程調整</h3>
                        <p class="text-stone-600 text-sm">
                            グループで日程候補を共有し、みんなの都合に合う日を見つけられます。
                        </p>
                    </div>

                    <!-- Feature 2: Members -->
                    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg">
                        <div class="w-12 h-12 bg-stone-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-stone-800 mb-2">メンバー管理</h3>
                        <p class="text-stone-600 text-sm">
                            QRコードやリンクで簡単にメンバーを招待できます。
                        </p>
                    </div>

                    <!-- Feature 3: RSVP -->
                    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg">
                        <div class="w-12 h-12 bg-stone-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-stone-800 mb-2">出欠回答</h3>
                        <p class="text-stone-600 text-sm">
                            ○△×のワンタップで素早く回答。
                        </p>
                    </div>

                    <!-- Feature 4: Fee Calculator -->
                    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg">
                        <div class="w-12 h-12 bg-stone-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-stone-800 mb-2">会費計算</h3>
                        <p class="text-stone-600 text-sm">
                            参加人数で割り勘、一人あたりの金額を自動計算。
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-16 sm:py-20 bg-amber-800">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-6">
                    今すぐ canrit をはじめよう
                </h2>
                <p class="text-amber-100 mb-8">
                    無料で登録、すぐに使えます。
                </p>
                @if (Route::has('register'))
                    <a
                        href="{{ route('register') }}"
                        class="inline-block px-8 py-4 text-lg font-semibold text-amber-800 bg-white hover:bg-amber-50 rounded shadow-md hover:shadow-lg"
                    >
                        無料ではじめる
                    </a>
                @endif
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-8 bg-stone-100">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="flex justify-center gap-6 mb-4 text-sm">
                    <a href="{{ route('terms') }}" class="text-stone-600 hover:text-amber-800">利用規約</a>
                    <a href="{{ route('privacy') }}" class="text-stone-600 hover:text-amber-800">プライバシーポリシー</a>
                </div>
                <p class="text-stone-500 text-sm">
                    &copy; {{ date('Y') }} canrit. All rights reserved.
                </p>
            </div>
        </footer>
    </body>
</html>
