@extends('layouts.app')

@section('title', 'Розклад')

@section('actions')
@if(auth()->user()->can('create', \App\Models\Event::class))
<a href="{{ route('events.create') }}"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Нова подія
</a>
@endif
@endsection

@section('content')
@php
    $months = ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];
    $daysShort = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Нд'];
    $daysFull = ['Понеділок', 'Вівторок', 'Середа', 'Четвер', "П'ятниця", 'Субота', 'Неділя'];

    $prevMonth = $month == 1 ? 12 : $month - 1;
    $prevYear = $month == 1 ? $year - 1 : $year;
    $nextMonth = $month == 12 ? 1 : $month + 1;
    $nextYear = $month == 12 ? $year + 1 : $year;

    $maxWeek = \Carbon\Carbon::create($year, 1, 1)->isoWeeksInYear;
    $prevWeek = $currentWeek ? ($currentWeek == 1 ? \Carbon\Carbon::create($year - 1, 1, 1)->isoWeeksInYear : $currentWeek - 1) : null;
    $prevWeekYear = $currentWeek == 1 ? $year - 1 : $year;
    $nextWeek = $currentWeek ? ($currentWeek >= $maxWeek ? 1 : $currentWeek + 1) : null;
    $nextWeekYear = ($currentWeek && $currentWeek >= $maxWeek) ? $year + 1 : $year;
@endphp

