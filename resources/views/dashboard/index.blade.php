@extends('layouts.app')

@section('title', 'Головна')

@section('content')
<!-- Onboarding Reminder for new admins -->
<x-onboarding-reminder />

<div class="space-y-4 lg:space-y-6 page-transition">
    <!-- Mobile Welcome -->
    <div class="lg:hidden">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Привіт, {{ explode(' ', auth()->user()->name)[0] }}!</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ now()->locale('uk')->translatedFormat('l, d F') }}</p>
    </div>

    @hasChurchRole
    <!-- Stats Grid - Informative Cards -->
    <div id="stats-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mt-4 lg:mt-6">
        <!-- People Stats -->
        <a href="{{ route('people.index') }}" id="stat-people" class="stagger-item card-hover bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5 hover:border-blue-200 dark:hover:border-blue-800 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-blue-50 dark:bg-blue-900/50 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <div class="flex items-center gap-2">
                    @if($stats['people_trend'] > 0)
                    <span class="text-xs font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/50 px-2 py-1 rounded-lg flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        +{{ $stats['people_trend'] }}
                    </span>
                    @elseif($stats['people_trend'] < 0)
                    <span class="text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/50 px-2 py-1 rounded-lg flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        {{ $stats['people_trend'] }}
                    </span>
                    @endif
                    <span class="text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/50 px-2 py-1 rounded-lg">Люди</span>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total_people'] }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">за 3 місяці</p>
            <div class="mt-2 space-y-1.5">
                @if($stats['age_stats']['children'] > 0)
                <div class="flex items-center justify-between text-xs">
                    <span class="text-amber-600 dark:text-amber-400">Діти (0-12)</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $stats['age_stats']['children'] }}</span>
                </div>
                @endif
                @if($stats['age_stats']['teens'] > 0)
                <div class="flex items-center justify-between text-xs">
                    <span class="text-purple-600 dark:text-purple-400">Підлітки (13-17)</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $stats['age_stats']['teens'] }}</span>
                </div>
                @endif
                @if($stats['age_stats']['youth'] > 0)
                <div class="flex items-center justify-between text-xs">
                    <span class="text-blue-600 dark:text-blue-400">Молодь (18-35)</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $stats['age_stats']['youth'] }}</span>
                </div>
                @endif
                @if($stats['age_stats']['adults'] > 0)
                <div class="flex items-center justify-between text-xs">
                    <span class="text-green-600 dark:text-green-400">Дорослі (36-59)</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $stats['age_stats']['adults'] }}</span>
                </div>
                @endif
                @if($stats['age_stats']['seniors'] > 0)
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-600 dark:text-gray-400">Старші (60+)</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $stats['age_stats']['seniors'] }}</span>
                </div>
                @endif
            </div>
        </a>

        <!-- Ministries Stats -->
        <a href="{{ route('ministries.index') }}" id="stat-ministries" class="stagger-item card-hover bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5 hover:border-green-200 dark:hover:border-green-800 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-green-50 dark:bg-green-900/50 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="flex items-center gap-2">
                    @if($stats['volunteers_trend'] > 0)
                    <span class="text-xs font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/50 px-2 py-1 rounded-lg flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        +{{ $stats['volunteers_trend'] }}
                    </span>
                    @elseif($stats['volunteers_trend'] < 0)
                    <span class="text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/50 px-2 py-1 rounded-lg flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        {{ $stats['volunteers_trend'] }}
                    </span>
                    @endif
                    <span class="text-xs font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/50 px-2 py-1 rounded-lg">Служіння</span>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['volunteers_count'] }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">служителів</p>
            <div class="mt-2 space-y-1.5 max-h-32 overflow-y-auto">
                @foreach($stats['ministries_list'] as $ministry)
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-600 dark:text-gray-400 truncate mr-2">{{ $ministry->name }}</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300 flex-shrink-0">{{ $ministry->members_count }}</span>
                </div>
                @endforeach
            </div>
        </a>

        <!-- Groups Stats -->
        <a href="{{ route('groups.index') }}" id="stat-groups" class="stagger-item card-hover bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5 hover:border-purple-200 dark:hover:border-purple-800 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-purple-50 dark:bg-purple-900/50 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/50 px-2 py-1 rounded-lg">Групи</span>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total_groups'] ?? 0 }}</p>
            <div class="mt-3 space-y-1.5">
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span class="text-gray-500 dark:text-gray-400">Активних</span>
                    </div>
                    <span class="font-semibold text-green-600 dark:text-green-400">{{ $stats['active_groups'] }}</span>
                </div>
                @if($stats['paused_groups'] > 0)
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                        <span class="text-gray-500 dark:text-gray-400">На паузі</span>
                    </div>
                    <span class="font-semibold text-yellow-600 dark:text-yellow-400">{{ $stats['paused_groups'] }}</span>
                </div>
                @endif
                @if($stats['vacation_groups'] > 0)
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span class="text-gray-500 dark:text-gray-400">У відпустці</span>
                    </div>
                    <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $stats['vacation_groups'] }}</span>
                </div>
                @endif
                <div class="flex items-center justify-between text-xs pt-1 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-gray-500 dark:text-gray-400">Учасників</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $stats['total_group_members'] }}</span>
                </div>
            </div>
        </a>

        <!-- Events Stats -->
        <a href="{{ route('schedule') }}" id="stat-events" class="stagger-item card-hover bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5 hover:border-amber-200 dark:hover:border-amber-800 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-amber-50 dark:bg-amber-900/50 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/50 px-2 py-1 rounded-lg">{{ now()->locale('uk')->translatedFormat('F') }}</span>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['events_this_month'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1 mb-2">подій цього місяця</p>
            <div class="space-y-1.5">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Проведено</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $stats['past_events'] }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Заплановано</span>
                    <span class="font-semibold text-amber-600 dark:text-amber-400">{{ $stats['upcoming_events'] }}</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Task Tracker hidden for now
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mt-4 lg:mt-6">
        <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Завдання
            </h2>
            <a href="{{ route('boards.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1">
                Всі завдання
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        @if(count($urgentTasks) > 0)
        <div class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3">
                @foreach($urgentTasks->take(5) as $task)
                <a href="{{ route('boards.index', ['card' => $task->id]) }}"
                   class="block bg-gray-50 dark:bg-gray-700/50 border-l-4 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 overflow-hidden
                          {{ $task->priority === 'urgent' ? 'border-l-red-500' : ($task->priority === 'high' ? 'border-l-orange-500' : 'border-l-yellow-500') }}">
                    <div class="p-3">
                        <h4 class="font-medium text-sm text-gray-900 dark:text-white line-clamp-2 mb-2">{{ $task->title }}</h4>
                        <div class="flex items-center gap-1.5 mb-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                {{ $task->column->name }}
                            </span>
                            @if($task->priority === 'urgent')
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400">
                                !
                            </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            @if($task->due_date)
                            <span class="flex items-center gap-1 {{ $task->isOverdue() ? 'text-red-500 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $task->due_date->format('d.m') }}
                            </span>
                            @else
                            <span></span>
                            @endif

                            @if($task->assignee)
                            <div class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center" title="{{ $task->assignee->full_name }}">
                                <span class="text-[10px] font-medium text-primary-600 dark:text-primary-400">{{ mb_substr($task->assignee->first_name, 0, 1) }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            @if(count($urgentTasks) > 5)
            <a href="{{ route('boards.index') }}" class="block text-center py-3 mt-3 text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 border-t border-gray-200 dark:border-gray-700">
                + ще {{ count($urgentTasks) - 5 }} завдань
            </a>
            @endif
        </div>
        @else
        <div class="p-8 text-center">
            <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="text-gray-600 dark:text-gray-400">Немає термінових завдань</p>
            <a href="{{ route('boards.index') }}" class="inline-block mt-2 text-sm text-primary-600 dark:text-primary-400 hover:underline">
                Перейти до трекера
            </a>
        </div>
        @endif
    </div>
    --}}

    <!-- Birthdays -->
    <div class="bg-gradient-to-r from-pink-50 to-purple-50 dark:from-pink-900/30 dark:to-purple-900/30 rounded-2xl border border-pink-100 dark:border-pink-800 p-4 mt-4 lg:mt-6"
         x-data="birthdayWidget()" x-cloak>
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-pink-100 dark:bg-pink-900 flex items-center justify-center">
                    <span class="text-xl">🎂</span>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">Дні народження</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <span x-text="count"></span> <span x-text="countLabel"></span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button @click="prevMonth()" class="p-1.5 rounded-lg hover:bg-pink-100 dark:hover:bg-pink-900/50 text-gray-500 dark:text-gray-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button @click="currentMonth = {{ now()->month }}; loadBirthdays()"
                        class="px-3 py-1 text-sm font-medium rounded-lg transition-colors min-w-[120px] text-center"
                        :class="currentMonth === {{ now()->month }} ? 'bg-pink-200 dark:bg-pink-800 text-pink-800 dark:text-pink-200' : 'hover:bg-pink-100 dark:hover:bg-pink-900/50 text-gray-700 dark:text-gray-300'"
                        x-text="monthName">
                </button>
                <button @click="nextMonth()" class="p-1.5 rounded-lg hover:bg-pink-100 dark:hover:bg-pink-900/50 text-gray-500 dark:text-gray-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
        <div x-show="loading" class="flex justify-center py-6">
            <svg class="w-6 h-6 animate-spin text-pink-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        </div>
        <div x-show="!loading && people.length > 0" class="flex flex-wrap gap-2">
            <template x-for="person in people" :key="person.id">
                <a :href="person.url" class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-gray-800 rounded-xl hover:shadow-md transition-shadow">
                    <template x-if="person.photo">
                        <img :src="person.photo" class="w-8 h-8 rounded-full object-cover">
                    </template>
                    <template x-if="!person.photo">
                        <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                            <span class="text-xs font-medium text-primary-600 dark:text-primary-400" x-text="person.initial"></span>
                        </div>
                    </template>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="person.name"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="person.day + ' ' + person.month_short"></p>
                    </div>
                </a>
            </template>
        </div>
        <div x-show="!loading && people.length === 0" class="text-center py-6 text-sm text-gray-500 dark:text-gray-400">
            Немає днів народження в цьому місяці
        </div>
    </div>

    @php
        $birthdayInitialData = $birthdaysThisMonth->sortBy(fn($p) => $p->birth_date->day)->values()->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->full_name,
            'initial' => mb_substr($p->first_name, 0, 1),
            'day' => $p->birth_date->format('d'),
            'month_short' => $p->birth_date->translatedFormat('M'),
            'photo' => $p->photo ? \Illuminate\Support\Facades\Storage::url($p->photo) : null,
            'url' => route('people.show', $p),
        ]);
    @endphp

    @push('scripts')
    <script>
    function birthdayWidget() {
        const monthNames = ['Січень','Лютий','Березень','Квітень','Травень','Червень','Липень','Серпень','Вересень','Жовтень','Листопад','Грудень'];
        const initialData = @json($birthdayInitialData);

        return {
            currentMonth: {{ now()->month }},
            people: initialData,
            count: initialData.length,
            loading: false,

            get monthName() {
                return monthNames[this.currentMonth - 1];
            },

            get countLabel() {
                const n = this.count;
                if (n % 10 === 1 && n % 100 !== 11) return 'особа';
                if ([2,3,4].includes(n % 10) && ![12,13,14].includes(n % 100)) return 'особи';
                return 'осіб';
            },

            prevMonth() {
                this.currentMonth = this.currentMonth === 1 ? 12 : this.currentMonth - 1;
                this.loadBirthdays();
            },

            nextMonth() {
                this.currentMonth = this.currentMonth === 12 ? 1 : this.currentMonth + 1;
                this.loadBirthdays();
            },

            async loadBirthdays() {
                this.loading = true;
                try {
                    const res = await fetch(`{{ route('dashboard.birthdays') }}?month=${this.currentMonth}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    this.people = data.people;
                    this.count = data.count;
                } catch (e) {
                    console.error('Failed to load birthdays:', e);
                } finally {
                    this.loading = false;
                }
            }
        };
    }
    </script>
    @endpush

    <!-- Pending Assignments Alert -->
    @if(count($pendingAssignments) > 0)
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/30 dark:to-orange-900/30 rounded-2xl border border-amber-100 dark:border-amber-800 p-4 mt-4 lg:mt-6">
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
                                <button type="submit" class="w-11 h-11 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 rounded-xl flex items-center justify-center hover:bg-green-200 dark:hover:bg-green-800 active:bg-green-300 dark:active:bg-green-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('assignments.decline', $assignment) }}">
                                @csrf
                                <button type="submit" class="w-11 h-11 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400 rounded-xl flex items-center justify-center hover:bg-red-200 dark:hover:bg-red-800 active:bg-red-300 dark:active:bg-red-700 transition-colors">
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

    <!-- Events & Attendance Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6 mt-4 lg:mt-6">
        <!-- Upcoming Events -->
        <div class="md:col-span-2 lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Найближчі події</h2>
                <a href="{{ route('schedule') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Всі</a>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                @forelse($upcomingEvents as $event)
                <a href="{{ route('events.show', $event) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: {{ $event->ministry?->color ?? '#3b82f6' }}30;">
                        <svg class="w-6 h-6" style="color: {{ $event->ministry?->color ?? '#3b82f6' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Відвідуваність богослужінь</h2>
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
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mt-4 lg:mt-6">
        <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Аналітика
            </h2>
            <div class="flex rounded-xl bg-gray-100 dark:bg-gray-700 p-1 overflow-x-auto">
                <button type="button" data-chart="growth" class="chart-tab px-2 sm:px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-colors bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm whitespace-nowrap">
                    Зростання
                </button>
                <button type="button" data-chart="financial" class="chart-tab px-2 sm:px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white whitespace-nowrap">
                    Фінанси
                </button>
                <button type="button" data-chart="attendance" class="chart-tab px-2 sm:px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white whitespace-nowrap">
                    Відвідуваність
                </button>
                <button type="button" data-chart="ministries" class="chart-tab px-2 sm:px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white whitespace-nowrap">
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
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mt-4 lg:mt-6">
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

    <!-- Admin Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 mt-4 lg:mt-6">
        <!-- Ministry Budgets -->
        @if(count($ministryBudgets) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Бюджети команд</h2>
                <a href="{{ route('finances.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Звіт</a>
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
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900 dark:text-white">Витрати за місяць</h2>
                <a href="{{ route('finances.expenses.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Всі</a>
            </div>

            <!-- Total -->
            <div class="text-center pb-4 mb-4 border-b border-gray-200 dark:border-gray-700">
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['expenses_this_month'], 0, ',', ' ') }} ₴</p>
            </div>

            <!-- Breakdown by category -->
            @if($expensesByCategory->isNotEmpty())
            <div class="space-y-3">
                @foreach($expensesByCategory as $category)
                @php
                    $percentage = $stats['expenses_this_month'] > 0 ? ($category['amount'] / $stats['expenses_this_month']) * 100 : 0;
                @endphp
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-700 dark:text-gray-300">{{ $category['name'] }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($category['amount'], 0, ',', ' ') }} ₴</span>
                    </div>
                    <div class="w-full h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-red-500 rounded-full" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center">Немає витрат за цей місяць</p>
            @endif
        </div>
        @endif

        <!-- People Needing Attention -->
        @if(count($needAttention) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700">
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
    @else
    <!-- Pending Approval Message for users without church role -->
    <div class="mt-6 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-2xl border border-amber-200 dark:border-amber-800 p-6 lg:p-8">
        <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-2xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Очікування підтвердження</h2>
            <p class="text-gray-600 dark:text-gray-400 max-w-md mb-6">
                Ваш акаунт створено, але адміністратор ще не надав вам доступ до системи.
                Зверніться до адміністратора вашої церкви для отримання доступу.
            </p>
            <a href="{{ route('my-profile') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Мій профіль
            </a>
        </div>
    </div>
    @endhasChurchRole
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
                        }, {
                            label: 'Залишок',
                            data: data.map(d => d.balance),
                            type: 'line',
                            borderColor: chartColors.info,
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.4,
                            pointBackgroundColor: chartColors.info,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            yAxisID: 'y',
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
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.info}"></span>
                        <span>Залишок</span>
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
