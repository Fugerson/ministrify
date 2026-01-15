@extends('layouts.app')

@section('title', 'Фінанси')

@section('actions')
<div class="flex items-center space-x-2">
    <a href="{{ route('finances.incomes.create') }}"
       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Надходження
    </a>
    <a href="{{ route('finances.expenses.create') }}"
       class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
        </svg>
        Витрата
    </a>
</div>
@endsection

@section('content')
<div x-data="financesDashboard()" class="space-y-6">
    <!-- Period selector -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-2">
                <select x-model="selectedYear" @change="updatePeriod()"
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <select x-model="selectedMonth" @change="updatePeriod()"
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    <option value="">Весь рік</option>
                    <option value="1">Січень</option>
                    <option value="2">Лютий</option>
                    <option value="3">Березень</option>
                    <option value="4">Квітень</option>
                    <option value="5">Травень</option>
                    <option value="6">Червень</option>
                    <option value="7">Липень</option>
                    <option value="8">Серпень</option>
                    <option value="9">Вересень</option>
                    <option value="10">Жовтень</option>
                    <option value="11">Листопад</option>
                    <option value="12">Грудень</option>
                </select>
            </div>
            <div class="flex items-center space-x-3 text-sm">
                <a href="{{ route('finances.incomes') }}" class="text-green-600 dark:text-green-400 hover:underline">Надходження</a>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <a href="{{ route('finances.expenses.index') }}" class="text-red-600 dark:text-red-400 hover:underline">Витрати</a>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <a href="{{ route('donations.index') }}" class="text-primary-600 dark:text-primary-400 hover:underline">Пожертви</a>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <a href="{{ route('finances.monobank.index') }}" class="text-gray-600 dark:text-gray-400 hover:underline">Monobank</a>
            </div>
        </div>
    </div>

    <!-- Current Balance Card -->
    <div class="bg-gradient-to-br {{ $currentBalance >= 0 ? 'from-indigo-600 to-purple-600' : 'from-orange-500 to-red-500' }} rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-indigo-100 text-sm font-medium">Поточний баланс каси</p>
                <p class="text-4xl font-bold mt-1">{{ number_format($currentBalance, 0, ',', ' ') }} ₴</p>
                <div class="mt-3 text-sm text-indigo-100 space-y-1">
                    @if($initialBalance > 0)
                    <div class="flex justify-between">
                        <span>Початковий баланс{{ $initialBalanceDate ? ' (' . $initialBalanceDate->format('d.m.Y') . ')' : '' }}:</span>
                        <span class="font-medium">{{ number_format($initialBalance, 0, ',', ' ') }} ₴</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span>+ Всього надходжень:</span>
                        <span class="font-medium text-green-200">{{ number_format($allTimeIncome, 0, ',', ' ') }} ₴</span>
                    </div>
                    <div class="flex justify-between">
                        <span>- Всього витрат:</span>
                        <span class="font-medium text-red-200">{{ number_format($allTimeExpense, 0, ',', ' ') }} ₴</span>
                    </div>
                </div>
            </div>
            <div class="p-3 bg-white bg-opacity-20 rounded-full ml-4">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        @if(!$initialBalance && !$initialBalanceDate)
        <div class="mt-4 pt-4 border-t border-white/20">
            <a href="{{ route('settings.index') }}#finance" class="text-sm text-white/80 hover:text-white inline-flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Вказати початковий баланс
            </a>
        </div>
        @endif
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Income -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Надходження</p>
                    <p class="text-3xl font-bold mt-1">{{ number_format($totalIncome, 0, ',', ' ') }} ₴</p>
                    @if($yearComparison['growth']['income'] != 0)
                        <p class="text-green-100 text-sm mt-2">
                            <span class="{{ $yearComparison['growth']['income'] > 0 ? '' : 'text-red-200' }}">
                                {{ $yearComparison['growth']['income'] > 0 ? '+' : '' }}{{ $yearComparison['growth']['income'] }}%
                            </span>
                            порівняно з минулим роком
                        </p>
                    @endif
                </div>
                <div class="p-3 bg-green-400 bg-opacity-30 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Expense -->
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Витрати</p>
                    <p class="text-3xl font-bold mt-1">{{ number_format($totalExpense, 0, ',', ' ') }} ₴</p>
                    @if($yearComparison['growth']['expense'] != 0)
                        <p class="text-red-100 text-sm mt-2">
                            <span class="{{ $yearComparison['growth']['expense'] < 0 ? 'text-green-200' : '' }}">
                                {{ $yearComparison['growth']['expense'] > 0 ? '+' : '' }}{{ $yearComparison['growth']['expense'] }}%
                            </span>
                            порівняно з минулим роком
                        </p>
                    @endif
                </div>
                <div class="p-3 bg-red-400 bg-opacity-30 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Period Balance -->
        <div class="bg-gradient-to-br {{ $periodBalance >= 0 ? 'from-blue-500 to-blue-600' : 'from-orange-500 to-orange-600' }} rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Результат за період</p>
                    <p class="text-3xl font-bold mt-1">{{ $periodBalance >= 0 ? '+' : '' }}{{ number_format($periodBalance, 0, ',', ' ') }} ₴</p>
                    <p class="text-blue-100 text-sm mt-2">
                        {{ $periodLabel }}
                    </p>
                </div>
                <div class="p-3 bg-blue-400 bg-opacity-30 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Динаміка за {{ $year }} рік</h3>
        <div class="h-64">
            <canvas id="financeChart"></canvas>
        </div>
    </div>

    <!-- Analytics grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Income by category -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Надходження по категоріях</h3>
                <a href="{{ route('finances.incomes') }}" class="text-sm text-primary-600 hover:text-primary-500">Усі →</a>
            </div>
            <div class="p-6">
                @if($incomeByCategory->where('incomes_sum_amount', '>', 0)->count() > 0)
                    <div class="space-y-4">
                        @foreach($incomeByCategory->where('incomes_sum_amount', '>', 0)->take(5) as $cat)
                            @php
                                $percent = $totalIncome > 0 ? ($cat->incomes_sum_amount / $totalIncome) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $cat->icon ?? '💰' }} {{ $cat->name }}
                                    </span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($cat->incomes_sum_amount, 0, ',', ' ') }} ₴
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-green-500" style="width: {{ $percent }}%; background-color: {{ $cat->color }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Немає надходжень за цей період</p>
                @endif
            </div>
        </div>

        <!-- Expense by ministry -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Витрати по командах</h3>
                <a href="{{ route('finances.expenses.index') }}" class="text-sm text-primary-600 hover:text-primary-500">Усі →</a>
            </div>
            <div class="p-6">
                @if($expenseByMinistry->count() > 0)
                    <div class="space-y-4">
                        @foreach($expenseByMinistry->take(5) as $ministry)
                            @php
                                $percent = $totalExpense > 0 ? ($ministry->expenses_sum_amount / $totalExpense) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $ministry->name }}
                                    </span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($ministry->expenses_sum_amount, 0, ',', ' ') }} ₴
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-red-500" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Немає витрат за цей період</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent incomes -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Останні надходження</h3>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($recentIncomes as $income)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center text-green-600 dark:text-green-400">
                                {{ $income->category?->icon ?? '💰' }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $income->category?->name ?? 'Без категорії' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $income->date->format('d.m.Y') }} • {{ $income->donor_name }}
                                </p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                            +{{ number_format($income->amount, 0, ',', ' ') }} ₴
                        </span>
                    </div>
                @empty
                    <p class="px-6 py-8 text-gray-500 dark:text-gray-400 text-center">Немає надходжень</p>
                @endforelse
            </div>
        </div>

        <!-- Recent expenses -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Останні витрати</h3>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($recentExpenses as $expense)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: {{ $expense->ministry?->color ?? '#ef4444' }}20;">
                                <svg class="w-5 h-5" style="color: {{ $expense->ministry?->color ?? '#ef4444' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ Str::limit($expense->description, 30) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $expense->date->format('d.m.Y') }} • {{ $expense->ministry?->name }}
                                </p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                            -{{ number_format($expense->amount, 0, ',', ' ') }} ₴
                        </span>
                    </div>
                @empty
                    <p class="px-6 py-8 text-gray-500 dark:text-gray-400 text-center">Немає витрат</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Top donors (if not anonymous) -->
    @if($topDonors->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Найбільші жертводавці</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @foreach($topDonors as $index => $donor)
                    <div class="text-center">
                        <div class="relative inline-block">
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center text-white text-2xl font-bold mx-auto">
                                {{ $index + 1 }}
                            </div>
                        </div>
                        <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                            {{ $donor->person?->first_name }} {{ Str::limit($donor->person?->last_name, 1, '.') }}
                        </p>
                        <p class="text-sm text-green-600 dark:text-green-400 font-semibold">
                            {{ number_format($donor->total, 0, ',', ' ') }} ₴
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function financesDashboard() {
    return {
        selectedYear: '{{ $year }}',
        selectedMonth: '{{ $month ?? "" }}',

        init() {
            this.initChart();
        },

        updatePeriod() {
            let url = '{{ route("finances.index") }}?year=' + this.selectedYear;
            if (this.selectedMonth) {
                url += '&month=' + this.selectedMonth;
            }
            window.location.href = url;
        },

        initChart() {
            const ctx = document.getElementById('financeChart');
            if (!ctx) return;

            const monthlyData = @json($monthlyData);

            // Calculate cumulative balance
            let cumulativeBalance = 0;
            const balanceData = monthlyData.map(d => {
                cumulativeBalance += d.balance;
                return cumulativeBalance;
            });

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: monthlyData.map(d => d.month),
                    datasets: [
                        {
                            label: 'Надходження',
                            data: monthlyData.map(d => d.income),
                            backgroundColor: 'rgba(34, 197, 94, 0.8)',
                            borderRadius: 4,
                            yAxisID: 'y',
                            order: 2,
                        },
                        {
                            label: 'Витрати',
                            data: monthlyData.map(d => d.expense),
                            backgroundColor: 'rgba(239, 68, 68, 0.8)',
                            borderRadius: 4,
                            yAxisID: 'y',
                            order: 2,
                        },
                        {
                            label: 'Баланс (накопичувальний)',
                            data: balanceData,
                            type: 'line',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 5,
                            pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointHoverRadius: 7,
                            yAxisID: 'y1',
                            order: 1,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    const value = context.parsed.y;
                                    const sign = context.datasetIndex === 2 && value >= 0 ? '+' : '';
                                    label += sign + value.toLocaleString('uk-UA') + ' ₴';
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Надходження / Витрати',
                                color: '#6b7280',
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('uk-UA') + ' ₴';
                                }
                            },
                            grid: {
                                drawOnChartArea: true,
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Накопичувальний баланс',
                                color: '#3b82f6',
                            },
                            ticks: {
                                color: '#3b82f6',
                                callback: function(value) {
                                    const sign = value >= 0 ? '+' : '';
                                    return sign + value.toLocaleString('uk-UA') + ' ₴';
                                }
                            },
                            grid: {
                                drawOnChartArea: false,
                            }
                        }
                    }
                }
            });
        }
    }
}
</script>
@endsection
