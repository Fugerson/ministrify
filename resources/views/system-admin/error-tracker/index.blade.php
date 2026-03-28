@extends('layouts.system-admin')

@section('title', 'Error Tracker')

@section('content')
<div class="space-y-6" x-data="{ showClearModal: false, clearAction: '' }">

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Unresolved</p>
                    <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $totalUnresolved }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-600/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Today (unique)</p>
                    <p class="text-3xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $totalToday }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 dark:bg-amber-600/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Today (total hits)</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $totalOccurrencesToday }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-600/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Errors — Last 30 Days</h3>
        <div style="height: 250px;">
            <canvas id="errorsChart"></canvas>
        </div>
    </div>

    <!-- Filters + Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700">
        <form method="GET" action="{{ route('system.errors.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search errors..."
                   class="flex-1 min-w-[200px] px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500">

            <select name="status" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                <option value="unresolved" {{ ($status ?? 'unresolved') === 'unresolved' ? 'selected' : '' }}>Unresolved</option>
                <option value="resolved" {{ $status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="ignored" {{ $status === 'ignored' ? 'selected' : '' }}>Ignored</option>
                <option value="" {{ $status === '' ? 'selected' : '' }}>All</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium">
                Filter
            </button>

            @if($search || ($status && $status !== 'unresolved'))
                <a href="{{ route('system.errors.index') }}" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm">
                    Reset
                </a>
            @endif

            <div class="ml-auto flex gap-2">
                <button type="button" @click="clearAction = 'resolved'; showClearModal = true"
                        class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                    Clear resolved
                </button>
                <button type="button" @click="clearAction = 'all'; showClearModal = true"
                        class="px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium">
                    Clear all
                </button>
            </div>
        </form>
    </div>

    <!-- Top Errors -->
    @if($topErrors->isNotEmpty() && (!$status || $status === 'unresolved'))
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Errors by Occurrences</h3>
        <div class="space-y-3">
            @foreach($topErrors as $err)
                <a href="{{ route('system.errors.show', $err) }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-mono text-gray-900 dark:text-white truncate">{{ $err->short_message }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $err->short_file }}:{{ $err->line }}</p>
                    </div>
                    <span class="ml-3 px-2.5 py-1 bg-red-100 dark:bg-red-600/20 text-red-700 dark:text-red-400 text-sm font-bold rounded-lg">
                        {{ $err->occurrences }}x
                    </span>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Errors List -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($errors->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-16 h-16 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 text-lg">No errors found</p>
                <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Everything is running smoothly!</p>
            </div>
        @else
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($errors as $error)
                    <a href="{{ route('system.errors.show', $error) }}"
                       class="flex items-start gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">

                        {{-- Status indicator --}}
                        <div class="mt-1 flex-shrink-0">
                            @if($error->status === 'unresolved')
                                <span class="w-3 h-3 bg-red-500 rounded-full block"></span>
                            @elseif($error->status === 'resolved')
                                <span class="w-3 h-3 bg-green-500 rounded-full block"></span>
                            @else
                                <span class="w-3 h-3 bg-gray-400 rounded-full block"></span>
                            @endif
                        </div>

                        {{-- Error info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $error->short_message }}
                            </p>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1">
                                <span class="text-xs font-mono text-gray-500 dark:text-gray-400">
                                    {{ $error->short_file }}:{{ $error->line }}
                                </span>
                                @if($error->exception_class)
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-md">
                                        {{ class_basename($error->exception_class) }}
                                    </span>
                                @endif
                                @if($error->url)
                                    <span class="text-xs text-gray-400 dark:text-gray-500 truncate max-w-[300px]">
                                        {{ $error->method }} {{ parse_url($error->url, PHP_URL_PATH) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Meta --}}
                        <div class="flex-shrink-0 text-right">
                            <span class="inline-flex items-center px-2 py-1 bg-red-100 dark:bg-red-600/20 text-red-700 dark:text-red-400 text-xs font-bold rounded-lg">
                                {{ $error->occurrences }}x
                            </span>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                {{ $error->last_seen_at->diffForHumans() }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                {{ $errors->links() }}
            </div>
        @endif
    </div>

    <!-- Clear Confirmation Modal -->
    <div x-show="showClearModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="showClearModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 max-w-md w-full shadow-xl" @click.outside="showClearModal = false">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Confirm</h3>
            <p class="text-gray-600 dark:text-gray-400 mt-2" x-text="clearAction === 'all' ? 'Delete ALL error logs? This cannot be undone.' : 'Delete all resolved errors?'"></p>
            <div class="flex justify-end gap-3 mt-6">
                <button @click="showClearModal = false" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    Cancel
                </button>
                <form method="POST" action="{{ route('system.errors.clear') }}">
                    @csrf
                    <input type="hidden" name="action" :value="clearAction">
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const textColor = isDark ? '#9ca3af' : '#6b7280';

    const ctx = document.getElementById('errorsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($days->pluck('date')),
            datasets: [{
                label: 'Total hits',
                data: @json($days->pluck('total')),
                backgroundColor: isDark ? 'rgba(239,68,68,0.3)' : 'rgba(239,68,68,0.2)',
                borderColor: 'rgb(239,68,68)',
                borderWidth: 1,
                borderRadius: 4,
            }, {
                label: 'Unique errors',
                data: @json($days->pluck('unique')),
                backgroundColor: isDark ? 'rgba(99,102,241,0.5)' : 'rgba(99,102,241,0.4)',
                borderColor: 'rgb(99,102,241)',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { labels: { color: textColor, boxWidth: 12 } },
            },
            scales: {
                x: {
                    grid: { color: gridColor },
                    ticks: { color: textColor, maxRotation: 0, autoSkip: true, maxTicksLimit: 10 }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: { color: textColor, precision: 0 }
                }
            }
        }
    });
});
</script>
@endsection