<div class="space-y-4"
     x-data="{
        activeTab: '{{ request('tab', 'calendar') }}',
        ...calendarNavigator({{ json_encode(['view' => $view, 'year' => $year, 'month' => $month, 'week' => $currentWeek ?? null]) }})
     }"
     x-init="
        $watch('activeTab', val => {
            const url = new URL(window.location);
            url.searchParams.set('tab', val);
            window.history.replaceState({}, '', url);
            if (val === 'matrix') { $dispatch('load-matrix'); }
        });
        if (activeTab === 'matrix') { $nextTick(() => $dispatch('load-matrix')); }
     ">

    {{-- Shared Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            {{-- Tab Toggle + View Controls --}}
            <div class="flex items-center gap-3">
                {{-- Tab Toggle: Календар / Призначення --}}
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <button @click="activeTab = 'calendar'" type="button"
                       :class="activeTab === 'calendar'
                           ? 'bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300'
                           : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                       class="px-3 sm:px-4 py-2 text-sm font-medium rounded-xl whitespace-nowrap transition-colors">
                        {{ __('Календар') }}
                    </button>
                    <button @click="activeTab = 'matrix'" type="button"
                       :class="activeTab === 'matrix'
                           ? 'bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300'
                           : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                       class="px-3 sm:px-4 py-2 text-sm font-medium rounded-xl whitespace-nowrap transition-colors">
                        {{ __('Призначення') }}
                    </button>
                </div>

                {{-- Calendar: Week/Month toggle --}}
                <div x-show="activeTab === 'calendar'" class="flex items-center gap-3">
                    <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 hidden sm:block"></div>
                    <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-xl p-1">
                        <button @click="switchView('week')" type="button"
                           :class="currentView === 'week' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                           class="px-4 py-2 text-sm font-medium rounded-lg transition-colors">
                            {{ __('app.week') }}
                        </button>
                        <button @click="switchView('month')" type="button"
                           :class="currentView === 'month' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                           class="px-4 py-2 text-sm font-medium rounded-lg transition-colors">
                            {{ __('app.month') }}
                        </button>
                    </div>
                </div>

                {{-- Matrix: teleport target for filters --}}
                <div x-show="activeTab === 'matrix'" id="matrix-header-slot" class="flex items-center gap-2"></div>
            </div>

            {{-- Calendar: Date Navigation --}}
            <div x-show="activeTab === 'calendar'" class="flex items-center justify-between sm:justify-center gap-2 sm:gap-4">
                <button @click="prevPeriod()" type="button"
                   class="w-11 h-11 sm:w-10 sm:h-10 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 dark:active:bg-gray-600 rounded-xl transition-colors">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                {{-- Month/Week Picker Button --}}
                <div x-data="{ showPicker: false }" class="relative">
                    <button @click="showPicker = !showPicker" type="button"
                       class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white min-w-[140px] sm:min-w-[200px] text-center px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors cursor-pointer">
                        <span x-show="currentView === 'week'" x-text="weekDisplay"></span>
                        <span x-show="currentView === 'month'" x-text="monthDisplay"></span>
                        <svg class="w-4 h-4 inline-block ml-2 transition-transform" :class="showPicker ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                    </button>

                    {{-- Calendar Picker Dropdown --}}
                    <div x-show="showPicker" x-transition @click.outside="showPicker = false"
                         class="absolute top-full mt-2 left-1/2 -translate-x-1/2 z-50 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4">
                        <div x-show="currentView === 'month'" class="space-y-3">
                            <div class="flex items-center justify-between mb-3">
                                <button @click="currentYear--; loadCalendar()" type="button" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                                <span class="font-semibold" x-text="currentYear"></span>
                                <button @click="currentYear++; loadCalendar()" type="button" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <template x-for="(month, index) in ['Січ', 'Лют', 'Бер', 'Кві', 'Тра', 'Чер', 'Лип', 'Сер', 'Вер', 'Жов', 'Лис', 'Гру']" :key="index">
                                    <button @click="currentMonth = index + 1; showPicker = false; loadCalendar()" type="button"
                                       :class="currentMonth === index + 1 ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600'"
                                       class="p-2 rounded-lg font-medium text-sm transition-colors">
                                        <span x-text="month"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div x-show="currentView === 'week'" class="space-y-3">
                            <div class="flex items-center justify-between mb-3">
                                <button @click="currentYear--; loadCalendar()" type="button" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                                <span class="font-semibold" x-text="currentYear"></span>
                                <button @click="currentYear++; loadCalendar()" type="button" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <template x-for="(month, index) in ['Січ', 'Лют', 'Бер', 'Кві', 'Тра', 'Чер', 'Лип', 'Сер', 'Вер', 'Жов', 'Лис', 'Гру']" :key="index">
                                    <button @click="currentMonth = index + 1" type="button"
                                       :class="currentMonth === index + 1 ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600'"
                                       class="p-2 rounded-lg font-medium text-xs transition-colors">
                                        <span x-text="month"></span>
                                    </button>
                                </template>
                            </div>
                            <div class="grid grid-cols-4 gap-2 max-h-48 overflow-y-auto">
                                <template x-for="week in Array.from({length: 52}, (_, i) => i + 1)" :key="week">
                                    <button @click="currentWeek = week; showPicker = false; loadCalendar()" type="button"
                                       :class="currentWeek === week ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600'"
                                       class="p-2 rounded-lg font-medium text-xs transition-colors">
                                        <span x-text="'W' + week"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <button @click="nextPeriod()" type="button"
                   class="w-11 h-11 sm:w-10 sm:h-10 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 dark:active:bg-gray-600 rounded-xl transition-colors">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            {{-- Actions (Calendar tab) --}}
            <div x-show="activeTab === 'calendar'" class="flex items-center gap-2">
                @if(auth()->user()->canEdit('events'))
                @if($isGoogleConnected)
                    <div x-data="{
                        syncing: false,
                        message: '',
                        error: false,
                        lastSynced: '{{ $lastSyncedAt ?? '' }}',
                        get syncStatus() {
                            if (this.syncing) return '{{ __('app.syncing') }}';
                            if (!this.lastSynced) return '{{ __('app.not_synced') }}';
                            const diff = Math.floor((Date.now() - new Date(this.lastSynced).getTime()) / 60000);
                            if (diff < 1) return '{{ __('app.just_now') }}';
                            if (diff < 60) return diff + ' {{ __('app.min_ago') }}';
                            if (diff < 1440) return Math.floor(diff / 60) + ' {{ __('app.hour_ago') }}';
                            return Math.floor(diff / 1440) + ' {{ __('app.day_ago') }}';
                        }
                    }" class="inline-flex items-center gap-1.5">
                        <span class="text-xs text-gray-500 dark:text-gray-400 hidden sm:inline" x-text="syncStatus"></span>
                        <button @click="
                            syncing = true; message = ''; error = false;
                            fetch('{{ route('settings.google-calendar.full-sync-all') }}', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                body: JSON.stringify({})
                            })
                            .then(r => r.json())
                            .then(data => {
                                syncing = false;
                                message = data.message || data.error || 'Готово';
                                error = !data.success;
                                if (data.success) lastSynced = new Date().toISOString();
                            })
                            .catch(e => { syncing = false; message = 'Помилка з\'єднання'; error = true; })
                        "
                                :disabled="syncing"
                                title="{{ __('app.sync_google') }}"
                                class="w-9 h-9 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors disabled:opacity-50">
                            <svg x-show="!syncing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <svg x-show="syncing" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </button>
                        <template x-if="message">
                            <span x-text="message" :class="error ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'" class="text-xs max-w-[200px] truncate" x-transition></span>
                        </template>
                    </div>
                @else
                    <a href="{{ route('settings.index') }}#google-calendar"
                       class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-lg transition-colors"
                       title="Підключити Google Calendar">
                        <svg class="w-4 h-4 mr-1.5 text-blue-500" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0C5.383 0 0 5.383 0 12s5.383 12 12 12 12-5.383 12-12S18.617 0 12 0zM9.857 17.143H6.857v-3h3v3zm0-4.286H6.857V9.857h3v3zm4.286 4.286h-3v-3h3v3zm0-4.286h-3V9.857h3v3zm4.286 4.286h-3v-3h3v3zm0-4.286h-3V9.857h3v3z"/>
                        </svg>
                        Google Calendar
                    </a>
                @endif
                @endif

                {{-- Export/Import Dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false"
                            class="p-2 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                        </svg>
                    </button>

                    <div x-show="open" x-cloak x-transition
                         class="absolute right-0 mt-2 w-56 max-w-[calc(100vw-2rem)] bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50">
                        <div class="py-1">
                            <a href="{{ route('calendar.export') }}"
                               class="flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                {{ __('app.export_ics') }}
                            </a>
                            <a href="{{ route('calendar.export', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"
                               class="flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ __('app.export_current_period') }}
                            </a>
                            <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                            @if(auth()->user()->can('create', \App\Models\Event::class))
                            <a href="{{ route('calendar.import') }}"
                               class="flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                {{ __('app.import_ics') }}
                            </a>
                            @endif
                            <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                            <button onclick="showSubscriptionModal()"
                                    class="flex items-center w-full px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                {{ __('app.subscribe_calendar') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================== --}}
    {{-- CALENDAR TAB                   --}}
    {{-- ============================== --}}
    <div x-show="activeTab === 'calendar'">
        @if($view === 'week')
            {{-- Week View --}}
            <div class="hidden sm:block bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700">
                    @php $dayDate = $startDate->copy(); @endphp
                    @for($i = 0; $i < 7; $i++)
                        @php $isToday = $dayDate->isToday(); @endphp
                        <div class="p-3 text-center border-r border-gray-200 dark:border-gray-700 last:border-r-0">
                            <p class="text-xs font-medium uppercase {{ $isToday ? 'text-primary-600 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400' }}">{{ $daysShort[$i] }}</p>
                            <div class="flex justify-center mt-1">
                                <span class="{{ $isToday ? 'w-8 h-8 flex items-center justify-center rounded-full bg-primary-600 text-white font-bold text-lg' : 'text-lg font-semibold text-gray-900 dark:text-white' }}">
                                    {{ $dayDate->format('d') }}
                                </span>
                            </div>
                        </div>
                        @php $dayDate->addDay(); @endphp
                    @endfor
                </div>

                <div class="grid grid-cols-7 sm:min-h-[400px]">
                    @php $dayDate = $startDate->copy(); @endphp
                    @for($i = 0; $i < 7; $i++)
                        @php
                            $dateKey = $dayDate->format('Y-m-d');
                            $dayEvents = $events->get($dateKey, collect());
                            $isToday = $dayDate->isToday();
                            $isPast = $dayDate->isPast() && !$isToday;
                        @endphp
                        <div class="border-r border-gray-200 dark:border-gray-700 last:border-r-0 p-2 {{ $isToday ? 'bg-primary-50/30 dark:bg-primary-900/10 ring-1 ring-inset ring-primary-200 dark:ring-primary-800' : '' }} {{ $isPast ? 'opacity-60' : '' }}">
                            <div class="space-y-2">
                                @foreach($dayEvents as $item)
                                    @if($item->type === 'meeting')
                                        <a href="{{ route('meetings.show', [$item->ministry_id, $item->id]) }}"
                                           class="block p-2 rounded-lg text-xs transition-all hover:shadow-md"
                                           style="background-color: {{ $item->ministry?->color ?? '#8b5cf6' }}30; border-left: 3px solid {{ $item->ministry?->color ?? '#8b5cf6' }};">
                                            <p class="font-medium text-gray-900 dark:text-white truncate">
                                                {{ $item->time ? \Carbon\Carbon::parse($item->time)->format('H:i') : '--:--' }}
                                            </p>
                                            <p class="text-gray-700 dark:text-gray-300 truncate">{{ $item->title }}</p>
                                            <div class="flex items-center mt-1">
                                                <span class="w-2 h-2 rounded-full bg-purple-500" title="{{ __('app.meeting') }}"></span>
                                            </div>
                                        </a>
                                    @else
                                        @php
                                            $isMultiDay = $item->is_multi_day ?? false;
                                            $isFirstDay = $item->is_first_day ?? true;
                                            $isLastDay = $item->is_last_day ?? true;
                                            $roundedClass = $isMultiDay ? ($isFirstDay ? 'rounded-l-lg rounded-r-none' : ($isLastDay ? 'rounded-r-lg rounded-l-none' : 'rounded-none')) : 'rounded-lg';
                                        @endphp
                                        <a href="{{ route('events.show', $item->original) }}"
                                           class="block p-2 {{ $roundedClass }} text-xs transition-all hover:shadow-md {{ $isMultiDay && !$isFirstDay ? '-ml-2 pl-4' : '' }}"
                                           style="background-color: {{ $item->ministry_display_color ?? '#3b82f6' }}30; border-left: {{ $isFirstDay ? '3px' : '0' }} solid {{ $item->ministry_display_color ?? '#3b82f6' }};">
                                            @if($isFirstDay)
                                                <p class="font-medium text-gray-900 dark:text-white truncate">{{ $item->time ? $item->time->format('H:i') : ($isMultiDay ? '' : __('app.all_day')) }}</p>
                                            @endif
                                            <p class="text-gray-700 dark:text-gray-300 truncate">{{ $item->title }}</p>
                                            @if($isFirstDay)
                                                <div class="flex items-center mt-1 gap-1">
                                                    @if($item->original->isFullyStaffed())
                                                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                                    @else
                                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                    @endif
                                                    @if($isMultiDay)
                                                        <span class="text-[10px] text-gray-500">{{ $item->original->date->format('d') }}-{{ $item->original->end_date->format('d.m') }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @php $dayDate->addDay(); @endphp
                    @endfor
                </div>
            </div>

            {{-- Week View - Mobile List --}}
            <div class="sm:hidden bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @php $dayDate = $startDate->copy(); @endphp
                    @for($i = 0; $i < 7; $i++)
                        @php
                            $dateKey = $dayDate->format('Y-m-d');
                            $dayEvents = $events->get($dateKey, collect());
                            $isToday = $dayDate->isToday();
                            $isPast = $dayDate->isPast() && !$isToday;
                        @endphp
                        @if($dayEvents->count() > 0)
                        <div class="p-4 {{ $isToday ? 'border-l-4 border-l-primary-500 bg-primary-50/50 dark:bg-primary-900/10' : '' }} {{ $isPast ? 'opacity-60' : '' }}">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="{{ $isToday ? 'w-8 h-8 flex items-center justify-center rounded-full bg-primary-600 text-white font-bold text-sm' : 'text-lg font-bold text-gray-900 dark:text-white' }}">{{ $dayDate->format('d') }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $daysShort[$i] }}@if($isToday) <span class="ml-1 text-primary-600 dark:text-primary-400 font-medium">Сьогодні</span>@endif</span>
                            </div>
                            <div class="space-y-2">
                                @foreach($dayEvents as $item)
                                    @if($item->type === 'meeting')
                                        <a href="{{ route('meetings.show', [$item->ministry_id, $item->id]) }}"
                                           class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $item->ministry?->color ?? '#8b5cf6' }}30;">
                                                    <svg class="w-5 h-5" style="color: {{ $item->ministry?->color ?? '#8b5cf6' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white">{{ $item->title }}</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->ministry?->name ?? '' }} &bull; {{ $item->time ? \Carbon\Carbon::parse($item->time)->format('H:i') : '-' }}</p>
                                                </div>
                                            </div>
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    @else
                                        <a href="{{ route('events.show', $item->original) }}"
                                           class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $item->ministry_display_color ?? '#3b82f6' }}30;">
                                                    <svg class="w-5 h-5" style="color: {{ $item->ministry_display_color ?? '#3b82f6' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white">{{ $item->title }}</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->ministry_display_name ?? __('app.no_ministry') }}@if($item->time) &bull; {{ $item->time->format('H:i') }}@endif</p>
                                                </div>
                                            </div>
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @php $dayDate->addDay(); @endphp
                    @endfor
                </div>
            </div>
        @else
            {{-- Month View - Calendar Grid --}}
            <div class="hidden sm:block bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                    @foreach($daysShort as $day)
                        <div class="p-3 text-center">
                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">{{ $day }}</p>
                        </div>
                    @endforeach
                </div>

                @php
                    $firstDayOfMonth = $startDate->copy()->startOfMonth();
                    $startDayOfWeek = $firstDayOfMonth->dayOfWeekIso - 1;
                    $calendarStart = $firstDayOfMonth->copy()->subDays($startDayOfWeek);
                    $calendarDate = $calendarStart->copy();
                @endphp

                <div class="grid grid-cols-7">
                    @for($week = 0; $week < 6; $week++)
                        @for($day = 0; $day < 7; $day++)
                            @php
                                $dateKey = $calendarDate->format('Y-m-d');
                                $dayEvents = $events->get($dateKey, collect());
                                $isToday = $calendarDate->isToday();
                                $isCurrentMonth = $calendarDate->month == $month;
                                $isPast = $calendarDate->isPast() && !$isToday;
                            @endphp
                            <div class="sm:min-h-[140px] lg:min-h-[160px] border-b border-r border-gray-200 dark:border-gray-700 p-1.5 lg:p-2 {{ !$isCurrentMonth ? 'bg-gray-100/70 dark:bg-gray-800/50' : '' }} {{ $isToday ? 'bg-primary-50/40 dark:bg-primary-900/10 ring-1 ring-inset ring-primary-300 dark:ring-primary-700' : '' }}">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium {{ $isToday ? 'w-7 h-7 flex items-center justify-center rounded-full bg-primary-600 text-white shadow-sm' : ($isCurrentMonth ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-600') }}">
                                        {{ $calendarDate->format('j') }}
                                    </span>
                                    @if($dayEvents->count() > 5)
                                        <span class="text-xs text-gray-500 dark:text-gray-500">+{{ $dayEvents->count() - 5 }}</span>
                                    @endif
                                </div>
                                <div class="space-y-0.5">
                                    @foreach($dayEvents->take(5) as $item)
                                        @if($item->type === 'meeting')
                                            <a href="{{ route('meetings.show', [$item->ministry_id, $item->id]) }}"
                                               class="block px-1.5 py-0.5 rounded text-xs truncate transition-colors hover:opacity-80 {{ $isPast && !$isToday ? 'opacity-60' : '' }}"
                                               style="background-color: {{ $item->ministry?->color ?? '#8b5cf6' }}30; color: {{ $item->ministry?->color ?? '#8b5cf6' }};">
                                                <span class="hidden lg:inline">{{ $item->time ? \Carbon\Carbon::parse($item->time)->format('H:i') : '' }}</span>
                                                📋 {{ Str::limit($item->title, 12) }}
                                            </a>
                                        @else
                                            <a href="{{ route('events.show', $item->original) }}"
                                               class="block px-1.5 py-0.5 rounded text-xs truncate transition-colors hover:opacity-80 {{ $isPast && !$isToday ? 'opacity-60' : '' }}"
                                               style="background-color: {{ $item->ministry_display_color ?? '#3b82f6' }}30; color: {{ $item->ministry_display_color ?? '#3b82f6' }};">
                                                <span class="hidden lg:inline">{{ $item->time ? $item->time->format('H:i') : '' }}</span>
                                                {{ Str::limit($item->title, 15) }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @php $calendarDate->addDay(); @endphp
                        @endfor
                        @if($calendarDate->month > $month && $calendarDate->year >= $year)
                            @break
                        @endif
                    @endfor
                </div>
            </div>

            {{-- Events List (below calendar) --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('app.month_events') }}</h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @php
                        $currentDate = $startDate->copy();
                        $endOfMonth = $startDate->copy()->endOfMonth();
                    @endphp

                    @while($currentDate <= $endOfMonth)
                        @php
                            $dateKey = $currentDate->format('Y-m-d');
                            $dayEvents = $events->get($dateKey, collect());
                            $isToday = $currentDate->isToday();
                            $isPast = $currentDate->isPast() && !$isToday;
                            $dayOfWeek = ['Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'][$currentDate->dayOfWeek];
                        @endphp

                        @if($dayEvents->count() > 0)
                            <div class="p-4 {{ $isToday ? 'border-l-4 border-l-primary-500 bg-primary-50/50 dark:bg-primary-900/10' : '' }} {{ $isPast ? 'opacity-60' : '' }}">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="{{ $isToday ? 'w-8 h-8 flex items-center justify-center rounded-full bg-primary-600 text-white font-bold text-sm' : 'text-lg font-bold text-gray-900 dark:text-white' }}">
                                        {{ $currentDate->format('d') }}
                                    </span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $dayOfWeek }}
                                        @if($isToday)
                                            <span class="ml-1 text-primary-600 dark:text-primary-400 font-medium">Сьогодні</span>
                                        @endif
                                    </span>
                                </div>

                                <div class="space-y-2">
                                    @foreach($dayEvents as $item)
                                        @if($item->type === 'meeting')
                                            <a href="{{ route('meetings.show', [$item->ministry_id, $item->id]) }}"
                                               class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $item->ministry?->color ?? '#8b5cf6' }}30;">
                                                        <svg class="w-5 h-5" style="color: {{ $item->ministry?->color ?? '#8b5cf6' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">{{ $item->title }}</p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->ministry?->name ?? '' }} &bull; {{ $item->time ? \Carbon\Carbon::parse($item->time)->format('H:i') : '-' }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300">
                                                        {{ __('app.meeting') }}
                                                    </span>
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </div>
                                            </a>
                                        @else
                                            <a href="{{ route('events.show', $item->original) }}"
                                               class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $item->ministry_display_color ?? '#3b82f6' }}30;">
                                                        <svg class="w-5 h-5" style="color: {{ $item->ministry_display_color ?? '#3b82f6' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">{{ $item->title }}</p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->ministry_display_name ?? __('app.no_ministry') }}@if($item->time) &bull; {{ $item->time->format('H:i') }}@endif</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    @if($item->original->isFullyStaffed())
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300">
                                                            {{ $item->original->confirmed_assignments_count }}/{{ $item->original->total_positions_count }}
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300">
                                                            {{ $item->original->filled_positions_count }}/{{ $item->original->total_positions_count }}
                                                        </span>
                                                    @endif
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </div>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @php $currentDate->addDay(); @endphp
                    @endwhile

                    @if($events->isEmpty())
                        <div class="p-12 text-center">
                            <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400">{{ __('app.no_events_this_month') }}</p>
                            @if(auth()->user()->can('create', \App\Models\Event::class))
                            <a href="{{ route('events.create') }}" class="mt-3 inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Створити подію
                            </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Upcoming Events from Next Month --}}
            @if($view === 'month' && isset($upcomingNextMonth) && $upcomingNextMonth->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-blue-50 dark:bg-blue-900/20">
                    <h3 class="font-semibold text-blue-700 dark:text-blue-300 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        Найближчі події ({{ $months[$nextMonth - 1] ?? __('app.next_month') }})
                    </h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($upcomingNextMonth as $item)
                        <a href="{{ route('events.show', $item->original) }}"
                           class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl flex flex-col items-center justify-center" style="background-color: {{ $item->ministry_display_color ?? '#3b82f6' }}30;">
                                    <span class="text-lg font-bold" style="color: {{ $item->ministry_display_color ?? '#3b82f6' }};">{{ $item->date->format('d') }}</span>
                                    <span class="text-xs" style="color: {{ $item->ministry_display_color ?? '#3b82f6' }};">{{ $months[$item->date->month - 1] ?? '' }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $item->title }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $item->ministry_display_name ?? __('app.no_ministry') }}
                                        @if($item->time)
                                            &bull; {{ $item->time->format('H:i') }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($item->original->isFullyStaffed())
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300">
                                        {{ $item->original->confirmed_assignments_count }}/{{ $item->original->total_positions_count }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300">
                                        {{ $item->original->filled_positions_count }}/{{ $item->original->total_positions_count }}
                                    </span>
                                @endif
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        @endif
    </div>

    {{-- ============================== --}}
    {{-- MATRIX TAB                     --}}
    {{-- ============================== --}}
    <div x-show="activeTab === 'matrix'" x-cloak>
        <div x-data="matrixView()" @load-matrix.window="if (events.length === 0 && !loading) loadData()">

            {{-- Matrix Filters (teleported to shared header) --}}
            <template x-teleport="#matrix-header-slot">
                <div class="flex flex-wrap items-center gap-2">
                    <select x-model="serviceType" @change="loadData()"
                            class="rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm py-2">
                        @foreach($serviceTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <select x-model="weeks" @change="loadData()"
                            class="rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm py-2">
                        <option value="4">4 {{ __('тижні') }}</option>
                        <option value="8">8 {{ __('тижнів') }}</option>
                        <option value="12">12 {{ __('тижнів') }}</option>
                    </select>

                    <div class="flex items-center gap-0.5">
                        <button @click="prevPeriod()" type="button"
                            class="w-9 h-9 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button @click="nextPeriod()" type="button"
                            class="w-9 h-9 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                    <span class="text-sm sm:text-base font-semibold text-gray-700 dark:text-gray-300" x-text="periodLabel"></span>
                </div>
            </template>

            {{-- Loading --}}
            <div x-show="loading" class="flex items-center justify-center py-12">
                <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>

            {{-- Empty State --}}
            <template x-if="!loading && events.length === 0">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-lg">{{ __('Немає подій за цей період') }}</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">{{ __('Спробуйте обрати інший тип служіння або період') }}</p>
                </div>
            </template>

            {{-- Empty Ministries --}}
            <template x-if="!loading && events.length > 0 && ministries.length === 0">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <p class="text-gray-500 dark:text-gray-400 text-lg">{{ __('Немає команд для відображення') }}</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">{{ __('Позначте команди як "Частина недільного служіння" в налаштуваннях') }}</p>
                </div>
            </template>

            {{-- Matrix Grid --}}
            <template x-if="!loading && events.length > 0 && ministries.length > 0">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <th class="sticky left-0 z-20 bg-gray-50 dark:bg-gray-700 px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-r border-gray-200 dark:border-gray-600 w-[160px] sm:w-[200px] min-w-[160px] sm:min-w-[200px]">
                                        {{ __('Команда / Роль') }}
                                    </th>
                                    <template x-for="event in events" :key="event.id">
                                        <th class="px-2 py-3 text-center border-b border-gray-200 dark:border-gray-600 min-w-[140px]"
                                            :class="isNearestEvent(event) ? 'bg-primary-50 dark:bg-primary-900/30' : 'bg-gray-50 dark:bg-gray-700'">
                                            <a :href="'/events/' + event.id"
                                               class="block hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                                <div class="text-[10px] font-medium uppercase tracking-wide"
                                                     :class="isNearestEvent(event) ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500'"
                                                     x-text="event.dayOfWeek"></div>
                                                <div class="text-sm font-bold"
                                                     :class="isNearestEvent(event) ? 'text-primary-700 dark:text-primary-300' : 'text-gray-900 dark:text-white'"
                                                     x-text="event.dateLabel"></div>
                                                <template x-if="event.time">
                                                    <div class="text-[10px]"
                                                         :class="isNearestEvent(event) ? 'text-primary-500 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500'"
                                                         x-text="event.time"></div>
                                                </template>
                                            </a>
                                        </th>
                                    </template>
                                </tr>
                            </thead>

                            <tbody>
                                <template x-for="(ministry, mIdx) in ministries" :key="ministry.id">
                                    <template x-for="(role, roleIdx) in ministry.roles" :key="role.type + '_' + role.id">
                                        <tr class="border-b border-gray-100 dark:border-gray-700/50 group/row hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors"
                                            :class="roleIdx === 0 ? (mIdx > 0 ? 'border-t-2 border-gray-200 dark:border-gray-600' : 'border-t border-gray-200 dark:border-gray-600') : ''">
                                            <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 group-hover/row:bg-gray-50 dark:group-hover/row:bg-gray-700 px-3 sm:px-4 border-r border-gray-200 dark:border-gray-600 transition-colors"
                                                :class="roleIdx === 0 ? 'pt-3 pb-2.5' : 'py-2.5'">
                                                <template x-if="roleIdx === 0">
                                                    <div class="flex items-center gap-2 mb-1.5 pb-1 border-b"
                                                         :style="'border-color:' + (ministry.color || '#6B7280') + '40'">
                                                        <span class="w-1 h-4 rounded-full flex-shrink-0" :style="'background:' + (ministry.color || '#6B7280')"></span>
                                                        <span class="text-[11px] font-bold uppercase tracking-wide"
                                                              :style="'color:' + (ministry.color || '#6B7280')"
                                                              x-text="(ministry.icon ? ministry.icon + ' ' : '') + ministry.name"></span>
                                                    </div>
                                                </template>
                                                <div class="flex items-center gap-2">
                                                    <template x-if="role.icon">
                                                        <span class="text-sm flex-shrink-0" x-text="role.icon"></span>
                                                    </template>
                                                    <template x-if="!role.icon">
                                                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 bg-gray-300 dark:bg-gray-600"></span>
                                                    </template>
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="role.name"></span>
                                                </div>
                                            </td>

                                            <template x-for="event in events" :key="event.id">
                                                <td class="px-1.5 py-1.5 text-center border-l border-gray-100 dark:border-gray-700/50 cursor-pointer"
                                                    :class="isNearestEvent(event) ? 'bg-primary-50/30 dark:bg-primary-900/10' : ''"
                                                    @click="openCellDropdown(ministry, role, event, $event)">
                                                    <div class="min-h-[40px] flex flex-col items-center justify-center gap-0.5 rounded-lg px-1 py-1 transition-all duration-150"
                                                         :class="getCellClasses(ministry.id, role, event.id)">
                                                        <template x-for="person in getCellPersons(ministry.id, role, event.id)" :key="person.id">
                                                            <div class="flex items-center gap-1 text-xs leading-tight w-full justify-center">
                                                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                                                      :class="statusDotClass(person.status)"></span>
                                                                <span class="truncate max-w-[110px] font-medium" x-text="person.person_name"
                                                                      :class="statusTextClass(person.status)"></span>
                                                            </div>
                                                        </template>
                                                        <template x-if="getCellNotes(ministry.id, role, event.id)">
                                                            <div class="text-[10px] text-amber-500 dark:text-amber-400 truncate max-w-[120px] mt-0.5" :title="getCellNotes(ministry.id, role, event.id)" x-text="'💬 ' + getCellNotes(ministry.id, role, event.id)"></div>
                                                        </template>
                                                        <template x-if="getCellPersons(ministry.id, role, event.id).length === 0">
                                                            <div class="flex items-center justify-center w-full h-full">
                                                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover/row:text-primary-400 dark:group-hover/row:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                                </svg>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </td>
                                            </template>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Legend --}}
                    <div class="px-4 py-2.5 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/25">
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span> {{ __('Підтверджено') }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span> {{ __('Очікує') }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-red-500"></span> {{ __('Відхилено') }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-gray-400"></span> {{ __('Не підтверджено') }}
                            </span>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Toast notification --}}
            <div x-show="toast.show" x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
                 class="fixed bottom-6 right-6 z-50 px-4 py-2.5 rounded-xl shadow-lg text-sm font-medium"
                 :class="toast.type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'">
                <span x-text="toast.message"></span>
            </div>

            {{-- Assign/Action Dropdown --}}
            <div x-show="dropdown.open" x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 @click.outside="dropdown.open = false"
                 @keydown.escape.window="dropdown.open = false"
                 class="fixed z-50 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 w-72 overflow-hidden"
                 :style="'top:' + dropdown.y + 'px;left:' + dropdown.x + 'px'">

                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-gray-900 dark:text-white truncate"
                                 x-text="dropdown.role?.icon ? dropdown.role.icon + ' ' + dropdown.role?.name : dropdown.role?.name"></div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400"
                                 x-text="dropdown.event?.dayOfWeek + ' ' + dropdown.event?.dateLabel + (dropdown.event?.time ? ', ' + dropdown.event?.time : '')"></div>
                        </div>
                        <button @click="dropdown.open = false"
                            class="p-1 -mr-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <template x-if="dropdown.persons.length > 0">
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <template x-for="person in dropdown.persons" :key="person.id">
                            <div class="flex items-center justify-between px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2 h-2 rounded-full flex-shrink-0" :class="statusDotClass(person.status)"></span>
                                    <span class="text-sm text-gray-900 dark:text-white truncate" x-text="person.person_name"></span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full flex-shrink-0"
                                          :class="statusBadgeClass(person.status)"
                                          x-text="statusLabel(person.status)"></span>
                                </div>
                                <div class="flex items-center gap-0.5 flex-shrink-0 ml-2">
                                    <template x-if="person.has_telegram && person.source !== 'assignment'">
                                        <button @click.stop="notifyPerson(person)"
                                            class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                            :title="'{{ __('Надіслати в Telegram') }}'">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                                            </svg>
                                        </button>
                                    </template>
                                    <button @click.stop="removePerson(person)"
                                        class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20"
                                        :title="'{{ __('Видалити') }}'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-1.5 mb-1">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">{{ __('Примітка до позиції') }}</span>
                    </div>
                    <input type="text" :value="dropdown.cellNotes || ''"
                           @input.debounce.600ms="saveCellNotes($event.target.value)"
                           placeholder="{{ __('Примітка...') }}"
                           class="w-full px-2 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400 dark:placeholder-gray-500">
                </div>

                <div class="p-2">
                    <input type="text" x-model="dropdown.search" x-ref="dropdownSearch"
                           placeholder="{{ __('Пошук учасника...') }}"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400 dark:placeholder-gray-500"
                           @keydown.escape="dropdown.open = false">
                </div>
                <div class="overflow-y-auto max-h-44 pb-1">
                    <template x-for="member in filteredMembers()" :key="member.id">
                        <button @click="assignPerson(member)"
                            class="w-full text-left px-3 py-2 text-sm hover:bg-primary-50 dark:hover:bg-primary-900/30 text-gray-700 dark:text-gray-300 transition-colors flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="truncate" x-text="member.name"></span>
                        </button>
                    </template>
                    <template x-if="filteredMembers().length === 0 && dropdown.search">
                        <div class="px-3 py-3 text-sm text-gray-400 dark:text-gray-500 text-center">
                            {{ __('Нікого не знайдено') }}
                        </div>
                    </template>
                    <template x-if="filteredMembers().length === 0 && !dropdown.search && dropdown.persons.length > 0">
                        <div class="px-3 py-3 text-sm text-gray-400 dark:text-gray-500 text-center">
                            {{ __('Всі учасники призначені') }}
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Subscription Modal --}}
<div id="subscriptionModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="hideSubscriptionModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.calendar_subscription') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('app.add_to_calendars') }}</p>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('app.ical_url') }}
                    </label>
                    <div class="flex gap-2">
                        <input type="text" id="icalUrl" readonly
                               value="{{ $church->calendar_feed_url }}"
                               class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white">
                        <button onclick="copyIcalUrl(event)" class="px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors">
                            Копіювати
                        </button>
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">{{ __('app.how_to_subscribe') }}</h4>
                    <div class="text-sm text-blue-700 dark:text-blue-400 space-y-2">
                        <details class="cursor-pointer">
                            <summary class="font-medium">Google Calendar</summary>
                            <ol class="mt-2 ml-4 list-decimal space-y-1">
                                <li>{{ __('app.open_google_calendar') }}</li>
                                <li>{{ __('app.click_plus_other') }}</li>
                                <li>{{ __('app.select_from_url') }}</li>
                                <li>{{ __('app.paste_url') }}</li>
                                <li>{{ __('app.click_add_calendar') }}</li>
                            </ol>
                        </details>
                        <details class="cursor-pointer">
                            <summary class="font-medium">{{ __('app.apple_calendar') }}</summary>
                            <ol class="mt-2 ml-4 list-decimal space-y-1">
                                <li>{{ __('app.open_settings_calendar') }}</li>
                                <li>{{ __('app.select_accounts_add') }}</li>
                                <li>{{ __('app.select_other_subscription') }}</li>
                                <li>{{ __('app.paste_url') }}</li>
                            </ol>
                        </details>
                        <details class="cursor-pointer">
                            <summary class="font-medium">{{ __('app.outlook_calendar') }}</summary>
                            <ol class="mt-2 ml-4 list-decimal space-y-1">
                                <li>{{ __('app.open_outlook') }}</li>
                                <li>{{ __('app.select_add_from_internet') }}</li>
                                <li>{{ __('app.paste_url') }}</li>
                            </ol>
                        </details>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="https://calendar.google.com/calendar/r?cid={{ urlencode($church->calendar_feed_url) }}" target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19.5 3h-15A1.5 1.5 0 003 4.5v15A1.5 1.5 0 004.5 21h15a1.5 1.5 0 001.5-1.5v-15A1.5 1.5 0 0019.5 3z"/>
                        </svg>
                        Google Calendar
                    </a>
                </div>

                <details class="text-sm text-gray-500 dark:text-gray-400">
                    <summary class="cursor-pointer font-medium">{{ __('app.api_for_developers') }}</summary>
                    <div class="mt-2 space-y-2 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                        <p><strong>JSON API:</strong></p>
                        <code class="block text-xs bg-gray-100 dark:bg-gray-600 p-2 rounded overflow-x-auto">
                            GET {{ url('/api/calendar/events') }}?token={{ $church->getCalendarToken() }}
                        </code>
                        <p class="text-xs mt-2">Параметри: <code>start</code>, <code>end</code>, <code>ministry</code></p>
                    </div>
                </details>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                <button onclick="hideSubscriptionModal()" class="w-full px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-800 dark:text-white rounded-lg font-medium transition-colors">
                    Закрити
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showSubscriptionModal() {
    document.getElementById('subscriptionModal').classList.remove('hidden');
}

