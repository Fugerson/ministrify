@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
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
                            <p class="font-medium text-gray-900 dark:text-white text-sm truncate">{{ $assignment->event->ministry->icon }} {{ $assignment->event->title }}</p>
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
                    <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                        <span class="text-2xl">{{ $event->ministry->icon }}</span>
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
    const ctx = document.getElementById('attendanceChart');
    if (!ctx) return;

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
});
</script>
@endpush
@endsection
