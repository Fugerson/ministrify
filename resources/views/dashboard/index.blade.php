@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Людей</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_people'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Служінь</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_ministries'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Подій цього місяця</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['events_this_month'] }}</p>
                </div>
            </div>
        </div>

        @admin
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Витрати цього місяця</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['expenses_this_month'] ?? 0, 0, ',', ' ') }} &#8372;</p>
                </div>
            </div>
        </div>
        @endadmin
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upcoming events -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Найближчі події</h2>
                <a href="{{ route('schedule') }}" class="text-sm text-primary-600 hover:text-primary-500">
                    Дивитися всі
                </a>
            </div>
            <div class="divide-y">
                @forelse($upcomingEvents as $event)
                    <a href="{{ route('events.show', $event) }}" class="block px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 text-2xl">{{ $event->ministry->icon }}</div>
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-medium text-gray-900">{{ $event->title }}</p>
                                    <span class="text-sm text-gray-500">{{ $event->date->format('d.m') }}</span>
                                </div>
                                <p class="text-sm text-gray-500">{{ $event->ministry->name }} &bull; {{ $event->time->format('H:i') }}</p>
                                <div class="mt-1">
                                    @if($event->isFullyStaffed())
                                        <span class="inline-flex items-center text-xs text-green-600">
                                            &#9989; {{ $event->filled_positions_count }}/{{ $event->total_positions_count }} позицій
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-xs text-yellow-600">
                                            &#9888; Потрібно: {{ $event->unfilled_positions->pluck('name')->implode(', ') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500">
                        Немає запланованих подій
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Pending assignments (for volunteers) -->
        @if(count($pendingAssignments) > 0)
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Очікують підтвердження</h2>
            </div>
            <div class="divide-y">
                @foreach($pendingAssignments as $assignment)
                    <div class="px-6 py-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ $assignment->event->title }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $assignment->event->date->format('d.m.Y') }} &bull;
                                    {{ $assignment->position->name }}
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <form method="POST" action="{{ route('assignments.confirm', $assignment) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-green-100 text-green-700 text-sm rounded-lg hover:bg-green-200">
                                        Підтвердити
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('assignments.decline', $assignment) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-red-100 text-red-700 text-sm rounded-lg hover:bg-red-200">
                                        Відхилити
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Ministry budgets (for admins) -->
        @admin
        @if(count($ministryBudgets) > 0)
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Бюджети служінь</h2>
                <a href="{{ route('expenses.report') }}" class="text-sm text-primary-600 hover:text-primary-500">
                    Повний звіт
                </a>
            </div>
            <div class="p-6 space-y-4">
                @foreach($ministryBudgets as $budget)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">{{ $budget['icon'] }} {{ $budget['name'] }}</span>
                            <span class="text-sm text-gray-500">{{ number_format($budget['spent'], 0, ',', ' ') }} / {{ number_format($budget['budget'], 0, ',', ' ') }} &#8372;</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $budget['percentage'] > 90 ? 'bg-red-500' : ($budget['percentage'] > 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                 style="width: {{ min(100, $budget['percentage']) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- People needing attention -->
        @if(count($needAttention) > 0)
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">&#9888; Потребують уваги</h2>
                <p class="text-sm text-gray-500">Не відвідували 3+ тижні</p>
            </div>
            <div class="divide-y">
                @foreach($needAttention as $person)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                <span class="text-sm text-gray-600">{{ substr($person->first_name, 0, 1) }}</span>
                            </div>
                            <span class="ml-3 text-sm text-gray-900">{{ $person->full_name }}</span>
                        </div>
                        @if($person->phone)
                            <a href="tel:{{ $person->phone }}" class="text-sm text-primary-600 hover:text-primary-500">
                                Зателефонувати
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        @endadmin
    </div>
</div>
@endsection
