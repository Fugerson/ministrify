@extends('layouts.app')

@section('title', 'Дошки завдань')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Дошки завдань</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Оберіть дошку для роботи із завданнями</p>
    </div>

    <!-- Church-wide board -->
    @if($churchWideBoard)
        <div class="mb-8">
            <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Загальна дошка</h2>
            <a href="{{ route('boards.show', $churchWideBoard) }}"
               class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-lg hover:border-primary-300 dark:hover:border-primary-600 transition-all duration-200 overflow-hidden group">
                <div class="h-1.5 w-full" style="background-color: {{ $churchWideBoard->display_color }}"></div>
                <div class="p-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background-color: {{ $churchWideBoard->display_color }}20">
                            <svg class="w-6 h-6" style="color: {{ $churchWideBoard->display_color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                {{ $churchWideBoard->name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Завдання для всієї церкви</p>
                        </div>
                        <div class="flex items-center gap-4 flex-shrink-0">
                            @php
                                $total = $churchWideBoard->cards_count;
                                $completed = $churchWideBoard->completed_cards_count;
                                $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
                            @endphp
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $completed }}/{{ $total }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">завдань</div>
                            </div>
                            <div class="w-24 hidden sm:block">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-300" style="width: {{ $progress }}%; background-color: {{ $churchWideBoard->display_color }}"></div>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endif

    <!-- Ministry boards -->
    @if($ministryBoards->isNotEmpty())
        <div>
            <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Дошки команд</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($ministryBoards as $board)
                    <a href="{{ route('boards.show', $board) }}"
                       class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-lg hover:border-gray-300 dark:hover:border-gray-600 transition-all duration-200 overflow-hidden group">
                        <div class="h-1.5 w-full" style="background-color: {{ $board->display_color }}"></div>
                        <div class="p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 text-lg"
                                     style="background-color: {{ $board->display_color }}20">
                                    @if($board->ministry?->icon)
                                        {{ $board->ministry->icon }}
                                    @else
                                        <svg class="w-5 h-5" style="color: {{ $board->display_color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors truncate">
                                        {{ $board->display_name }}
                                    </h3>
                                    @php
                                        $total = $board->cards_count;
                                        $completed = $board->completed_cards_count;
                                        $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
                                    @endphp
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $completed }}/{{ $total }}</span>
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full transition-all duration-300" style="width: {{ $progress }}%; background-color: {{ $board->display_color }}"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if(!$churchWideBoard && $ministryBoards->isEmpty())
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
            </svg>
            <p class="mt-4 text-gray-500 dark:text-gray-400">Немає доступних дошок</p>
        </div>
    @endif
</div>
@endsection