function hideSubscriptionModal() {
    document.getElementById('subscriptionModal').classList.add('hidden');
}

function copyIcalUrl(e) {
    const input = document.getElementById('icalUrl');
    input.select();
    const btn = e ? e.target : null;
    navigator.clipboard.writeText(input.value).then(() => {
        if (btn) {
            const originalText = btn.textContent;
            btn.textContent = '{{ __('app.copied') }}';
            setTimeout(() => btn.textContent = originalText, 2000);
        }
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideSubscriptionModal();
    }
});

// Calendar Navigator
function calendarNavigator(initialState) {
    const months = ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];

    return {
        currentView: initialState.view,
        currentYear: initialState.year,
        currentMonth: initialState.month,
        currentWeek: initialState.week,
        loading: false,

        get monthDisplay() {
            return `${months[this.currentMonth - 1]} ${this.currentYear}`;
        },

        get weekDisplay() {
            const d = new Date(this.currentYear, 0, 1);
            const weekStart = new Date(d.setDate(d.getDate() + (this.currentWeek - 1) * 7));
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekEnd.getDate() + 6);
            return `${String(weekStart.getDate()).padStart(2, '0')}.${String(weekStart.getMonth() + 1).padStart(2, '0')} - ${String(weekEnd.getDate()).padStart(2, '0')}.${String(weekEnd.getMonth() + 1).padStart(2, '0')}.${weekEnd.getFullYear()}`;
        },

        switchView(view) {
            this.currentView = view;
            if (view === 'week') {
                this.currentWeek = new Date().getWeek();
            }
            this.loadCalendar();
        },

        prevPeriod() {
            if (this.activeTab !== 'calendar') return;
            if (this.currentView === 'week') {
                this.currentWeek = this.currentWeek === 1 ? 52 : this.currentWeek - 1;
                if (this.currentWeek === 52) this.currentYear--;
            } else {
                this.currentMonth = this.currentMonth === 1 ? 12 : this.currentMonth - 1;
                if (this.currentMonth === 12) this.currentYear--;
            }
            this.loadCalendar();
        },

        nextPeriod() {
            if (this.activeTab !== 'calendar') return;
            if (this.currentView === 'week') {
                this.currentWeek = this.currentWeek === 52 ? 1 : this.currentWeek + 1;
                if (this.currentWeek === 1) this.currentYear++;
            } else {
                this.currentMonth = this.currentMonth === 12 ? 1 : this.currentMonth + 1;
                if (this.currentMonth === 1) this.currentYear++;
            }
            this.loadCalendar();
        },

        goToday() {
            const today = new Date();
            this.currentYear = today.getFullYear();
            this.currentMonth = today.getMonth() + 1;
            this.currentWeek = today.getWeek();
            this.currentView = 'month';
            this.loadCalendar();
        },

        loadCalendar() {
            const params = new URLSearchParams({
                view: this.currentView,
                year: this.currentYear,
                tab: this.activeTab || 'calendar',
                ...(this.currentView === 'week' ? { week: this.currentWeek } : { month: this.currentMonth })
            });

            window.location.href = `{{ route('schedule') }}?${params}`;
        }
    };
}

