{{-- Analytics Charts Widget (Admin Only) --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Аналітика
        </h2>
        <div class="flex rounded-xl bg-gray-100 dark:bg-gray-700 p-1 overflow-x-auto">
            <button type="button" data-chart="growth" class="chart-tab px-2 sm:px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-colors bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm whitespace-nowrap">
                Зростання
            </button>
            <button type="button" data-chart="financial" class="chart-tab px-2 sm:px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white whitespace-nowrap">
                Фінанси
            </button>
            <button type="button" data-chart="attendance" class="chart-tab px-2 sm:px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white whitespace-nowrap">
                Відвідуваність
            </button>
            <button type="button" data-chart="ministries" class="chart-tab px-2 sm:px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white whitespace-nowrap">
                Служіння
            </button>
        </div>
    </div>
    <div class="p-4 lg:p-6">
        <div class="h-72 relative">
            <div id="chartLoader" class="absolute inset-0 flex items-center justify-center">
                <svg class="animate-spin h-8 w-8 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <canvas id="analyticsChart"></canvas>
        </div>
        <div id="chartLegend" class="mt-4 flex flex-wrap items-center justify-center gap-4 text-sm text-gray-500 dark:text-gray-400">
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const analyticsCtx = document.getElementById('analyticsChart');
    if (!analyticsCtx) return;

    let analyticsChart = null;
    const chartLoader = document.getElementById('chartLoader');
    const chartLegend = document.getElementById('chartLegend');
    const chartTabs = document.querySelectorAll('.chart-tab');
    const primaryColor = '{{ $currentChurch->primary_color ?? "#3b82f6" }}';

    const chartColors = {
        primary: primaryColor,
        success: '#22c55e',
        danger: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6',
        purple: '#8b5cf6',
        pink: '#ec4899',
        teal: '#14b8a6',
        orange: '#f97316',
        cyan: '#06b6d4',
    };

    const colorPalette = [
        chartColors.primary, chartColors.success, chartColors.danger,
        chartColors.warning, chartColors.purple, chartColors.pink,
        chartColors.teal, chartColors.orange, chartColors.cyan, chartColors.info
    ];

    function getChartOptions(type) {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const gridColor = isDark ? '#374151' : '#f3f4f6';

        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#1f2937' : '#ffffff',
                    titleColor: isDark ? '#ffffff' : '#111827',
                    bodyColor: isDark ? '#d1d5db' : '#6b7280',
                    borderColor: isDark ? '#374151' : '#e5e7eb',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: type === 'financial' ? {
                        label: function(context) {
                            return context.dataset.label + ': ' + new Intl.NumberFormat('uk-UA').format(context.raw) + ' ₴';
                        }
                    } : {}
                }
            },
            scales: type === 'ministries' ? {} : {
                x: {
                    grid: { display: false },
                    ticks: { color: textColor }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: {
                        color: textColor,
                        callback: type === 'financial' ? function(value) {
                            return new Intl.NumberFormat('uk-UA', { notation: 'compact' }).format(value) + ' ₴';
                        } : undefined
                    }
                }
            }
        };
    }

    async function loadChart(type) {
        chartLoader.classList.remove('hidden');

        try {
            const response = await fetch(`{{ route('dashboard.charts') }}?type=${type}`);
            const data = await response.json();

            if (analyticsChart) {
                analyticsChart.destroy();
            }

            chartLoader.classList.add('hidden');

            const config = buildChartConfig(type, data);
            analyticsChart = new Chart(analyticsCtx, config);
            updateLegend(type, data);

        } catch (error) {
            console.error('Error loading chart:', error);
            chartLoader.classList.add('hidden');
        }
    }

    function buildChartConfig(type, data) {
        switch(type) {
            case 'growth':
                return {
                    type: 'line',
                    data: {
                        labels: data.map(d => d.label),
                        datasets: [{
                            label: 'Загальна кількість',
                            data: data.map(d => d.value),
                            borderColor: chartColors.primary,
                            backgroundColor: chartColors.primary + '20',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: chartColors.primary,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                        }, {
                            label: 'Нові',
                            data: data.map(d => d.new),
                            borderColor: chartColors.success,
                            backgroundColor: 'transparent',
                            borderDash: [5, 5],
                            tension: 0.4,
                            pointBackgroundColor: chartColors.success,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 3,
                        }]
                    },
                    options: getChartOptions('growth')
                };

            case 'financial':
                return {
                    type: 'bar',
                    data: {
                        labels: data.map(d => d.label),
                        datasets: [{
                            label: 'Доходи',
                            data: data.map(d => d.income),
                            backgroundColor: chartColors.success + 'cc',
                            borderRadius: 6,
                            borderSkipped: false,
                        }, {
                            label: 'Витрати',
                            data: data.map(d => d.expenses),
                            backgroundColor: chartColors.danger + 'cc',
                            borderRadius: 6,
                            borderSkipped: false,
                        }, {
                            label: 'Залишок',
                            data: data.map(d => d.balance),
                            type: 'line',
                            borderColor: chartColors.info,
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.4,
                            pointBackgroundColor: chartColors.info,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            yAxisID: 'y',
                        }]
                    },
                    options: getChartOptions('financial')
                };

            case 'attendance':
                return {
                    type: 'line',
                    data: {
                        labels: data.map(d => d.label),
                        datasets: [{
                            label: 'Середня відвідуваність',
                            data: data.map(d => d.value),
                            borderColor: chartColors.info,
                            backgroundColor: chartColors.info + '20',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: chartColors.info,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                        }]
                    },
                    options: getChartOptions('attendance')
                };

            case 'ministries':
                return {
                    type: 'doughnut',
                    data: {
                        labels: data.map(d => d.label),
                        datasets: [{
                            data: data.map(d => d.value),
                            backgroundColor: data.map((d, i) => d.color || colorPalette[i % colorPalette.length]),
                            borderWidth: 0,
                            hoverOffset: 10,
                        }]
                    },
                    options: {
                        ...getChartOptions('ministries'),
                        cutout: '60%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.raw + ' учасників';
                                    }
                                }
                            }
                        }
                    }
                };

            default:
                return { type: 'line', data: { labels: [], datasets: [] }, options: {} };
        }
    }

    function updateLegend(type, data) {
        let html = '';

        switch(type) {
            case 'growth':
                html = `
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.primary}"></span>
                        <span>Загальна кількість</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.success}"></span>
                        <span>Нові за місяць</span>
                    </div>
                `;
                break;

            case 'financial':
                html = `
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.success}"></span>
                        <span>Доходи</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.danger}"></span>
                        <span>Витрати</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.info}"></span>
                        <span>Залишок</span>
                    </div>
                `;
                break;

            case 'attendance':
                html = `
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.info}"></span>
                        <span>Середня відвідуваність за 12 місяців</span>
                    </div>
                `;
                break;

            case 'ministries':
                html = data.slice(0, 5).map((d, i) => `
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${d.color || colorPalette[i % colorPalette.length]}"></span>
                        <span>${d.label}: ${d.value}</span>
                    </div>
                `).join('');
                if (data.length > 5) {
                    html += `<span class="text-gray-400">+${data.length - 5} більше</span>`;
                }
                break;
        }

        chartLegend.innerHTML = html;
    }

    chartTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            chartTabs.forEach(t => {
                t.classList.remove('bg-white', 'dark:bg-gray-600', 'text-gray-900', 'dark:text-white', 'shadow-sm');
                t.classList.add('text-gray-600', 'dark:text-gray-400');
            });
            this.classList.add('bg-white', 'dark:bg-gray-600', 'text-gray-900', 'dark:text-white', 'shadow-sm');
            this.classList.remove('text-gray-600', 'dark:text-gray-400');

            loadChart(this.dataset.chart);
        });
    });

    loadChart('growth');
});
</script>
@endpush
