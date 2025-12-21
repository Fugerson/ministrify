@extends('layouts.app')

@section('title', 'Розклад')

@section('actions')
@leader
<a href="{{ route('events.create') }}"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Нова подія
</a>
@endleader
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

<div class="space-y-4">
    <!-- View Toggle & Navigation -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- View Toggle -->
            <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-xl p-1">
                <a href="{{ route('schedule', ['view' => 'week', 'year' => $year, 'week' => now()->weekOfYear]) }}"
                   class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $view === 'week' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    Тиждень
                </a>
                <a href="{{ route('schedule', ['view' => 'month', 'year' => $year, 'month' => $month]) }}"
                   class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $view === 'month' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    Місяць
                </a>
            </div>

            <!-- Date Navigation -->
            <div class="flex items-center justify-between sm:justify-center gap-4">
                @if($view === 'week')
                    <a href="{{ route('schedule', ['view' => 'week', 'year' => $prevWeekYear, 'week' => $prevWeek]) }}"
                       class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white min-w-[200px] text-center">
                        {{ $startDate->format('d.m') }} - {{ $endDate->format('d.m.Y') }}
                    </h2>
                    <a href="{{ route('schedule', ['view' => 'week', 'year' => $nextWeekYear, 'week' => $nextWeek]) }}"
                       class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('schedule', ['view' => 'month', 'year' => $prevYear, 'month' => $prevMonth]) }}"
                       class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white min-w-[180px] text-center">
                        {{ $months[$month - 1] }} {{ $year }}
                    </h2>
                    <a href="{{ route('schedule', ['view' => 'month', 'year' => $nextYear, 'month' => $nextMonth]) }}"
                       class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endif
            </div>

            <!-- Today Button -->
            <a href="{{ route('schedule', ['view' => $view]) }}"
               class="px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors">
                Сьогодні
            </a>
        </div>
    </div>

    @if($view === 'week')
        <!-- Week View -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Days Header -->
            <div class="grid grid-cols-7 border-b border-gray-100 dark:border-gray-700">
                @php $dayDate = $startDate->copy(); @endphp
                @for($i = 0; $i < 7; $i++)
                    @php
                        $isToday = $dayDate->isToday();
                    @endphp
                    <div class="p-3 text-center border-r border-gray-100 dark:border-gray-700 last:border-r-0 {{ $isToday ? 'bg-primary-50 dark:bg-primary-900/30' : '' }}">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $daysShort[$i] }}</p>
                        <p class="text-lg font-semibold mt-1 {{ $isToday ? 'text-primary-600 dark:text-primary-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $dayDate->format('d') }}
                        </p>
                    </div>
                    @php $dayDate->addDay(); @endphp
                @endfor
            </div>

            <!-- Events Grid -->
            <div class="grid grid-cols-7 min-h-[400px]">
                @php $dayDate = $startDate->copy(); @endphp
                @for($i = 0; $i < 7; $i++)
                    @php
                        $dateKey = $dayDate->format('Y-m-d');
                        $dayEvents = $events->get($dateKey, collect());
                        $isToday = $dayDate->isToday();
                        $isPast = $dayDate->isPast() && !$isToday;
                    @endphp
                    <div class="border-r border-gray-100 dark:border-gray-700 last:border-r-0 p-2 {{ $isToday ? 'bg-primary-50/50 dark:bg-primary-900/20' : '' }} {{ $isPast ? 'opacity-60' : '' }}">
                        <div class="space-y-2">
                            @foreach($dayEvents as $event)
                                <a href="{{ route('events.show', $event) }}"
                                   class="block p-2 rounded-lg text-xs transition-all hover:shadow-md"
                                   style="background-color: {{ $event->ministry->color ?? '#3b82f6' }}20; border-left: 3px solid {{ $event->ministry->color ?? '#3b82f6' }};">
                                    <p class="font-medium text-gray-900 dark:text-white truncate">{{ $event->time->format('H:i') }}</p>
                                    <p class="text-gray-600 dark:text-gray-300 truncate">{{ $event->title }}</p>
                                    <div class="flex items-center mt-1">
                                        <span class="text-lg mr-1">{{ $event->ministry->icon }}</span>
                                        @if($event->isFullyStaffed())
                                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                        @else
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @php $dayDate->addDay(); @endphp
                @endfor
            </div>
        </div>
    @else
        <!-- Month View - Calendar Grid -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Days Header -->
            <div class="grid grid-cols-7 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                @foreach($daysShort as $day)
                    <div class="p-3 text-center">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ $day }}</p>
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
                        <div class="min-h-[100px] lg:min-h-[120px] border-b border-r border-gray-100 dark:border-gray-700 p-1.5 lg:p-2 {{ !$isCurrentMonth ? 'bg-gray-50 dark:bg-gray-800/50' : '' }} {{ $isToday ? 'bg-primary-50 dark:bg-primary-900/20' : '' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium {{ $isToday ? 'w-7 h-7 flex items-center justify-center rounded-full bg-primary-600 text-white' : ($isCurrentMonth ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500') }}">
                                    {{ $calendarDate->format('j') }}
                                </span>
                                @if($dayEvents->count() > 2)
                                    <span class="text-xs text-gray-400 dark:text-gray-500">+{{ $dayEvents->count() - 2 }}</span>
                                @endif
                            </div>
                            <div class="space-y-1">
                                @foreach($dayEvents->take(2) as $event)
                                    <a href="{{ route('events.show', $event) }}"
                                       class="block px-1.5 py-0.5 rounded text-xs truncate transition-colors hover:opacity-80 {{ $isPast && !$isToday ? 'opacity-60' : '' }}"
                                       style="background-color: {{ $event->ministry->color ?? '#3b82f6' }}20; color: {{ $event->ministry->color ?? '#3b82f6' }};">
                                        <span class="hidden lg:inline">{{ $event->time->format('H:i') }}</span>
                                        {{ Str::limit($event->title, 15) }}
                                    </a>
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
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white">Події місяця</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
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
                        <div class="p-4 {{ $isToday ? 'bg-primary-50 dark:bg-primary-900/20' : '' }} {{ $isPast ? 'opacity-60' : '' }}">
                            <div class="flex items-baseline mb-3">
                                <span class="text-lg font-bold {{ $isToday ? 'text-primary-600 dark:text-primary-400' : 'text-gray-900 dark:text-white' }}">
                                    {{ $currentDate->format('d') }}
                                </span>
                                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $dayOfWeek }}
                                    @if($isToday)
                                        <span class="ml-1 text-primary-600 dark:text-primary-400 font-medium">(Сьогодні)</span>
                                    @endif
                                </span>
                            </div>

                            <div class="space-y-2">
                                @foreach($dayEvents as $event)
                                    <a href="{{ route('events.show', $event) }}"
                                       class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $event->ministry->color ?? '#3b82f6' }}20;">
                                                <span class="text-xl">{{ $event->ministry->icon }}</span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">{{ $event->title }}</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $event->ministry->name }} &bull; {{ $event->time->format('H:i') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($event->isFullyStaffed())
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300">
                                                    {{ $event->confirmed_assignments_count }}/{{ $event->total_positions_count }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300">
                                                    {{ $event->filled_positions_count }}/{{ $event->total_positions_count }}
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

                    @php $currentDate->addDay(); @endphp
                @endwhile

                @if($events->isEmpty())
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400">Немає подій у цьому місяці</p>
                        @leader
                        <a href="{{ route('events.create') }}" class="mt-3 inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Створити подію
                        </a>
                        @endleader
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