// Helper function to get week number
Date.prototype.getWeek = function() {
    const d = new Date(Date.UTC(this.getFullYear(), this.getMonth(), this.getDate()));
    const dayNum = d.getUTCDay() || 7;
    d.setUTCDate(d.getUTCDate() + 4 - dayNum);
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
    return Math.ceil((((d - yearStart) / 86400000) + 1)/7);
};

// Matrix View
function matrixView() {
    return {
        loading: false,
        serviceType: 'sunday_service',
        weeks: 4,
        startDate: null,
        events: [],
        ministries: [],
        grid: {},
        members: {},
        periodLabel: '',
        nearestEventId: null,

        dropdown: {
            open: false,
            x: 0,
            y: 0,
            ministry: null,
            role: null,
            event: null,
            persons: [],
            search: '',
            cellNotes: '',
        },

        toast: {
            show: false,
            message: '',
            type: 'success',
            timer: null,
        },

        busy: false,

        init() {
            const now = new Date();
            const day = now.getDay();
            const diff = now.getDate() - day + (day === 0 ? -6 : 1);
            this.startDate = new Date(now.setDate(diff));
            this.startDate.setHours(0, 0, 0, 0);
        },

        formatDate(d) {
            return d.getFullYear() + '-' +
                String(d.getMonth() + 1).padStart(2, '0') + '-' +
                String(d.getDate()).padStart(2, '0');
        },

        updatePeriodLabel() {
            const months = ['січ', 'лют', 'бер', 'кві', 'тра', 'чер', 'лип', 'сер', 'вер', 'жов', 'лис', 'гру'];
            const end = new Date(this.startDate);
            end.setDate(end.getDate() + this.weeks * 7 - 1);

            const startLabel = this.startDate.getDate() + ' ' + months[this.startDate.getMonth()];
            const endLabel = end.getDate() + ' ' + months[end.getMonth()];

            this.periodLabel = this.startDate.getFullYear() === end.getFullYear()
                ? startLabel + ' — ' + endLabel + ', ' + this.startDate.getFullYear()
                : startLabel + ' ' + this.startDate.getFullYear() + ' — ' + endLabel + ' ' + end.getFullYear();
        },

        prevPeriod() {
            this.startDate.setDate(this.startDate.getDate() - this.weeks * 7);
            this.startDate = new Date(this.startDate);
            this.loadData();
        },

        nextPeriod() {
            this.startDate.setDate(this.startDate.getDate() + this.weeks * 7);
            this.startDate = new Date(this.startDate);
            this.loadData();
        },

        goToday() {
            const now = new Date();
            const day = now.getDay();
            const diff = now.getDate() - day + (day === 0 ? -6 : 1);
            this.startDate = new Date(now.setDate(diff));
            this.startDate.setHours(0, 0, 0, 0);
            this.loadData();
        },

        findNearestEvent() {
            const today = this.formatDate(new Date());
            let nearest = null;
            for (const event of this.events) {
                if (event.date >= today) {
                    nearest = event.id;
                    break;
                }
            }
            this.nearestEventId = nearest || (this.events.length > 0 ? this.events[this.events.length - 1].id : null);
        },

        isNearestEvent(event) {
            return event.id === this.nearestEventId;
        },

        async loadData() {
            this.loading = true;
            this.dropdown.open = false;
            this.updatePeriodLabel();

            try {
                const params = new URLSearchParams({
                    service_type: this.serviceType,
                    weeks: this.weeks,
                    start_date: this.formatDate(this.startDate),
                });

                const resp = await fetch(`{{ route('schedule.matrix-data') }}?${params}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!resp.ok) throw new Error('Failed to load');

                const data = await resp.json();
                this.events = data.events;
                this.ministries = data.ministriesData;
                this.grid = data.grid;
                this.members = data.members;
                this.findNearestEvent();
            } catch (e) {
                console.error('Matrix load error:', e);
                this.showToast('Помилка завантаження', 'error');
            } finally {
                this.loading = false;
            }
        },

        getRoleKey(role) {
            return role.type + '_' + role.id;
        },

        getCellPersons(ministryId, role, eventId) {
            const mKey = String(ministryId);
            const rKey = this.getRoleKey(role);
            const eKey = String(eventId);
            return this.grid?.[mKey]?.[rKey]?.[eKey] || [];
        },

        getCellNotes(ministryId, role, eventId) {
            const persons = this.getCellPersons(ministryId, role, eventId);
            for (const p of persons) {
                if (p.notes) return p.notes;
            }
            return null;
        },

        getCellClasses(ministryId, role, eventId) {
            const persons = this.getCellPersons(ministryId, role, eventId);
            if (persons.length === 0) {
                return 'border border-dashed border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50/50 dark:hover:bg-primary-900/20';
            }
            const hasDeclined = persons.some(p => p.status === 'declined');
            if (hasDeclined) return 'bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 ring-1 ring-red-200 dark:ring-red-800';
            const allConfirmed = persons.every(p => p.status === 'confirmed' || p.status === 'attended');
            if (allConfirmed) return 'bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30';
            return 'bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/30';
        },

        statusDotClass(status) {
            switch(status) {
                case 'confirmed': return 'bg-green-500';
                case 'pending': return 'bg-amber-500';
                case 'declined': return 'bg-red-500';
                case 'attended': return 'bg-blue-500';
                default: return 'bg-gray-400';
            }
        },

        statusTextClass(status) {
            switch(status) {
                case 'confirmed': return 'text-green-700 dark:text-green-400';
                case 'pending': return 'text-amber-700 dark:text-amber-300';
                case 'declined': return 'text-red-500 dark:text-red-400 line-through';
                case 'attended': return 'text-blue-700 dark:text-blue-400';
                default: return 'text-gray-700 dark:text-gray-300';
            }
        },

        statusBadgeClass(status) {
            switch(status) {
                case 'confirmed': return 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400';
                case 'pending': return 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400';
                case 'declined': return 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400';
                case 'attended': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400';
                default: return 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400';
            }
        },

        statusLabel(status) {
            const labels = {
                confirmed: '{{ __("Так") }}',
                pending: '{{ __("Очікує") }}',
                declined: '{{ __("Ні") }}',
                attended: '{{ __("Був") }}',
            };
            return labels[status] || '—';
        },

        openCellDropdown(ministry, role, event, $event) {
            const rect = $event.currentTarget.getBoundingClientRect();
            const dropdownWidth = 288;
            const dropdownHeight = 360;

            let x = rect.left + (rect.width / 2) - (dropdownWidth / 2);
            let y = rect.bottom + 6;

            if (x + dropdownWidth > window.innerWidth - 8) x = window.innerWidth - dropdownWidth - 8;
            if (y + dropdownHeight > window.innerHeight) y = rect.top - dropdownHeight - 6;
            if (x < 8) x = 8;
            if (y < 8) y = 8;

            this.dropdown.x = x;
            this.dropdown.y = y;
            this.dropdown.ministry = ministry;
            this.dropdown.role = role;
            this.dropdown.event = event;
            this.dropdown.persons = this.getCellPersons(ministry.id, role, event.id);
            this.dropdown.search = '';
            this.dropdown.cellNotes = this.getCellNotes(ministry.id, role, event.id) || '';
            this.dropdown.open = true;

            this.$nextTick(() => {
                this.$refs.dropdownSearch?.focus();
            });
        },

        filteredMembers() {
            if (!this.dropdown.ministry) return [];
            const mKey = String(this.dropdown.ministry.id);
            const allMembers = this.members[mKey] || [];
            const assignedIds = this.dropdown.persons.map(p => p.person_id);
            const search = this.dropdown.search.toLowerCase();

            return allMembers.filter(m => {
                if (assignedIds.includes(m.id)) return false;
                if (search && !m.name.toLowerCase().includes(search)) return false;
                return true;
            });
        },

        showToast(message, type = 'success') {
            if (this.toast.timer) clearTimeout(this.toast.timer);
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            this.toast.timer = setTimeout(() => { this.toast.show = false; }, 2000);
        },

        async assignPerson(member) {
            const { ministry, role, event } = this.dropdown;
            if (!ministry || !role || !event || this.busy) return;
            this.busy = true;

            const mKey = String(ministry.id);
            const rKey = this.getRoleKey(role);
            const eKey = String(event.id);

            try {
                let url, body;

                if (role.type === 'ministry_role') {
                    url = `/events/${event.id}/ministry-team`;
                    body = {
                        ministry_id: ministry.id,
                        person_id: member.id,
                        ministry_role_id: role.id,
                    };
                } else {
                    url = `/rotation/event/${event.id}/assign-position`;
                    body = {
                        position_id: role.id,
                        person_id: member.id,
                    };
                }

                const resp = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(body),
                });

                if (resp.ok) {
                    const data = await resp.json();
                    if (!this.grid[mKey]) this.grid[mKey] = {};
                    if (!this.grid[mKey][rKey]) this.grid[mKey][rKey] = {};
                    if (!this.grid[mKey][rKey][eKey]) this.grid[mKey][rKey][eKey] = [];

                    this.grid[mKey][rKey][eKey].push({
                        id: data.id,
                        person_id: member.id,
                        person_name: member.short_name || member.name,
                        status: data.status || 'pending',
                        has_telegram: member.has_telegram,
                        source: role.type === 'ministry_role' ? 'ministry_team' : 'assignment',
                        notes: null,
                    });

                    this.dropdown.persons = this.grid[mKey][rKey][eKey];
                    this.showToast((member.short_name || member.name) + ' — ' + '{{ __("призначено") }}');

                    if (this.filteredMembers().length === 0) {
                        setTimeout(() => { this.dropdown.open = false; }, 600);
                    }
                } else {
                    const err = await resp.json().catch(() => ({}));
                    this.showToast(err.error || err.message || '{{ __("Помилка при призначенні") }}', 'error');
                }
            } catch (e) {
                console.error('Assign error:', e);
                this.showToast('{{ __("Помилка при призначенні") }}', 'error');
            } finally {
                this.busy = false;
            }
        },

        async removePerson(person) {
            const { ministry, role, event } = this.dropdown;
            if (!ministry || !role || !event || this.busy) return;
            this.busy = true;

            const mKey = String(ministry.id);
            const rKey = this.getRoleKey(role);
            const eKey = String(event.id);

            try {
                let url;
                if (person.source === 'assignment') {
                    url = `/rotation/assignment/${person.id}`;
                } else {
                    url = `/events/${event.id}/ministry-team/${person.id}`;
                }

                const resp = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (resp.ok) {
                    if (this.grid[mKey]?.[rKey]?.[eKey]) {
                        this.grid[mKey][rKey][eKey] = this.grid[mKey][rKey][eKey].filter(p => p.id !== person.id);
                    }
                    this.dropdown.persons = this.grid[mKey]?.[rKey]?.[eKey] || [];
                    this.showToast(person.person_name + ' — ' + '{{ __("видалено") }}');

                    if (this.dropdown.persons.length === 0) {
                        setTimeout(() => { this.dropdown.open = false; }, 400);
                    }
                }
            } catch (e) {
                console.error('Remove error:', e);
                this.showToast('{{ __("Помилка") }}', 'error');
            } finally {
                this.busy = false;
            }
        },

        async saveCellNotes(value) {
            const { ministry, role, event, persons } = this.dropdown;
            if (!ministry || !role || !event || persons.length === 0) return;

            const notes = value.trim() || null;
            this.dropdown.cellNotes = notes || '';

            const mKey = String(ministry.id);
            const rKey = this.getRoleKey(role);
            const eKey = String(event.id);

            try {
                const promises = persons.map(person => {
                    const url = person.source === 'assignment'
                        ? `/rotation/assignment/${person.id}/notes`
                        : `/events/${event.id}/ministry-team/${person.id}/notes`;

                    return fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ notes }),
                    });
                });

                await Promise.all(promises);

                if (this.grid[mKey]?.[rKey]?.[eKey]) {
                    this.grid[mKey][rKey][eKey].forEach(p => p.notes = notes);
                }
                this.showToast('{{ __("Примітку збережено") }}');
            } catch (e) {
                console.error('Save cell notes error:', e);
                this.showToast('{{ __("Помилка") }}', 'error');
            }
        },

        async notifyPerson(person) {
            const { event } = this.dropdown;
            if (!event) return;

            try {
                const url = `/events/${event.id}/ministry-team/${person.id}/notify`;
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await resp.json();
                if (data.success) {
                    const mKey = String(this.dropdown.ministry.id);
                    const rKey = this.getRoleKey(this.dropdown.role);
                    const eKey = String(event.id);
                    if (this.grid[mKey]?.[rKey]?.[eKey]) {
                        const p = this.grid[mKey][rKey][eKey].find(p => p.id === person.id);
                        if (p) p.status = 'pending';
                    }
                    this.dropdown.persons = [...(this.grid[mKey]?.[rKey]?.[eKey] || [])];
                    this.showToast('{{ __("Повідомлення надіслано") }}');
                } else {
                    this.showToast(data.message || '{{ __("Не вдалося надіслати") }}', 'error');
                }
            } catch (e) {
                console.error('Notify error:', e);
                this.showToast('{{ __("Помилка") }}', 'error');
            }
        },
    };
}
</script>
@endsection
