@extends('layouts.app')

@section('title', __('app.report_finances_title'))

@section('actions')
<a href="{{ route('reports.export-finances', ['year' => $year]) }}"
   id="export-link"
   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
    </svg>
    {{ __('app.reports_export_excel') }}
</a>
@endsection

@section('content')
<div x-data="{
    year: '{{ $year }}',
    loading: false,
    async applyFilters() {
        this.loading = true;
        const params = new URLSearchParams();
        params.set('year', this.year);
        history.replaceState(null, '', '?' + params.toString());
        // Update export link
        const exportLink = document.getElementById('export-link');
        if (exportLink) {
            const exportUrl = new URL(exportLink.href);
            exportUrl.searchParams.set('year', this.year);
            exportLink.href = exportUrl.toString();
        }
        try {
            const res = await fetch('?' + params.toString(), { headers: {'X-Requested-With': 'XMLHttpRequest'} });
            const html = await res.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const newContent = doc.getElementById('report-content');
            if (newContent) {
                document.getElementById('report-content').innerHTML = newContent.innerHTML;
                doc.getElementById('report-content').querySelectorAll('script').forEach(script => {
                    const newScript = document.createElement('script');
                    if (script.src) {
                        newScript.src = script.src;
                    } else {
                        newScript.textContent = script.textContent;
                    }
                    document.body.appendChild(newScript);
                    document.body.removeChild(newScript);
                });
            }
        } catch (e) {
            console.error('Filter error:', e);
        }
        this.loading = false;
    }
}" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('reports.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ __('app.reports_back_to_reports') }}
        </a>

        <select x-model="year" @change="applyFilters()"
                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white">
            @for($y = now()->year; $y >= now()->year - 5; $y--)
                <option value="{{ $y }}">{{ $y }}</option>
            @endfor
        </select>
    </div>

    <!-- Loading overlay -->
    <div x-show="loading" x-cloak class="flex items-center justify-center py-12">
        <svg class="animate-spin h-8 w-8 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    <div id="report-content" x-show="!loading">
        <!-- Year Comparison -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                <p class="text-green-100 text-sm">{{ __('app.reports_income_year', ['year' => $year]) }}</p>
                <p class="text-3xl font-bold mt-1">{{ number_format($comparison['current']['income'], 0, ',', ' ') }} ₴</p>
                @if($comparison['previous']['income'] > 0)
                    @php $incomeGrowth = (($comparison['current']['income'] - $comparison['previous']['income']) / $comparison['previous']['income']) * 100; @endphp
                    <p class="text-green-100 text-sm mt-2">
                        {{ $incomeGrowth >= 0 ? '+' : '' }}{{ number_format($incomeGrowth, 1) }}% vs {{ $year - 1 }}
                    </p>
                @endif
            </div>

            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
                <p class="text-red-100 text-sm">{{ __('app.reports_expense_year', ['year' => $year]) }}</p>
                <p class="text-3xl font-bold mt-1">{{ number_format($comparison['current']['expense'], 0, ',', ' ') }} ₴</p>
                @if($comparison['previous']['expense'] > 0)
                    @php $expenseGrowth = (($comparison['current']['expense'] - $comparison['previous']['expense']) / $comparison['previous']['expense']) * 100; @endphp
                    <p class="text-red-100 text-sm mt-2">
                        {{ $expenseGrowth >= 0 ? '+' : '' }}{{ number_format($expenseGrowth, 1) }}% vs {{ $year - 1 }}
                    </p>
                @endif
            </div>

            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <p class="text-blue-100 text-sm">{{ __('app.reports_balance_year', ['year' => $year]) }}</p>
                @php $balance = $comparison['current']['income'] - $comparison['current']['expense']; @endphp
                <p class="text-3xl font-bold mt-1">{{ $balance >= 0 ? '+' : '' }}{{ number_format($balance, 0, ',', ' ') }} ₴</p>
            </div>
        </div>

        <!-- Monthly Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-3 sm:p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.reports_monthly_dynamics') }}</h3>
            <div class="h-72 -mx-1 sm:mx-0">
                <canvas id="financeChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Income by Category -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.reports_income_by_category') }}</h3>
                @if($incomeByCategory->count() > 0)
                    @php $totalIncome = $incomeByCategory->sum('total'); @endphp
                    <div class="space-y-3">
                        @foreach($incomeByCategory as $item)
                            @php $percent = $totalIncome > 0 ? ($item->total / $totalIncome * 100) : 0; @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $item->category?->name ?? __('app.reports_no_category') }}</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($item->total, 0, ',', ' ') }} ₴</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-green-500" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">{{ __('app.reports_no_data') }}</p>
                @endif
            </div>

            <!-- Expense by Ministry -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.reports_expense_by_team') }}</h3>
                @if($expenseByMinistry->count() > 0)
                    @php $totalExpense = $expenseByMinistry->sum('total'); @endphp
                    <div class="space-y-3">
                        @foreach($expenseByMinistry as $item)
                            @php $percent = $totalExpense > 0 ? ($item->total / $totalExpense * 100) : 0; @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $item->ministry?->name ?? __('app.reports_no_team') }}</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($item->total, 0, ',', ' ') }} ₴</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full" style="width: {{ $percent }}%; background-color: {{ $item->ministry?->color ?? '#ef4444' }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">{{ __('app.reports_no_data') }}</p>
                @endif
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
        <script>
        onPageReady(function() {
            const ctx = document.getElementById('financeChart');
            if (!ctx) return;
            var old = Chart.getChart(ctx); if (old) old.destroy();
            const data = @json($monthlyData);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.month),
                    datasets: [
                        {
                            label: @json(__('app.reports_income_label')),
                            data: data.map(d => d.income),
                            backgroundColor: 'rgba(34, 197, 94, 0.8)',
                            borderRadius: 4,
                            yAxisID: 'y',
                        },
                        {
                            label: @json(__('app.reports_expense_label')),
                            data: data.map(d => d.expense),
                            backgroundColor: 'rgba(239, 68, 68, 0.8)',
                            borderRadius: 4,
                            yAxisID: 'y',
                        },
                        {
                            label: @json(__('app.reports_cumulative_balance')),
                            data: data.map(d => d.cumulative),
                            type: 'line',
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true,
                            tension: 0.4,
                            yAxisID: 'y1',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { font: { size: window.innerWidth < 640 ? 13 : 15 }, boxWidth: window.innerWidth < 640 ? 12 : 40, padding: 16 } } },
                    scales: {
                        y: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            ticks: {
                                font: { size: window.innerWidth < 640 ? 9 : 12 },
                                callback: v => {
                                    if (window.innerWidth < 640) {
                                        if (Math.abs(v) >= 1000) return (v / 1000).toFixed(0) + 'k';
                                        return v;
                                    }
                                    return v.toLocaleString('uk-UA') + ' ₴';
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: {
                                font: { size: window.innerWidth < 640 ? 9 : 12 },
                                callback: v => {
                                    if (window.innerWidth < 640) {
                                        const prefix = v >= 0 ? '+' : '';
                                        if (Math.abs(v) >= 1000) return prefix + (v / 1000).toFixed(0) + 'k';
                                        return prefix + v;
                                    }
                                    return (v >= 0 ? '+' : '') + v.toLocaleString('uk-UA') + ' ₴';
                                }
                            }
                        }
                    }
                }
            });
        });
        </script>
    </div>
</div>

<x-realtime-banner channel="finances" />
@endsection
