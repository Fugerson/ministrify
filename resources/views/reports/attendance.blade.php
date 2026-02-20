@extends('layouts.app')

@section('title', 'Звіт: Відвідуваність')

@section('actions')
<a href="{{ route('reports.export-attendance', ['year' => $year]) }}"
   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
    </svg>
    Експорт Excel
</a>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('reports.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Назад до звітів
        </a>

        <form class="flex items-center space-x-2">
            <select name="year" onchange="this.form.submit()"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                @for($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <select name="ministry_id" onchange="this.form.submit()"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <option value="">Усі команди</option>
                @foreach($ministries as $ministry)
                    <option value="{{ $ministry->id }}" {{ $ministryId == $ministry->id ? 'selected' : '' }}>{{ $ministry->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Monthly Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Відвідуваність по місяцях</h3>
        <div class="h-64">
            <canvas id="attendanceChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Weekday Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">По днях тижня</h3>
            <div class="space-y-3">
                @php $maxWeekday = max(array_column($weekdayStats, 'count')); @endphp
                @foreach($weekdayStats as $day)
                    <div class="flex items-center">
                        <span class="w-8 text-sm font-medium text-gray-600 dark:text-gray-400">{{ $day['day'] }}</span>
                        <div class="flex-1 mx-3 h-6 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-primary-500 rounded-full" style="width: {{ $maxWeekday > 0 ? ($day['count'] / $maxWeekday * 100) : 0 }}%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $day['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Top Attendees -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🏆 Топ відвідувачів</h3>
            <div class="space-y-3">
                @foreach($topAttendees as $index => $person)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold
                                {{ $index === 0 ? 'bg-yellow-100 text-yellow-700' : ($index === 1 ? 'bg-gray-100 text-gray-600' : ($index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-50 text-gray-500')) }}">
                                {{ $index + 1 }}
                            </span>
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">{{ $person->full_name }}</span>
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $person->attendance_records_count }} відвідувань</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Inactive Members -->
    @if($inactiveMembers->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Перестали відвідувати (3+ місяці)
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($inactiveMembers as $person)
                <a href="{{ route('people.show', $person) }}" class="flex items-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                    <div class="w-10 h-10 bg-red-200 dark:bg-red-800 rounded-full flex items-center justify-center text-red-700 dark:text-red-300 font-medium">
                        {{ substr($person->first_name, 0, 1) }}
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $person->full_name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Останній раз: {{ $person->attendanceRecords->first()?->created_at->diffForHumans() ?? 'Невідомо' }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
onPageReady(function() {
    const ctx = document.getElementById('attendanceChart');
    if (!ctx) return;
    var old = Chart.getChart(ctx); if (old) old.destroy();
    const data = @json($monthlyData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.month),
            datasets: [
                {
                    label: 'Всього відвідувань',
                    data: data.map(d => d.count),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Унікальних людей',
                    data: data.map(d => d.unique_people),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>
@endsection
