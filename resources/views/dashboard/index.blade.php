@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-4 lg:space-y-6">
    <!-- Mobile Welcome -->
    <div class="lg:hidden">
        <h1 class="text-xl font-bold text-gray-900">Привіт, {{ explode(' ', auth()->user()->name)[0] }}!</h1>
        <p class="text-sm text-gray-500">{{ now()->locale('uk')->translatedFormat('l, d F') }}</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 mt-3">{{ $stats['total_people'] }}</p>
            <p class="text-xs lg:text-sm text-gray-500 mt-0.5">Людей</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-green-50 flex items-center justify-center">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 mt-3">{{ $stats['total_ministries'] }}</p>
            <p class="text-xs lg:text-sm text-gray-500 mt-0.5">Служінь</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-purple-50 flex items-center justify-center">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 mt-3">{{ $stats['events_this_month'] }}</p>
            <p class="text-xs lg:text-sm text-gray-500 mt-0.5">Подій в місяці</p>
        </div>

        @admin
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-amber-50 flex items-center justify-center">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 mt-3">{{ number_format($stats['expenses_this_month'] ?? 0, 0, ',', ' ') }}</p>
            <p class="text-xs lg:text-sm text-gray-500 mt-0.5">Витрат &#8372;</p>
        </div>
        @endadmin
    </div>

    <!-- Pending Assignments Alert -->
    @if(count($pendingAssignments) > 0)
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-100 p-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900">Очікує підтвердження</h3>
                <p class="text-sm text-gray-600 mt-1">У вас {{ count($pendingAssignments) }} призначень, які потребують відповіді</p>
                <div class="mt-3 space-y-2">
                    @foreach($pendingAssignments->take(3) as $assignment)
                    <div class="bg-white rounded-xl p-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 text-sm truncate">{{ $assignment->event->ministry->icon }} {{ $assignment->event->title }}</p>
                            <p class="text-xs text-gray-500">{{ $assignment->event->date->format('d.m') }} &bull; {{ $assignment->position->name }}</p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <form method="POST" action="{{ route('assignments.confirm', $assignment) }}">
                                @csrf
                                <button type="submit" class="w-9 h-9 bg-green-100 text-green-700 rounded-lg flex items-center justify-center hover:bg-green-200 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('assignments.decline', $assignment) }}">
                                @csrf
                                <button type="submit" class="w-9 h-9 bg-red-100 text-red-700 rounded-lg flex items-center justify-center hover:bg-red-200 transition-colors">
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
        <!-- Upcoming Events -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 lg:px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Найближчі події</h2>
                <a href="{{ route('schedule') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    Всі
                </a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($upcomingEvents as $event)
                <a href="{{ route('events.show', $event) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-2xl">{{ $event->ministry->icon }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-medium text-gray-900 truncate">{{ $event->title }}</p>
                            @if($event->isFullyStaffed())
                            <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></span>
                            @else
                            <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500">{{ $event->date->format('d.m') }} &bull; {{ $event->time->format('H:i') }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-medium text-gray-900">{{ $event->filled_positions_count }}/{{ $event->total_positions_count }}</p>
                        <p class="text-xs text-gray-500">позицій</p>
                    </div>
                </a>
                @empty
                <div class="p-8 text-center">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm">Немає запланованих подій</p>
                </div>
                @endforelse
            </div>
        </div>

        @admin
        <!-- Ministry Budgets -->
        @if(count($ministryBudgets) > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 lg:px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Бюджети служінь</h2>
                <a href="{{ route('expenses.report') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    Звіт
                </a>
            </div>
            <div class="p-4 lg:p-5 space-y-4">
                @foreach($ministryBudgets as $budget)
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">{{ $budget['icon'] }} {{ $budget['name'] }}</span>
                        <span class="text-sm text-gray-500">
                            {{ number_format($budget['spent'], 0, ',', ' ') }} / {{ number_format($budget['budget'], 0, ',', ' ') }}
                        </span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500
                            {{ $budget['percentage'] > 90 ? 'bg-red-500' : ($budget['percentage'] > 70 ? 'bg-amber-500' : 'bg-green-500') }}"
                             style="width: {{ min(100, $budget['percentage']) }}%">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- People Needing Attention -->
        @if(count($needAttention) > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 lg:px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <h2 class="font-semibold text-gray-900">Потребують уваги</h2>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">Не відвідували 3+ тижні</p>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($needAttention as $person)
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-600">{{ mb_substr($person->first_name, 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ $person->full_name }}</p>
                            @if($person->phone)
                            <p class="text-xs text-gray-500">{{ $person->phone }}</p>
                            @endif
                        </div>
                    </div>
                    @if($person->phone)
                    <a href="tel:{{ $person->phone }}" class="w-9 h-9 bg-green-100 text-green-700 rounded-lg flex items-center justify-center hover:bg-green-200 transition-colors">
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
        @endadmin
    </div>

    <!-- Quick Actions (Mobile) -->
    <div class="lg:hidden">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Швидкі дії</h3>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('people.create') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center text-center hover:border-primary-200 transition-colors">
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-700">Додати людину</span>
            </a>
            <a href="{{ route('events.create') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center text-center hover:border-primary-200 transition-colors">
                <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-700">Створити подію</span>
            </a>
            <a href="{{ route('attendance.create') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center text-center hover:border-primary-200 transition-colors">
                <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-700">Відмітити присутність</span>
            </a>
            @leader
            <a href="{{ route('expenses.create') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center text-center hover:border-primary-200 transition-colors">
                <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-700">Додати витрату</span>
            </a>
            @endleader
        </div>
    </div>
</div>
@endsection
