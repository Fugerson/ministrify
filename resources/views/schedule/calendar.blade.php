@extends('layouts.app')

@section('title', __('app.schedule_title'))

@section('actions')
@if(auth()->user()->can('create', \App\Models\Event::class))
<button onclick="document.getElementById('createEventModal').classList.remove('hidden')"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    {{ __('app.schedule_new_event') }}
</button>
@endif
@endsection

@section('content')
@if(($tab ?? 'calendar') === 'planning')
    {{-- Tab Header for Planning --}}
    <div class="space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-2">
                <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-xl p-1">
                    <a href="{{ route('schedule') }}"
                       class="px-4 py-2 text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        {{ __('app.calendar') }}
                    </a>
                    <span class="px-4 py-2 text-sm font-medium rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow">
                        {{ __('app.service_planning') }}
                    </span>
                </div>
            </div>
        </div>

        @include('service-planning._matrix')
    </div>
@else
@php
    $months = explode(',', __('app.cal_months'));
    $daysShort = explode(',', __('app.cal_days_short'));
    $daysFull = explode(',', __('app.cal_days_full'));

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
        ...calendarNavigator({{ json_encode(['view' => $view, 'year' => $year, 'month' => $month, 'week' => $currentWeek ?? null]) }})
     }">

    {{-- Shared Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            {{-- Tab Toggle + View Controls --}}
            <div class="flex items-center gap-3">
                {{-- Calendar/Planning tab toggle --}}
                <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-xl p-1">
                    <span class="px-4 py-2 text-sm font-medium rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow">
                        {{ __('app.calendar') }}
                    </span>
                    <a href="{{ route('schedule', ['tab' => 'planning']) }}"
                       class="px-4 py-2 text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        {{ __('app.service_planning') }}
                    </a>
                </div>

                {{-- Calendar: Week/Month toggle --}}
                <div class="flex items-center gap-3">
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

            </div>

            {{-- Date Navigation --}}
            <div class="flex items-center justify-between sm:justify-center gap-2 sm:gap-4">
                    <div class="flex items-center gap-2 sm:gap-4">
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
                                 class="absolute top-full mt-2 left-1/2 -translate-x-1/2 z-50 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 min-w-[280px]">
                                {{-- Year Navigation --}}
                                <div class="flex items-center justify-between mb-4">
                                    <button @click="currentYear--" type="button"
                                       class="w-9 h-9 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </button>
                                    <span class="text-lg font-bold text-gray-900 dark:text-white" x-text="currentYear"></span>
                                    <button @click="currentYear++" type="button"
                                       class="w-9 h-9 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Month Grid --}}
                                <div class="grid grid-cols-3 gap-2 mb-4">
                                    @php $monthsShort = explode(',', __('app.cal_months_short')); @endphp
                                    <template x-for="(month, index) in {{ json_encode($monthsShort) }}" :key="index">
                                        <button @click="pickMonth(index + 1); showPicker = false;" type="button"
                                           :class="{
                                               'bg-primary-600 text-white': currentMonth === index + 1 && currentYear === {{ now()->year }},
                                               'bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300': currentMonth === index + 1 && currentYear !== {{ now()->year }},
                                               'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600': currentMonth !== index + 1,
                                               'ring-2 ring-primary-400 ring-offset-1 dark:ring-offset-gray-800': index + 1 === {{ now()->month }} && currentYear === {{ now()->year }}
                                           }"
                                           class="px-3 py-2.5 rounded-lg font-medium text-sm transition-colors">
                                            <span x-text="month"></span>
                                        </button>
                                    </template>
                                </div>

                                {{-- Today Button --}}
                                <button @click="goToday(); showPicker = false;" type="button"
                                   class="w-full py-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors">
                                    {{ __('app.cal_today') }}
                                </button>
                            </div>
                        </div>

                        <button @click="nextPeriod()" type="button"
                           class="w-11 h-11 sm:w-10 sm:h-10 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 dark:active:bg-gray-600 rounded-xl transition-colors">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2">
                @if(auth()->user()->canEdit('events'))
                @if($isGoogleConnected)
                    <div x-data="{
                        syncing: false,
                        message: '',
                        error: false,
                        lastSynced: '{{ $lastSyncedAt ?? '' }}',
                        get syncStatus() {
                            if (this.syncing) return @js( __('app.syncing') );
                            if (!this.lastSynced) return @js( __('app.not_synced') );
                            const diff = Math.floor((Date.now() - new Date(this.lastSynced).getTime()) / 60000);
                            if (diff < 1) return @js( __('app.just_now') );
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
                                message = data.message || data.error || @js( __('app.cal_done') );
                                error = !data.success;
                                if (data.success) lastSynced = new Date().toISOString();
                            })
                            .catch(e => { syncing = false; message = @js( __('app.cal_connection_error') ); error = true; })
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
                       title="{{ __('app.cal_connect_google') }}">
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

    {{-- Calendar --}}
    <div>
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
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $daysShort[$i] }}@if($isToday) <span class="ml-1 text-primary-600 dark:text-primary-400 font-medium">{{ __('app.cal_today') }}</span>@endif</span>
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
                                                {{ Str::limit($item->title, 12) }}
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
                                            <span class="ml-1 text-primary-600 dark:text-primary-400 font-medium">{{ __('app.cal_today') }}</span>
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
                            <button onclick="document.getElementById('createEventModal').classList.remove('hidden')" class="mt-3 inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.cal_create_event') }}
                            </button>
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
                        {{ __('app.cal_upcoming_events', ['month' => $months[$nextMonth - 1] ?? '']) }}
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


