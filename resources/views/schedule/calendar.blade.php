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

    $prevWeek = $currentWeek ? ($currentWeek == 1 ? 52 : $currentWeek - 1) : null;
    $prevWeekYear = $currentWeek == 1 ? $year - 1 : $year;
    $nextWeek = $currentWeek ? ($currentWeek == 52 ? 1 : $currentWeek + 1) : null;
    $nextWeekYear = $currentWeek == 52 ? $year + 1 : $year;
@endphp

<div class="space-y-4" x-data="calendarNavigator({{ json_encode(['view' => $view, 'year' => $year, 'month' => $month, 'week' => $currentWeek ?? null]) }})">
    <!-- View Toggle & Navigation -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- View Toggle -->
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

            <!-- Date Navigation with Month Picker -->
            <div class="flex items-center justify-between sm:justify-center gap-2 sm:gap-4">
                <button @click="prevPeriod()" type="button"
                   class="w-11 h-11 sm:w-10 sm:h-10 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 dark:active:bg-gray-600 rounded-xl transition-colors">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <!-- Month/Week Picker Button -->
                <div x-data="{ showPicker: false }" class="relative">
                    <button @click="showPicker = !showPicker" type="button"
                       class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white min-w-[140px] sm:min-w-[200px] text-center px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors cursor-pointer">
                        <span x-show="currentView === 'week'" x-text="weekDisplay"></span>
                        <span x-show="currentView === 'month'" x-text="monthDisplay"></span>
                        <svg class="w-4 h-4 inline-block ml-2 transition-transform" :class="showPicker ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                    </button>

                    <!-- Calendar Picker Dropdown -->
                    <div x-show="showPicker" x-transition @click.outside="showPicker = false"
                         class="absolute top-full mt-2 left-1/2 -translate-x-1/2 z-50 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4">
                        <div x-show="currentView === 'month'" class="space-y-3">
                            <!-- Year Navigation -->
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

                            <!-- Month Grid -->
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
                            <!-- Week Navigation -->
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

                            <!-- Month selector for weeks -->
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <template x-for="(month, index) in ['Січ', 'Лют', 'Бер', 'Кві', 'Тра', 'Чер', 'Лип', 'Сер', 'Вер', 'Жов', 'Лис', 'Гру']" :key="index">
                                    <button @click="currentMonth = index + 1" type="button"
                                       :class="currentMonth === index + 1 ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600'"
                                       class="p-2 rounded-lg font-medium text-xs transition-colors">
                                        <span x-text="month"></span>
                                    </button>
                                </template>
                            </div>

                            <!-- Week Grid (1-52) -->
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

            <!-- Actions -->
            <div class="flex items-center gap-2">
                <button @click="goToday()" type="button"
                   class="px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors">
                    Сьогодні
                </button>

                <!-- Google Calendar Sync Status -->
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

                <!-- Export/Import Dropdown -->
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

    @if($view === 'week')
        <!-- Week View -->
        <div class="hidden sm:block bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Days Header -->
            <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700">
                @php $dayDate = $startDate->copy(); @endphp
                @for($i = 0; $i < 7; $i++)
                    @php
                        $isToday = $dayDate->isToday();
                    @endphp
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

            <!-- Events Grid -->
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

        <!-- Week View - Mobile List -->
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
        <!-- Month View - Calendar Grid -->
        <div class="hidden sm:block bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Days Header -->
            <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                @foreach($daysShort as $day)
                    <div class="p-3 text-center">
                        <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">{{ $day }}</p>
                    </div>
                @endforeach
            </div>

            <!-- Calendar Grid -->
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

        <!-- Events List (below calendar) -->
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

        <!-- Upcoming Events from Next Month -->
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

<!-- Subscription Modal -->
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
                <!-- iCal Feed URL -->
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

                <!-- Instructions -->
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

                <!-- Direct Add Links -->
                <div class="flex flex-wrap gap-2">
                    <a href="https://calendar.google.com/calendar/r?cid={{ urlencode($church->calendar_feed_url) }}" target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19.5 3h-15A1.5 1.5 0 003 4.5v15A1.5 1.5 0 004.5 21h15a1.5 1.5 0 001.5-1.5v-15A1.5 1.5 0 0019.5 3z"/>
                        </svg>
                        Google Calendar
                    </a>
                </div>

                <!-- API Info -->
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

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideSubscriptionModal();
    }
});

// Calendar Navigator - AJAX switching between week/month views
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
            // Calculate week dates (simplified)
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
</script>
@endsection
