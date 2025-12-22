@extends('layouts.app')

@section('title', 'Дошки завдань')

@section('actions')
<div class="flex items-center gap-3">
    @if($archivedCount > 0)
        <a href="{{ route('boards.archived') }}"
           class="flex items-center gap-2 px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            <span>Архів</span>
            <span class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 text-xs rounded-full">{{ $archivedCount }}</span>
        </a>
    @endif
    <a href="{{ route('boards.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-md">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Нова дошка
    </a>
</div>
@endsection

@section('content')
<x-page-help page="boards" />

<div class="space-y-6">
    <!-- Quick Stats -->
    @if($boards->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $boards->count() }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Активних дошок</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $boards->sum('cards_count') }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Всього карток</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $boards->sum(fn($b) => $b->cards->where('is_completed', true)->count()) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Завершено</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                @php
                    $overdue = $boards->sum(fn($b) => $b->cards->filter(fn($c) => $c->due_date && $c->due_date->isPast() && !$c->is_completed)->count());
                @endphp
                <p class="text-2xl font-bold {{ $overdue > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ $overdue }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Прострочено</p>
            </div>
        </div>
    @endif

    <!-- Boards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($boards as $board)
            <a href="{{ route('boards.show', $board) }}"
               class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl hover:border-transparent hover:ring-2 hover:ring-primary-500/50 transition-all duration-300 transform hover:-translate-y-1">
                <!-- Color Header -->
                <div class="h-24 relative overflow-hidden" style="background: linear-gradient(135deg, {{ $board->color }} 0%, {{ $board->color }}cc 100%);">
                    <div class="absolute inset-0 bg-gradient-to-br from-black/0 to-black/20"></div>
                    <div class="absolute bottom-3 left-4 right-4 flex items-end justify-between">
                        <h3 class="font-bold text-white text-lg drop-shadow-sm">{{ $board->name }}</h3>
                        <div class="flex items-center gap-1 bg-white/20 backdrop-blur-sm rounded-lg px-2 py-1">
                            <svg class="w-3.5 h-3.5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span class="text-white text-xs font-medium">{{ $board->cards_count }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    @if($board->description)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 line-clamp-2">{{ $board->description }}</p>
                    @endif

                    <!-- Progress -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="text-gray-500 dark:text-gray-400">Прогрес</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $board->progress }}%</span>
                        </div>
                        <div class="w-full h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 ease-out"
                                 style="width: {{ $board->progress }}%; background-color: {{ $board->color }}"></div>
                        </div>
                    </div>

                    <!-- Columns preview -->
                    <div class="flex items-center gap-2">
                        @foreach($board->columns->take(4) as $column)
                            <div class="flex items-center gap-1 px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                <div class="w-1.5 h-1.5 rounded-full"
                                     style="background-color: {{ $column->color === 'gray' ? '#9ca3af' : ($column->color === 'blue' ? '#3b82f6' : ($column->color === 'yellow' ? '#eab308' : ($column->color === 'green' ? '#22c55e' : '#9ca3af'))) }}"></div>
                                <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">{{ $column->cards->count() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Hover overlay -->
                <div class="absolute inset-0 bg-primary-600/5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
            </a>
        @empty
            <!-- Empty State -->
            <div class="col-span-full">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Почніть з першої дошки</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
                        Дошки допомагають організувати завдання вашої команди. Створюйте картки, призначайте відповідальних та відстежуйте прогрес.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <a href="{{ route('boards.create') }}"
                           class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Створити дошку
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
