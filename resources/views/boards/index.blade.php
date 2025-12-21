@extends('layouts.app')

@section('title', 'Дошки завдань')

@section('actions')
<div class="flex items-center gap-3">
    @if($archivedCount > 0)
        <a href="{{ route('boards.archived') }}"
           class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm font-medium">
            Архів ({{ $archivedCount }})
        </a>
    @endif
    <a href="{{ route('boards.create') }}"
       class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Нова дошка
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Info -->
    <div class="bg-blue-50 dark:bg-blue-900/30 rounded-xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-sm text-blue-700 dark:text-blue-300">
                Дошки завдань допомагають організувати роботу команди. Створюйте картки, призначайте відповідальних та відстежуйте прогрес.
            </p>
        </div>
    </div>

    <!-- Boards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($boards as $board)
            <a href="{{ route('boards.show', $board) }}"
               class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-all duration-200">
                <!-- Header with color bar -->
                <div class="h-2" style="background-color: {{ $board->color }}"></div>

                <div class="p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                {{ $board->name }}
                            </h3>
                            @if($board->description)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($board->description, 60) }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="mt-4 flex items-center gap-4">
                        <div class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            {{ $board->cards_count }} карток
                        </div>
                        <div class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                            </svg>
                            {{ $board->columns_count }} колонок
                        </div>
                    </div>

                    <!-- Progress -->
                    @if($board->cards_count > 0)
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                <span>Прогрес</span>
                                <span>{{ $board->progress }}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-300"
                                     style="width: {{ $board->progress }}%; background-color: {{ $board->color }}"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </a>
        @empty
            <div class="col-span-full bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </div>
                <h3 class="font-medium text-gray-900 dark:text-white mb-2">Немає дошок</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">Створіть першу дошку для організації завдань</p>
                <a href="{{ route('boards.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Створити дошку
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
