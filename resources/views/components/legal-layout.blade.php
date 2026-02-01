<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? 'canrit' }}</title>

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

        <!-- Content -->
        <main class="py-12 sm:py-16">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>

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
