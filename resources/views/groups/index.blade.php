<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-stone-800 leading-tight">
            マイグループ
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div id="vue-group-list"></div>
            </div>
        </div>
    </div>
    <script>
        window.currentUserId = {{ auth()->id() ?? 'null' }};
    </script>
</x-app-layout>