</div>

{{-- Create Event Modal --}}
@php
    $createMinistries = $ministries;
    $ministriesData = $createMinistries->map(function($m) {
        return ['id' => $m->id, 'name' => $m->name, 'color' => $m->color, 'is_worship' => $m->is_worship_ministry, 'is_sunday_part' => $m->is_sunday_service_part];
    })->values();
    $gcSettings = auth()->user()->settings['google_calendar'] ?? null;
    $gcConnected = $gcSettings && !empty($gcSettings['access_token']);
    $gcCalendarId = $gcSettings['calendar_id'] ?? 'primary';
@endphp
@if(auth()->user()->can('create', \App\Models\Event::class))
<div id="createEventModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="create-event-title" role="dialog" aria-modal="true">
    <div class="flex items-start justify-center min-h-screen pt-4 px-4 pb-20 sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeCreateEventModal()"></div>
        <div class="relative inline-block w-full max-w-lg mx-auto mt-8 sm:mt-16 bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden z-10" x-data="eventCreateForm()">
            <form class="divide-y divide-gray-200 dark:divide-gray-700" x-ref="form" @submit.prevent="submitForm">
                {{-- Header --}}
                <div class="px-6 py-4 flex items-center justify-between">
                    <h3 id="create-event-title" class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.cal_new_event') }}</h3>
                    <button type="button" onclick="closeCreateEventModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4 space-y-4 max-h-[70vh] overflow-y-auto">
                    {{-- Title --}}
                    <div>
                        <label for="modal_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.cal_name_label') }} *</label>
                        <input type="text" name="title" id="modal_title" required
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               :class="errors.title ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                               placeholder="{{ __('app.sunday_worship') }}">
                        <template x-if="errors.title"><p class="mt-1 text-sm text-red-500" x-text="errors.title[0]"></p></template>
                    </div>

                    {{-- Date & Time --}}
                    <div x-data="{ allDay: false, multiDay: false }">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="modal_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.cal_date_label') }} *</label>
                                <input type="date" name="date" id="modal_date" value="{{ now()->format('Y-m-d') }}" required
                                       class="w-full px-3 py-2.5 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                       :class="errors.date ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                                <template x-if="errors.date"><p class="mt-1 text-sm text-red-500" x-text="errors.date[0]"></p></template>
                            </div>
                            <div x-show="!allDay" x-transition>
                                <label for="modal_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.cal_time_label') }} *</label>
                                <input type="time" name="time" id="modal_time" value="10:00" :required="!allDay"
                                       class="w-full px-3 py-2.5 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                       :class="errors.time ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                                <template x-if="errors.time"><p class="mt-1 text-sm text-red-500" x-text="errors.time[0]"></p></template>
                            </div>
                        </div>

                        <label class="flex items-center gap-2 mt-3 cursor-pointer">
                            <input type="checkbox" name="multi_day" value="1" x-model="multiDay"
                                   class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('app.cal_multi_day') }}</span>
                        </label>

                        <div x-show="multiDay" x-collapse class="mt-3">
                            <label for="modal_end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.cal_end_date_label') }} *</label>
                            <input type="date" name="end_date" id="modal_end_date"
                                   class="w-full px-3 py-2.5 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                   :class="errors.end_date ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                            <template x-if="errors.end_date"><p class="mt-1 text-sm text-red-500" x-text="errors.end_date[0]"></p></template>
                        </div>

                        <label class="flex items-center gap-2 mt-2 cursor-pointer">
                            <input type="checkbox" name="all_day" value="1" x-model="allDay"
                                   class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('app.cal_all_day') }}</span>
                        </label>
                    </div>

                    {{-- Recurrence --}}
                    <div x-data="{ showRecurrence: false, ...recurrenceSettings() }" x-init="init()">
                        <button type="button" @click="showRecurrence = !showRecurrence"
                                class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            <svg class="w-4 h-4 transition-transform" :class="showRecurrence ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            {{ __('app.recurrence') }}
                            <span x-show="recurrenceType" x-cloak class="text-xs text-primary-600 dark:text-primary-400 font-medium"
                                  x-text="recurrenceType ? '(' + ({'daily':@js(__('app.recurrence_daily_js')),'weekly':@js(__('app.recurrence_weekly_js')),'biweekly':@js(__('app.recurrence_biweekly_js')),'monthly':@js(__('app.recurrence_monthly_js')),'yearly':@js(__('app.recurrence_yearly_js')),'weekdays':@js(__('app.recurrence_weekdays_js')),'custom':@js(__('app.recurrence_custom_js'))}[recurrenceType] || '') + ')' : ''"></span>
                        </button>

                        <div x-show="showRecurrence" x-collapse class="mt-3 space-y-3">
                            <select x-model="recurrenceType" @change="updateRecurrence()"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">{{ __('app.do_not_repeat') }}</option>
                                <option value="daily">{{ __('app.recurrence_daily') }}</option>
                                <option value="weekly">{{ __('app.recurrence_weekly') }}</option>
                                <option value="biweekly">{{ __('app.every_2_weeks') }}</option>
                                <option value="monthly">{{ __('app.recurrence_monthly') }}</option>
                                <option value="yearly">{{ __('app.recurrence_yearly') }}</option>
                                <option value="weekdays">{{ __('app.every_weekday') }}</option>
                                <option value="custom">{{ __('app.custom_recurrence') }}</option>
                            </select>

                            <div x-show="recurrenceType === 'custom'" x-collapse class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg space-y-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('app.every_n') }}</span>
                                    <input type="number" x-model="customInterval" @input="updatePreview()" min="1" max="99"
                                           class="w-16 px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center">
                                    <select x-model="customFrequency" @change="updatePreview()"
                                            class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        <option value="day">{{ __('app.days_unit') }}</option>
                                        <option value="week">{{ __('app.weeks_unit') }}</option>
                                        <option value="month">{{ __('app.months_unit') }}</option>
                                        <option value="year">{{ __('app.years_unit') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div x-show="recurrenceType && recurrenceType !== ''" x-collapse class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg space-y-3">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('app.ends') }}</label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" x-model="endType" value="count" @change="updatePreview()" class="text-primary-600 focus:ring-primary-500">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('app.after_label') }}</span>
                                        <input type="number" x-model="endCount" @input="updatePreview()" min="2" max="365" :disabled="endType !== 'count'"
                                               class="w-16 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center disabled:opacity-50">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('app.occurrences') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" x-model="endType" value="date" @change="updatePreview()" class="text-primary-600 focus:ring-primary-500">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('app.until_date') }}</span>
                                        <input type="date" x-model="endDate" @change="updatePreview()" :disabled="endType !== 'date'"
                                               class="px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white disabled:opacity-50">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="previewText"></p>
                            </div>
                        </div>

                        <template x-if="recurrenceType">
                            <div>
                                <input type="hidden" name="recurrence_rule[frequency]" :value="getRecurrenceFrequency()">
                                <input type="hidden" name="recurrence_rule[interval]" :value="getRecurrenceInterval()">
                            </div>
                        </template>
                        <input type="hidden" name="recurrence_end_type" :value="endType">
                        <input type="hidden" name="recurrence_end_count" :value="endCount">
                        <input type="hidden" name="recurrence_end_date" :value="endDate">
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label for="modal_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.notes_label') }}</label>
                        <textarea name="notes" id="modal_notes" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                  placeholder="{{ __('app.additional_info_placeholder') }}"></textarea>
                    </div>

                    {{-- Ministry --}}
                    @if($createMinistries->count() > 0)
                    <div x-data="modalMinistrySelector()">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.team_label') }}</label>
                        <x-searchable-select
                            name="ministry_id"
                            :items="$createMinistries"
                            labelKey="name"
                            valueKey="id"
                            colorKey="color"
                            placeholder="{{ __('app.search_team') }}"
                            nullText="{{ __('app.without_team') }}"
                            nullable
                            x-on:select-changed="selectedId = $event.detail.value || ''"
                        />
                        <div x-show="selected" x-cloak class="mt-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                                  :style="selected ? 'background-color: ' + selected.color + '20; color: ' + selected.color + '; border: 1px solid ' + selected.color + '40' : ''">
                                <span class="w-2 h-2 rounded-full" :style="selected ? 'background-color: ' + selected.color : ''"></span>
                                <span x-text="selected?.name"></span>
                            </span>
                        </div>
                    </div>
                    @endif

                    <input type="hidden" name="is_service" value="1">
                    <input type="hidden" name="track_attendance" value="1">

                    {{-- Reminders --}}
                    @if($currentChurch->telegram_bot_token)
                    <div x-data="{ showReminders: false, ...reminderSettings() }">
                        <button type="button" @click="showReminders = !showReminders"
                                class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            <svg class="w-4 h-4 transition-transform" :class="showReminders ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            {{ __('app.telegram_reminders') }}
                            <span x-show="reminders.length > 0" x-cloak class="inline-flex items-center justify-center w-5 h-5 text-xs font-medium bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-full" x-text="reminders.length"></span>
                        </button>

                        <div x-show="showReminders" x-collapse class="mt-3 space-y-2">
                            <template x-for="(reminder, index) in reminders" :key="index">
                                <div class="flex items-center gap-2">
                                    <select x-model="reminder.type" @change="updateReminder(index)"
                                            class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        <option value="days">{{ __('app.days_before') }}</option>
                                        <option value="hours">{{ __('app.hours_before') }}</option>
                                    </select>
                                    <input type="number" x-model="reminder.value" min="1" max="30"
                                           class="w-20 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center">
                                    <template x-if="reminder.type === 'days'">
                                        <input type="time" x-model="reminder.time"
                                               class="w-28 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    </template>
                                    <button type="button" @click="removeReminder(index)"
                                            class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>

                            <button type="button" @click="addReminder()"
                                    class="inline-flex items-center gap-1 text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.add_reminder') }}
                            </button>

                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('app.reminders_sent_to_assigned') }}
                            </p>
                        </div>

                        <template x-for="(reminder, index) in reminders" :key="'input-'+index">
                            <div>
                                <input type="hidden" :name="'reminders['+index+'][type]'" :value="reminder.type">
                                <input type="hidden" :name="'reminders['+index+'][value]'" :value="reminder.value">
                                <input type="hidden" :name="'reminders['+index+'][time]'" :value="reminder.time || ''">
                            </div>
                        </template>
                    </div>
                    @endif

                    {{-- Google Calendar --}}
                    @if($gcConnected)
                    <div x-data="modalGoogleCalendarPicker()">
                        <div class="flex items-center gap-3 mb-2">
                            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none">
                                <path d="M19 4H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z" stroke="#4285F4" stroke-width="1.5"/>
                                <path d="M8 2v4M16 2v4M3 10h18" stroke="#4285F4" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Google Calendar</span>
                        </div>
                        <select name="google_calendar_id" x-model="calendarId"
                                class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm">
                            <option value="">{{ __('app.do_not_add_google') }}</option>
                            <option value="primary">{{ __('app.primary_calendar') }}</option>
                            <template x-for="cal in calendars" :key="cal.id">
                                <option :value="cal.id" :disabled="!cal.can_sync" :selected="cal.id === defaultCalendarId"
                                        x-text="cal.summary + (cal.can_sync ? '' : ' ' + @js(__('app.cal_read_only')))"></option>
                            </template>
                        </select>
                    </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeCreateEventModal()" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        {{ __('app.cancel') }}
                    </button>
                    <button type="submit" :disabled="saving" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                        <svg x-show="saving" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="saving ? @js( __('app.creating_label') ) : @js( __('app.create') )"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

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
                            {{ __('app.cal_copy') }}
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
                        <p class="text-xs mt-2">{{ __('app.cal_api_params') }}: <code>start</code>, <code>end</code>, <code>ministry</code></p>
                    </div>
                </details>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                <button onclick="hideSubscriptionModal()" class="w-full px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-800 dark:text-white rounded-lg font-medium transition-colors">
                    {{ __('app.cal_close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var _calI18n = {
    checkForm: @json(__('app.check_form_errors')),
    saveError: @json(__('app.save_error_msg')),
    saved: @json(__('app.saved_label')),
    connectionError: @json(__('app.connection_error_msg')),
    months: {!! json_encode($months) !!},
    recDaily: @json(__('app.recurrence_daily_js')),
    recWeekly: @json(__('app.recurrence_weekly_js')),
    recBiweekly: @json(__('app.recurrence_biweekly_js')),
    recMonthly: @json(__('app.recurrence_monthly_js')),
    recYearly: @json(__('app.recurrence_yearly_js')),
    recWeekdays: @json(__('app.recurrence_weekdays_js')),
    everyN: @json(__('app.every_n')),
    willCreate: @json(__('app.will_create_n_events')),
    willRepeat: @json(__('app.will_repeat_until')),
    freqDay: @json(__('app.days_unit')),
    freqWeek: @json(__('app.weeks_unit')),
    freqMonth: @json(__('app.months_unit')),
    freqYear: @json(__('app.years_unit'))
};

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
            btn.textContent = @js( __('app.copied') );
            setTimeout(() => btn.textContent = originalText, 2000);
        }
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideSubscriptionModal();
        closeCreateEventModal();
    }
});

