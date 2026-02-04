<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            イベント作成
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('events.store') }}" method="POST">
                    @csrf

                    <div>
                        <label for="title">イベント名</label>
                        <input type="text" name="title" id="title" required class="border p-2 w-full">
                    </div>

                    <div>
                        <label for="description">説明</label>
                        <textarea name="description" id="description" class="border p-2 w-full"></textarea>
                    </div>

                    <div id="app" class="mt-4">
                        <event-schedule></event-schedule>
                    </div>

                    <button type="submit" class="mt-4 px-8 py-2 bg-amber-800 hover:bg-amber-900 text-white rounded-md shadow-sm font-medium transition">
                        作成
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
