@extends('layouts.app')

@section('title', 'Відвідуваність: ' . $group->name)

@section('actions')
@can('update', $group)
<div class="flex items-center gap-2">
    <a href="{{ route('groups.attendance.checkin', $group) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-xl hover:bg-green-700 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Чек-ін
    </a>
    <a href="{{ route('groups.attendance.create', $group) }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Записати
    </a>
</div>
@endcan
@endsection

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('groups.index') }}" class="hover:text-primary-600">Групи</a>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('groups.show', $group) }}" class="hover:text-primary-600">{{ $group->name }}</a>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span>Відвідуваність</span>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $attendances->total() }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Всього зустрічей</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $group->members->count() }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Учасників</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ round($attendances->avg('members_present'), 1) }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Сер. відвідуваність</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $attendances->sum('guests_count') }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Всього гостей</div>
        </div>
    </div>

    <!-- Chart -->
    @if($chartData->count() > 1)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Тренд відвідуваності</h3>
        <div class="h-48">
            <canvas id="attendanceChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Attendance List -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-gray-900 dark:text-white">Історія зустрічей</h3>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($attendances as $attendance)
            <a href="{{ route('groups.attendance.show', [$group, $attendance]) }}" class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors block">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                        <span class="text-sm font-bold text-primary-600 dark:text-primary-400">{{ $attendance->date->format('d') }}</span>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $attendance->date->translatedFormat('l, d F Y') }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if($attendance->time)
                            {{ $attendance->time->format('H:i') }}
                            @endif
                            @if($attendance->location)
                            • {{ $attendance->location }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-bold text-gray-900 dark:text-white">{{ $attendance->members_present }}</span>
                        <span class="text-gray-500 dark:text-gray-400">/ {{ $group->members->count() }}</span>
                    </div>
                    @if($attendance->guests_count > 0)
                    <p class="text-sm text-gray-500">+{{ $attendance->guests_count }} гостей</p>
                    @endif
                </div>
            </a>
            @empty
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p>Немає записів відвідуваності</p>
            </div>
            @endforelse
        </div>
        @if($attendances->hasPages())
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $attendances->links() }}
        </div>
        @endif
    </div>
</div>

@if($chartData->count() > 1)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
onPageReady(function() {
    var el = document.getElementById('attendanceChart');
    if (!el) return;
    var old = Chart.getChart(el); if (old) old.destroy();
    const ctx = el.getContext('2d');
    const chartData = @json($chartData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(d => d.date),
            datasets: [{
                label: 'Учасники',
                data: chartData.map(d => d.members),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.3,
            }, {
                label: 'Гості',
                data: chartData.map(d => d.guests),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@endif
@endsection