// Create Event Modal
function closeCreateEventModal() {
    document.getElementById('createEventModal')?.classList.add('hidden');
}

function eventCreateForm() {
    return {
        saving: false,
        errors: {},
        async submitForm() {
            this.saving = true;
            this.errors = {};
            const formData = new FormData(this.$refs.form);
            try {
                const response = await fetch('{{ route("events.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.errors = data.errors;
                        showToast('error', _calI18n.checkForm);
                    } else {
                        showToast('error', data.message || _calI18n.saveError);
                    }
                    this.saving = false;
                    return;
                }
                showToast('success', data.message || _calI18n.saved);
                closeCreateEventModal();
                // Reload the calendar to show the new event
                setTimeout(() => Livewire.navigate(window.location.href), 600);
            } catch (e) {
                showToast('error', _calI18n.connectionError);
                this.saving = false;
            }
        }
    }
}

function recurrenceSettings() {
    return {
        recurrenceType: '',
        customInterval: 1,
        customFrequency: 'week',
        endType: 'count',
        endCount: 12,
        endDate: '',
        previewText: '',

        init() {
            const defaultEnd = new Date();
            defaultEnd.setMonth(defaultEnd.getMonth() + 3);
            this.endDate = defaultEnd.toISOString().split('T')[0];
            this.updatePreview();
        },

        updateRecurrence() {
            switch(this.recurrenceType) {
                case 'daily': this.endCount = 30; break;
                case 'weekly': case 'biweekly': this.endCount = 12; break;
                case 'monthly': this.endCount = 12; break;
                case 'yearly': this.endCount = 5; break;
                case 'weekdays': this.endCount = 20; break;
            }
            this.updatePreview();
        },

        updatePreview() {
            if (!this.recurrenceType) { this.previewText = ''; return; }
            const typeLabels = {
                'daily': _calI18n.recDaily, 'weekly': _calI18n.recWeekly, 'biweekly': _calI18n.recBiweekly,
                'monthly': _calI18n.recMonthly, 'yearly': _calI18n.recYearly, 'weekdays': _calI18n.recWeekdays,
                'custom': `${_calI18n.everyN} ${this.customInterval} ${this.getFrequencyLabel()}`
            };
            const label = typeLabels[this.recurrenceType] || '';
            if (this.endType === 'count') {
                this.previewText = _calI18n.willCreate.replace(':count', this.endCount).replace(':label', label);
            } else {
                this.previewText = _calI18n.willRepeat.replace(':label', label).replace(':date', this.formatDate(this.endDate));
            }
        },

        getFrequencyLabel() {
            const labels = {
                'day': _calI18n.freqDay,
                'week': _calI18n.freqWeek,
                'month': _calI18n.freqMonth,
                'year': _calI18n.freqYear
            };
            return labels[this.customFrequency] || '';
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleDateString(document.documentElement.lang || 'uk', { day: 'numeric', month: 'long', year: 'numeric' });
        },

        getRecurrenceFrequency() {
            if (!this.recurrenceType) return '';
            if (this.recurrenceType === 'custom') {
                const map = { 'day': 'daily', 'week': 'weekly', 'month': 'monthly', 'year': 'yearly' };
                return map[this.customFrequency] || this.customFrequency;
            }
            if (this.recurrenceType === 'biweekly') return 'weekly';
            return this.recurrenceType;
        },

        getRecurrenceInterval() {
            if (!this.recurrenceType) return 1;
            if (this.recurrenceType === 'custom') return this.customInterval;
            if (this.recurrenceType === 'biweekly') return 2;
            return 1;
        }
    }
}

