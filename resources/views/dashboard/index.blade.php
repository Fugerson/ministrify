@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page-help page="dashboard" />

<div class="space-y-4 lg:space-y-6">
    <!-- Mobile Welcome -->
    <div class="lg:hidden">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Привіт, {{ explode(' ', auth()->user()->name)[0] }}!</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ now()->locale('uk')->translatedFormat('l, d F') }}</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
        <a href="{{ route('people.index') }}" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-blue-50 dark:bg-blue-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mt-3">{{ $stats['total_people'] }}</p>
            <p class="text-xs lg:text-sm text-gray-500 dark:text-gray-400 mt-0.5">Людей</p>
        </a>

        <a href="{{ route('ministries.index') }}" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-green-50 dark:bg-green-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mt-3">{{ $stats['total_ministries'] }}</p>
            <p class="text-xs lg:text-sm text-gray-500 dark:text-gray-400 mt-0.5">Служінь</p>
        </a>

        <a href="{{ route('groups.index') }}" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-purple-50 dark:bg-purple-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mt-3">{{ $stats['total_groups'] ?? 0 }}</p>
            <p class="text-xs lg:text-sm text-gray-500 dark:text-gray-400 mt-0.5">Груп</p>
        </a>

        <a href="{{ route('schedule') }}" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-amber-50 dark:bg-amber-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mt-3">{{ $stats['events_this_month'] }}</p>
            <p class="text-xs lg:text-sm text-gray-500 dark:text-gray-400 mt-0.5">Подій в місяці</p>
        </a>
    </div>

    <!-- Quick Actions: Boards & Tasks -->
    @if(count($boards) > 0 || count($urgentTasks) > 0)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
        <!-- Active Boards -->
        @if(count($boards) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 lg:px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                    Дошки завдань
                </h2>
                <a href="{{ route('boards.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Всі</a>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($boards as $board)
                <a href="{{ route('boards.show', $board) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $board->color }}20;">
                        <div class="w-4 h-4 rounded" style="background-color: {{ $board->color }};"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ $board->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $board->cards_count }} карток</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endforeach
            </div>
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50">
                <a href="{{ route('boards.create') }}" class="flex items-center justify-center gap-2 text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Створити нову дошку
                </a>
            </div>
        </div>
        @endif

        <!-- Urgent Tasks -->
        @if(count($urgentTasks) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 lg:px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Термінові завдання
                </h2>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($urgentTasks as $task)
                <a href="{{ route('boards.cards.show', $task) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="w-3 h-3 rounded-full flex-shrink-0
                        {{ $task->priority === 'urgent' ? 'bg-red-500' : ($task->priority === 'high' ? 'bg-orange-500' : 'bg-yellow-500') }}">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ $task->title }}</p>
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ $task->column->board->name }}</span>
                            @if($task->due_date)
                            <span class="flex items-center gap-1 {{ $task->isOverdue() ? 'text-red-500' : '' }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $task->due_date->format('d.m') }}
                            </span>
                            @endif
                        </div>
                    </div>
                    @if($task->assignee)
                    <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center flex-shrink-0">
                        <span class="text-xs font-medium text-primary-600 dark:text-primary-400">{{ mb_substr($task->assignee->first_name, 0, 1) }}</span>
                    </div>
                    @endif
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Birthdays This Week -->
    @if($birthdaysThisWeek->isNotEmpty())
    <div class="bg-gradient-to-r from-pink-50 to-purple-50 dark:from-pink-900/30 dark:to-purple-900/30 rounded-2xl border border-pink-100 dark:border-pink-800 p-4">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-pink-100 dark:bg-pink-900 flex items-center justify-center">
                <span class="text-xl">🎂</span>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Дні народження цього тижня</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Не забудьте привітати!</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach($birthdaysThisWeek as $person)
            <a href="{{ route('people.show', $person) }}" class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-gray-800 rounded-xl hover:shadow-md transition-shadow">
                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                    <span class="text-xs font-medium text-primary-600 dark:text-primary-400">{{ mb_substr($person->first_name, 0, 1) }}</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $person->full_name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $person->birth_date->format('d.m') }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Pending Assignments Alert -->
    @if(count($pendingAssignments) > 0)
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/30 dark:to-orange-900/30 rounded-2xl border border-amber-100 dark:border-amber-800 p-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900 dark:text-white">Очікує підтвердження</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">У вас {{ count($pendingAssignments) }} призначень</p>
                <div class="mt-3 space-y-2">
                    @foreach($pendingAssignments->take(3) as $assignment)
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 dark:text-white text-sm truncate">{{ $assignment->event->title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $assignment->event->date->format('d.m') }} &bull; {{ $assignment->position->name }}</p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <form method="POST" action="{{ route('assignments.confirm', $assignment) }}">
                                @csrf
                                <button type="submit" class="w-9 h-9 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 rounded-lg flex items-center justify-center hover:bg-green-200 dark:hover:bg-green-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('assignments.decline', $assignment) }}">
                                @csrf
                                <button type="submit" class="w-9 h-9 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400 rounded-lg flex items-center justify-center hover:bg-red-200 dark:hover:bg-red-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
        <!-- Upcoming Events -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 lg:px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Найближчі події</h2>
                <a href="{{ route('schedule') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Всі</a>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                @forelse($upcomingEvents as $event)
                <a href="{{ route('events.show', $event) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: {{ $event->ministry->color ?? '#3b82f6' }}20;">
                        <svg class="w-6 h-6" style="color: {{ $event->ministry->color ?? '#3b82f6' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-medium text-gray-900 dark:text-white truncate">{{ $event->title }}</p>
                            @if($event->isFullyStaffed())
                            <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></span>
                            @else
                            <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $event->date->format('d.m') }} &bull; {{ $event->time->format('H:i') }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $event->filled_positions_count }}/{{ $event->total_positions_count }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">позицій</p>
                    </div>
                </a>
                @empty
                <div class="p-8 text-center">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Немає запланованих подій</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Attendance Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-5">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Відвідуваність</h2>
            <div class="h-48">
                <canvas id="attendanceChart"></canvas>
            </div>
            <div class="mt-4 flex items-center justify-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-primary-500"></span>
                    <span>За останні 4 тижні</span>
                </div>
            </div>
        </div>
    </div>

    @admin
    <!-- Analytics Charts Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-4 lg:px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Аналітика
            </h2>
            <div class="flex rounded-xl bg-gray-100 dark:bg-gray-700 p-1">
                <button type="button" data-chart="growth" class="chart-tab px-3 py-1.5 text-sm font-medium rounded-lg transition-colors bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm">
                    Зростання
                </button>
                <button type="button" data-chart="financial" class="chart-tab px-3 py-1.5 text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    Фінанси
                </button>
                <button type="button" data-chart="attendance" class="chart-tab px-3 py-1.5 text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    Відвідуваність
                </button>
                <button type="button" data-chart="ministries" class="chart-tab px-3 py-1.5 text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    Служіння
                </button>
            </div>
        </div>
        <div class="p-4 lg:p-6">
            <div class="h-72 relative">
                <div id="chartLoader" class="absolute inset-0 flex items-center justify-center">
                    <svg class="animate-spin h-8 w-8 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <canvas id="analyticsChart"></canvas>
            </div>
            <div id="chartLegend" class="mt-4 flex flex-wrap items-center justify-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                <!-- Legend will be dynamically updated -->
            </div>
        </div>
    </div>

    <!-- Financial Summary Cards (for admins) -->
    @if(isset($stats['income_this_month']) || isset($stats['expenses_this_month']))
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
        @if(isset($stats['income_this_month']))
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 rounded-2xl border border-green-100 dark:border-green-800 p-4 lg:p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['income_this_month'], 0, ',', ' ') }} ₴</p>
            <p class="text-xs lg:text-sm text-green-600 dark:text-green-400 mt-0.5">Доходи за місяць</p>
        </div>
        @endif

        @if(isset($stats['expenses_this_month']))
        <div class="bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/30 dark:to-rose-900/30 rounded-2xl border border-red-100 dark:border-red-800 p-4 lg:p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['expenses_this_month'], 0, ',', ' ') }} ₴</p>
            <p class="text-xs lg:text-sm text-red-600 dark:text-red-400 mt-0.5">Витрати за місяць</p>
        </div>
        @endif

        @if(isset($stats['income_this_month']) && isset($stats['expenses_this_month']))
        @php $balance = $stats['income_this_month'] - $stats['expenses_this_month']; @endphp
        <div class="bg-gradient-to-br {{ $balance >= 0 ? 'from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 border-blue-100 dark:border-blue-800' : 'from-amber-50 to-orange-50 dark:from-amber-900/30 dark:to-orange-900/30 border-amber-100 dark:border-amber-800' }} rounded-2xl border p-4 lg:p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl {{ $balance >= 0 ? 'bg-blue-100 dark:bg-blue-900' : 'bg-amber-100 dark:bg-amber-900' }} flex items-center justify-center">
                    <svg class="w-5 h-5 {{ $balance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ $balance >= 0 ? '+' : '' }}{{ number_format($balance, 0, ',', ' ') }} ₴</p>
            <p class="text-xs lg:text-sm {{ $balance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400' }} mt-0.5">Баланс за місяць</p>
        </div>
        @endif

        @if(count($growthData) > 0)
        @php $lastMonth = end($growthData); @endphp
        <div class="bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/30 dark:to-violet-900/30 rounded-2xl border border-purple-100 dark:border-purple-800 p-4 lg:p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">+{{ $lastMonth['count'] }}</p>
            <p class="text-xs lg:text-sm text-purple-600 dark:text-purple-400 mt-0.5">Нових за місяць</p>
        </div>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
        <!-- Ministry Budgets -->
        @if(count($ministryBudgets) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 lg:px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Бюджети служінь</h2>
                <a href="{{ route('expenses.report') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Звіт</a>
            </div>
            <div class="p-4 lg:p-5 space-y-4">
                @foreach($ministryBudgets as $budget)
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $budget['icon'] }} {{ $budget['name'] }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ number_format($budget['spent'], 0, ',', ' ') }} / {{ number_format($budget['budget'], 0, ',', ' ') }} ₴
                        </span>
                    </div>
                    <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500
                            {{ $budget['percentage'] > 90 ? 'bg-red-500' : ($budget['percentage'] > 70 ? 'bg-amber-500' : 'bg-green-500') }}"
                             style="width: {{ min(100, $budget['percentage']) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Expenses This Month -->
        @if(isset($stats['expenses_this_month']))
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900 dark:text-white">Витрати за місяць</h2>
                <a href="{{ route('expenses.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Всі</a>
            </div>
            <div class="text-center py-4">
                <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['expenses_this_month'], 0, ',', ' ') }}</p>
                <p class="text-lg text-gray-500 dark:text-gray-400 mt-1">₴</p>
            </div>
        </div>
        @endif

        <!-- People Needing Attention -->
        @if(count($needAttention) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 lg:px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <h2 class="font-semibold text-gray-900 dark:text-white">Потребують уваги</h2>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Не відвідували 3+ тижні</p>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($needAttention as $person)
                <div class="flex items-center justify-between p-4">
                    <a href="{{ route('people.show', $person) }}" class="flex items-center gap-3 hover:opacity-80">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-600 dark:to-gray-700 flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ mb_substr($person->first_name, 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $person->full_name }}</p>
                            @if($person->phone)
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $person->phone }}</p>
                            @endif
                        </div>
                    </a>
                    @if($person->phone)
                    <a href="tel:{{ $person->phone }}" class="w-9 h-9 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 rounded-lg flex items-center justify-center hover:bg-green-200 dark:hover:bg-green-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endadmin
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Small attendance chart
    const ctx = document.getElementById('attendanceChart');
    if (ctx) {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const gridColor = isDark ? '#374151' : '#f3f4f6';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json(collect($attendanceData)->pluck('date')),
                datasets: [{
                    label: 'Відвідуваність',
                    data: @json(collect($attendanceData)->pluck('count')),
                    borderColor: '{{ $currentChurch->primary_color ?? "#3b82f6" }}',
                    backgroundColor: '{{ $currentChurch->primary_color ?? "#3b82f6" }}20',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '{{ $currentChurch->primary_color ?? "#3b82f6" }}',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { color: textColor, stepSize: 10 }
                    }
                }
            }
        });
    }

    // Analytics Charts (Admin section)
    const analyticsCtx = document.getElementById('analyticsChart');
    if (!analyticsCtx) return;

    let analyticsChart = null;
    const chartLoader = document.getElementById('chartLoader');
    const chartLegend = document.getElementById('chartLegend');
    const chartTabs = document.querySelectorAll('.chart-tab');
    const primaryColor = '{{ $currentChurch->primary_color ?? "#3b82f6" }}';

    const chartColors = {
        primary: primaryColor,
        success: '#22c55e',
        danger: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6',
        purple: '#8b5cf6',
        pink: '#ec4899',
        teal: '#14b8a6',
        orange: '#f97316',
        cyan: '#06b6d4',
    };

    const colorPalette = [
        chartColors.primary, chartColors.success, chartColors.danger,
        chartColors.warning, chartColors.purple, chartColors.pink,
        chartColors.teal, chartColors.orange, chartColors.cyan, chartColors.info
    ];

    function getChartOptions(type) {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const gridColor = isDark ? '#374151' : '#f3f4f6';

        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#1f2937' : '#ffffff',
                    titleColor: isDark ? '#ffffff' : '#111827',
                    bodyColor: isDark ? '#d1d5db' : '#6b7280',
                    borderColor: isDark ? '#374151' : '#e5e7eb',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: type === 'financial' ? {
                        label: function(context) {
                            return context.dataset.label + ': ' + new Intl.NumberFormat('uk-UA').format(context.raw) + ' ₴';
                        }
                    } : {}
                }
            },
            scales: type === 'ministries' ? {} : {
                x: {
                    grid: { display: false },
                    ticks: { color: textColor }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: {
                        color: textColor,
                        callback: type === 'financial' ? function(value) {
                            return new Intl.NumberFormat('uk-UA', { notation: 'compact' }).format(value) + ' ₴';
                        } : undefined
                    }
                }
            }
        };
    }

    async function loadChart(type) {
        chartLoader.classList.remove('hidden');

        try {
            const response = await fetch(`{{ route('dashboard.charts') }}?type=${type}`);
            const data = await response.json();

            if (analyticsChart) {
                analyticsChart.destroy();
            }

            chartLoader.classList.add('hidden');

            const config = buildChartConfig(type, data);
            analyticsChart = new Chart(analyticsCtx, config);
            updateLegend(type, data);

        } catch (error) {
            console.error('Error loading chart:', error);
            chartLoader.classList.add('hidden');
        }
    }

    function buildChartConfig(type, data) {
        const isDark = document.documentElement.classList.contains('dark');

        switch(type) {
            case 'growth':
                return {
                    type: 'line',
                    data: {
                        labels: data.map(d => d.label),
                        datasets: [{
                            label: 'Загальна кількість',
                            data: data.map(d => d.value),
                            borderColor: chartColors.primary,
                            backgroundColor: chartColors.primary + '20',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: chartColors.primary,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                        }, {
                            label: 'Нові',
                            data: data.map(d => d.new),
                            borderColor: chartColors.success,
                            backgroundColor: 'transparent',
                            borderDash: [5, 5],
                            tension: 0.4,
                            pointBackgroundColor: chartColors.success,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 3,
                        }]
                    },
                    options: getChartOptions('growth')
                };

            case 'financial':
                return {
                    type: 'bar',
                    data: {
                        labels: data.map(d => d.label),
                        datasets: [{
                            label: 'Доходи',
                            data: data.map(d => d.income),
                            backgroundColor: chartColors.success + 'cc',
                            borderRadius: 6,
                            borderSkipped: false,
                        }, {
                            label: 'Витрати',
                            data: data.map(d => d.expenses),
                            backgroundColor: chartColors.danger + 'cc',
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: getChartOptions('financial')
                };

            case 'attendance':
                return {
                    type: 'line',
                    data: {
                        labels: data.map(d => d.label),
                        datasets: [{
                            label: 'Середня відвідуваність',
                            data: data.map(d => d.value),
                            borderColor: chartColors.info,
                            backgroundColor: chartColors.info + '20',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: chartColors.info,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                        }]
                    },
                    options: getChartOptions('attendance')
                };

            case 'ministries':
                return {
                    type: 'doughnut',
                    data: {
                        labels: data.map(d => d.label),
                        datasets: [{
                            data: data.map(d => d.value),
                            backgroundColor: data.map((d, i) => d.color || colorPalette[i % colorPalette.length]),
                            borderWidth: 0,
                            hoverOffset: 10,
                        }]
                    },
                    options: {
                        ...getChartOptions('ministries'),
                        cutout: '60%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.raw + ' учасників';
                                    }
                                }
                            }
                        }
                    }
                };

            default:
                return { type: 'line', data: { labels: [], datasets: [] }, options: {} };
        }
    }

    function updateLegend(type, data) {
        let html = '';

        switch(type) {
            case 'growth':
                html = `
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.primary}"></span>
                        <span>Загальна кількість</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.success}"></span>
                        <span>Нові за місяць</span>
                    </div>
                `;
                break;

            case 'financial':
                html = `
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.success}"></span>
                        <span>Доходи</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.danger}"></span>
                        <span>Витрати</span>
                    </div>
                `;
                break;

            case 'attendance':
                html = `
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.info}"></span>
                        <span>Середня відвідуваність за 12 місяців</span>
                    </div>
                `;
                break;

            case 'ministries':
                html = data.slice(0, 5).map((d, i) => `
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${d.color || colorPalette[i % colorPalette.length]}"></span>
                        <span>${d.label}: ${d.value}</span>
                    </div>
                `).join('');
                if (data.length > 5) {
                    html += `<span class="text-gray-400">+${data.length - 5} більше</span>`;
                }
                break;
        }

        chartLegend.innerHTML = html;
    }

    // Tab click handlers
    chartTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active state
            chartTabs.forEach(t => {
                t.classList.remove('bg-white', 'dark:bg-gray-600', 'text-gray-900', 'dark:text-white', 'shadow-sm');
                t.classList.add('text-gray-600', 'dark:text-gray-400');
            });
            this.classList.add('bg-white', 'dark:bg-gray-600', 'text-gray-900', 'dark:text-white', 'shadow-sm');
            this.classList.remove('text-gray-600', 'dark:text-gray-400');

            // Load chart
            loadChart(this.dataset.chart);
        });
    });

    // Load initial chart
    loadChart('growth');
});
</script>
@endpush
@endsection
