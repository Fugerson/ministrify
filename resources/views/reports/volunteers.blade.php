@extends('layouts.app')

@section('title', __('app.report_volunteers_title'))

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('reports.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ __('app.reports_back_to_reports') }}
        </a>

        <div class="flex items-center gap-3">
            <select x-data @change="
                    const url = new URL(window.location);
                    url.searchParams.set('year', $el.value);
                    Livewire.navigate(url.toString());
                "
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white">
                @for($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <a href="{{ route('reports.export-volunteers', ['year' => $year]) }}"
               class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Excel
            </a>
        </div>
    </div>

    <!-- Monthly Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.reports_volunteer_activity_by_month') }}</h3>
        <div class="h-64">
            <canvas id="volunteersChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Volunteers -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.reports_top_volunteers') }}</h3>
            <div class="space-y-3">
                @foreach($topVolunteers as $index => $person)
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $index < 3 ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                        <div class="flex items-center">
                            <span class="w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold
                                {{ $index === 0 ? 'bg-yellow-400 text-yellow-900' : ($index === 1 ? 'bg-gray-300 text-gray-700' : ($index === 2 ? 'bg-orange-400 text-orange-900' : 'bg-gray-200 text-gray-600')) }}">
                                {{ $index + 1 }}
                            </span>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $person->full_name }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $person->assignments_count }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.reports_events_count') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- By Ministry -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.reports_distribution_by_team') }}</h3>
            @if($byMinistry->count() > 0)
                @php $totalAssignments = $byMinistry->sum('count'); @endphp
                <div class="space-y-3">
                    @foreach($byMinistry as $item)
                        @php $percent = $totalAssignments > 0 ? ($item->count / $totalAssignments * 100) : 0; @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $item->name }}</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('app.reports_assignments_count', ['count' => $item->count]) }}</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3">
                                <div class="h-3 rounded-full" style="width: {{ $percent }}%; background-color: {{ $item->color ?? '#3b82f6' }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">{{ __('app.reports_no_data') }}</p>
            @endif
        </div>
    </div>

    <!-- Inactive Volunteers -->
    @if($inactiveVolunteers->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 text-orange-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            {{ __('app.reports_inactive_volunteers') }}
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            @foreach($inactiveVolunteers as $person)
                <a href="{{ route('people.show', $person) }}" class="flex flex-col items-center p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors text-center">
                    <div class="w-12 h-12 bg-orange-200 dark:bg-orange-800 rounded-full flex items-center justify-center text-orange-700 dark:text-orange-300 font-medium text-lg mb-2">
                        {{ mb_substr($person->first_name, 0, 1) }}
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate w-full">{{ $person->full_name }}</p>
                </a>
            @endforeach
        </div>
    </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
onPageReady(function() {
    const ctx = document.getElementById('volunteersChart');
    if (!ctx) return;
    var old = Chart.getChart(ctx); if (old) old.destroy();
    const data = @json($monthlyData);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.month),
            datasets: [
                {
                    label: @json(__('app.reports_assignments_label')),
                    data: data.map(d => d.assignments),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderRadius: 4,
                },
                {
                    label: @json(__('app.reports_unique_volunteers')),
                    data: data.map(d => d.volunteers),
                    type: 'line',
                    borderColor: 'rgb(249, 115, 22)',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    fill: true,
                    tension: 0.4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { font: { size: window.innerWidth < 640 ? 13 : 15 }, boxWidth: window.innerWidth < 640 ? 12 : 40, padding: 16 } } },
            scales: { y: { beginAtZero: true } }
        }
    });
});
</script>
@endsection
