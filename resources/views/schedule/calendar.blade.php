@extends('layouts.app')

@section('title', 'Розклад')

@section('actions')
@leader
<a href="{{ route('events.create') }}"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Подія
</a>
@endleader
@endsection

@section('content')
@php
    $months = ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];
    $days = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Нд'];

    $prevMonth = $month == 1 ? 12 : $month - 1;
    $prevYear = $month == 1 ? $year - 1 : $year;
    $nextMonth = $month == 12 ? 1 : $month + 1;
    $nextYear = $month == 12 ? $year + 1 : $year;
@endphp

<div class="bg-white rounded-lg shadow">
    <!-- Month navigation -->
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <a href="{{ route('schedule', ['year' => $prevYear, 'month' => $prevMonth]) }}"
           class="p-2 hover:bg-gray-100 rounded-lg">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>

        <h2 class="text-xl font-semibold text-gray-900">{{ $months[$month - 1] }} {{ $year }}</h2>

        <a href="{{ route('schedule', ['year' => $nextYear, 'month' => $nextMonth]) }}"
           class="p-2 hover:bg-gray-100 rounded-lg">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <!-- Events by date -->
    <div class="divide-y">
        @php
            $currentDate = $startOfMonth->copy();
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
        @endphp

        @while($currentDate <= $endOfMonth)
            @php
                $dateKey = $currentDate->format('Y-m-d');
                $dayEvents = $events->get($dateKey, collect());
                $isToday = $currentDate->isToday();
                $isPast = $currentDate->isPast() && !$isToday;
            @endphp

            @if($dayEvents->count() > 0 || $isToday)
                <div class="p-4 {{ $isToday ? 'bg-primary-50' : '' }} {{ $isPast ? 'opacity-60' : '' }}">
                    <div class="flex items-baseline mb-3">
                        <span class="text-lg font-semibold text-gray-900 {{ $isToday ? 'text-primary-600' : '' }}">
                            {{ $currentDate->format('d') }}
                        </span>
                        <span class="ml-2 text-sm text-gray-500">
                            {{ ['Неділя', 'Понеділок', 'Вівторок', 'Середа', 'Четвер', 'П\'ятниця', 'Субота'][$currentDate->dayOfWeek] }}
                            @if($isToday)
                                <span class="ml-1 text-primary-600 font-medium">(Сьогодні)</span>
                            @endif
                        </span>
                    </div>

                    @if($dayEvents->count() > 0)
                        <div class="space-y-2">
                            @foreach($dayEvents as $event)
                                <a href="{{ route('events.show', $event) }}"
                                   class="block p-3 border rounded-lg hover:bg-white hover:shadow-sm transition-all">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <span class="text-xl">{{ $event->ministry->icon }}</span>
                                            <div class="ml-3">
                                                <p class="font-medium text-gray-900">{{ $event->title }}</p>
                                                <p class="text-sm text-gray-500">{{ $event->ministry->name }} &bull; {{ $event->time->format('H:i') }}</p>
                                            </div>
                                        </div>
                                        <div class="text-sm">
                                            @if($event->isFullyStaffed())
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">
                                                    &#9989; {{ $event->confirmed_assignments_count }}/{{ $event->total_positions_count }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700">
                                                    &#9888; {{ $event->filled_positions_count }}/{{ $event->total_positions_count }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Немає подій</p>
                    @endif
                </div>
            @endif

            @php $currentDate->addDay(); @endphp
        @endwhile

        @if($events->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <p>Немає подій у цьому місяці</p>
                @leader
                <a href="{{ route('events.create') }}" class="mt-2 inline-block text-primary-600 hover:text-primary-500">
                    Створити подію
                </a>
                @endleader
            </div>
        @endif
    </div>
</div>
@endsection