function reminderSettings(initial = []) {
    return {
        reminders: initial.length ? initial : [],
        addReminder() {
            this.reminders.push({ type: 'days', value: 1, time: '18:00' });
        },
        removeReminder(index) {
            this.reminders.splice(index, 1);
        },
        updateReminder(index) {
            if (this.reminders[index].type === 'hours') {
                this.reminders[index].time = null;
            } else {
                this.reminders[index].time = '18:00';
            }
        }
    }
}

function modalMinistrySelector() {
    return {
        selectedId: '',
        ministries: @json($ministriesData ?? []),
        get selected() {
            return this.ministries.find(m => m.id == this.selectedId);
        }
    }
}

function modalGoogleCalendarPicker() {
    return {
        calendarId: '{{ $gcCalendarId ?? "primary" }}',
        defaultCalendarId: '{{ $gcCalendarId ?? "primary" }}',
        calendars: [],
        async init() {
            try {
                const res = await fetch('{{ route("settings.google-calendar.calendars") }}');
                if (res.ok) {
                    const data = await res.json();
                    this.calendars = data.calendars || [];
                }
            } catch (e) {}
        }
    }
}

// Calendar Navigator
function calendarNavigator(initialState) {
    const months = _calI18n.months;

    // Restore saved view from localStorage if URL doesn't have explicit view param
    const savedCalendarFilters = filterStorage.load('schedule_calendar', { currentView: 'month' });
    const urlParams = new URLSearchParams(window.location.search);
    const hasExplicitView = urlParams.has('view');
    const effectiveView = hasExplicitView ? initialState.view : savedCalendarFilters.currentView;

    return {
        currentView: effectiveView,
        currentYear: initialState.year,
        currentMonth: initialState.month,
        currentWeek: initialState.week,
        loading: false,

        _needsRedirect: !hasExplicitView && effectiveView !== initialState.view,

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

        init() {
            // If saved view differs from server default and URL had no explicit view, redirect once
            if (this._needsRedirect) {
                this.$nextTick(() => this.loadCalendar());
            }
            this.$watch('currentView', () => {
                filterStorage.save('schedule_calendar', { currentView: this.currentView });
            });
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
                if (this.currentWeek === 1) {
                    this.currentYear--;
                    this.currentWeek = this.getISOWeeksInYear(this.currentYear);
                } else {
                    this.currentWeek--;
                }
            } else {
                this.currentMonth = this.currentMonth === 1 ? 12 : this.currentMonth - 1;
                if (this.currentMonth === 12) this.currentYear--;
            }
            this.loadCalendar();
        },

        nextPeriod() {
            if (this.currentView === 'week') {
                const maxWeek = this.getISOWeeksInYear(this.currentYear);
                if (this.currentWeek >= maxWeek) {
                    this.currentWeek = 1;
                    this.currentYear++;
                } else {
                    this.currentWeek++;
                }
            } else {
                this.currentMonth = this.currentMonth === 12 ? 1 : this.currentMonth + 1;
                if (this.currentMonth === 1) this.currentYear++;
            }
            this.loadCalendar();
        },

        getISOWeeksInYear(year) {
            const d = new Date(year, 11, 28); // Dec 28 is always in the last ISO week
            return d.getWeek();
        },

        pickMonth(month) {
            this.currentMonth = month;
            if (this.currentView === 'week') {
                // Jump to first week of the selected month
                const d = new Date(this.currentYear, month - 1, 4);
                this.currentWeek = d.getWeek();
            }
            this.loadCalendar();
        },

        goToday() {
            const today = new Date();
            this.currentYear = today.getFullYear();
            this.currentMonth = today.getMonth() + 1;
            this.currentWeek = today.getWeek();
            this.loadCalendar();
        },

        loadCalendar() {
            const params = new URLSearchParams({
                view: this.currentView,
                year: this.currentYear,
                tab: 'calendar',
                ...(this.currentView === 'week' ? { week: this.currentWeek } : { month: this.currentMonth })
            });

            Livewire.navigate(`{{ route('schedule') }}?${params}`);
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
@endif
@endsection
