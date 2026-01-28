@extends('layouts.app')

@section('title', 'Архівовані дошки')

@section('actions')
<a href="{{ route('boards.index') }}"
   class="inline-flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 text-sm font-medium">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Назад до дошок
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Info -->
    <div class="bg-yellow-50 dark:bg-yellow-900/30 rounded-xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
        </svg>
        <div>
            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                Архівовані дошки не відображаються в основному списку. Ви можете відновити їх у будь-який час.
            </p>
        </div>
    </div>

    <!-- Archived Boards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($boards as $board)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden opacity-75 hover:opacity-100 transition-opacity">
                <div class="h-2" style="background-color: {{ $board->color }}"></div>

                <div class="p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                {{ $board->name }}
                            </h3>
                            @if($board->description)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($board->description, 60) }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <form method="POST" action="{{ route('boards.restore', $board) }}">
                            @csrf
                            <button type="submit"
                                    class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Відновити
                            </button>
                        </form>
                        <form method="POST" action="{{ route('boards.destroy', $board) }}"
                              onsubmit="return confirm('Видалити назавжди?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="px-3 py-1.5 text-red-600 hover:text-red-700 text-sm font-medium">
                                Видалити
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
                <h3 class="font-medium text-gray-900 dark:text-white mb-2">Немає архівованих дошок</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Архівовані дошки з'являться тут</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
