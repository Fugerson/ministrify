@extends('layouts.app')

@section('title', __('app.finances'))

@section('content')
@include('finances.partials.tabs')

<div id="finance-content">
<div x-data="financesDashboard()" class="space-y-6">
    <!-- Period selector -->
    <div id="finance-period" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
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
                    <option value="" {{ empty($month) ? 'selected' : '' }}>{{ __('app.all_year') }}</option>
                    <option value="1" {{ $month == 1 ? 'selected' : '' }}>{{ __('app.january') }}</option>
                    <option value="2" {{ $month == 2 ? 'selected' : '' }}>{{ __('app.february') }}</option>
                    <option value="3" {{ $month == 3 ? 'selected' : '' }}>{{ __('app.march') }}</option>
                    <option value="4" {{ $month == 4 ? 'selected' : '' }}>{{ __('app.april') }}</option>
                    <option value="5" {{ $month == 5 ? 'selected' : '' }}>{{ __('app.may') }}</option>
                    <option value="6" {{ $month == 6 ? 'selected' : '' }}>{{ __('app.june') }}</option>
                    <option value="7" {{ $month == 7 ? 'selected' : '' }}>{{ __('app.july') }}</option>
                    <option value="8" {{ $month == 8 ? 'selected' : '' }}>{{ __('app.august') }}</option>
                    <option value="9" {{ $month == 9 ? 'selected' : '' }}>{{ __('app.september') }}</option>
                    <option value="10" {{ $month == 10 ? 'selected' : '' }}>{{ __('app.october') }}</option>
                    <option value="11" {{ $month == 11 ? 'selected' : '' }}>{{ __('app.november') }}</option>
                    <option value="12" {{ $month == 12 ? 'selected' : '' }}>{{ __('app.december') }}</option>
                </select>
            </div>
            <!-- Link to transactions -->
            @if(auth()->user()->canCreate('finances'))
            <a href="{{ route('finances.transactions') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('app.add_transaction') }}
            </a>
            @endif
        </div>
    </div>

    <!-- Current Balance Card - Multi-currency -->
    <div id="finance-balance" class="bg-gradient-to-br {{ $currentBalance >= 0 ? 'from-indigo-600 to-purple-600' : 'from-orange-500 to-red-500' }} rounded-xl shadow-lg p-4 sm:p-6 text-white">
        <div>
            <p class="text-indigo-100 text-sm font-medium mb-3">{{ __('app.current_cash_balance') }}</p>

            @php
                $currencySymbols = ['UAH' => '₴', 'USD' => '$', 'EUR' => '€'];
                $hasMultipleCurrencies = count($balancesByCurrency) > 1 || isset($balancesByCurrency['USD']) || isset($balancesByCurrency['EUR']);
            @endphp

            <!-- Show balance for each currency -->
            <div class="flex flex-wrap gap-4 items-baseline">
                @foreach(['UAH', 'USD', 'EUR'] as $code)
                    @if(isset($balancesByCurrency[$code]) && ($balancesByCurrency[$code] != 0 || $code === 'UAH'))
                        @php
                            $balance = $balancesByCurrency[$code];
                            $symbol = $currencySymbols[$code];
                        @endphp
                        <div class="@if($code === 'UAH') @else bg-white/10 rounded-lg px-4 py-2 @endif">
                            @if($code === 'UAH')
                                <p class="text-2xl sm:text-3xl md:text-4xl font-bold">{{ number_format($balance, 0, ',', ' ') }} {{ $symbol }}</p>
                            @else
                                <span class="text-2xl font-bold {{ $balance >= 0 ? '' : 'text-red-200' }}">
                                    {{ $balance >= 0 ? '' : '-' }}{{ $symbol }}{{ number_format(abs($balance), 0, ',', ' ') }}
                                </span>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>

            @if($hasMultipleCurrencies)
            <p class="text-xs text-indigo-200 mt-2">
                {{ __('app.equivalent_uah') }} {{ number_format($currentBalance, 0, ',', ' ') }} ₴
            </p>
            @endif

            <div class="mt-4 text-sm text-indigo-100 space-y-1 max-w-sm">
                @if($initialBalance > 0)
                <div class="flex justify-between gap-4">
                    <span>{{ __('app.initial_balance') }}{{ $initialBalanceDate ? ' (' . $initialBalanceDate->format('d.m.Y') . ')' : '' }}:</span>
                    <span class="font-medium whitespace-nowrap">{{ number_format($initialBalance, 0, ',', ' ') }} ₴</span>
                </div>
                @endif
                <div class="flex justify-between gap-4">
                    <span>{{ __('app.total_income_label') }}</span>
                    <span class="font-medium text-green-200 whitespace-nowrap">{{ number_format($allTimeIncome, 0, ',', ' ') }} ₴</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span>{{ __('app.total_expense_label') }}</span>
                    <span class="font-medium text-red-200 whitespace-nowrap">{{ number_format($allTimeExpense, 0, ',', ' ') }} ₴</span>
                </div>
            </div>

            @if(count($incomeByCurrency) > 1 || count($expenseByCurrency) > 1 || !empty($incomeByCurrency['USD']) || !empty($incomeByCurrency['EUR']) || !empty($expenseByCurrency['USD']) || !empty($expenseByCurrency['EUR']))
            <div class="mt-3 pt-3 border-t border-white/20">
                <p class="text-xs text-indigo-200 mb-2">{{ __('app.period_flow_by_currency') }}</p>
                <div class="flex flex-wrap gap-3">
                    @foreach(['UAH' => '₴', 'USD' => '$', 'EUR' => '€'] as $code => $symbol)
                        @php
                            $income = $incomeByCurrency[$code] ?? 0;
                            $expense = $expenseByCurrency[$code] ?? 0;
                        @endphp
                        @if($income > 0 || $expense > 0)
                        <div class="bg-white/10 rounded-lg px-3 py-1.5">
                            <span class="font-medium">{{ $symbol }}</span>
                            <span class="text-green-200">+{{ number_format($income, 0, ',', ' ') }}</span>
                            <span class="text-red-200">-{{ number_format($expense, 0, ',', ' ') }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @if(!$initialBalance && !$initialBalanceDate)
        <div class="mt-4 pt-4 border-t border-white/20">
            <a href="{{ route('settings.index') }}?tab=finance" class="text-sm text-white/80 hover:text-white inline-flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('app.set_initial_balance') }}
            </a>
        </div>
        @endif
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
        <!-- Total Income -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">{{ __('app.income_action') }}</p>
                    <p class="text-2xl sm:text-3xl font-bold mt-1">{{ number_format($totalIncome, 0, ',', ' ') }} ₴</p>
                    <p class="text-green-100 text-sm mt-2">
                        {{ $periodLabel }}
                        @if($yearComparison['growth']['income'] != 0)
                            <span class="{{ $yearComparison['growth']['income'] > 0 ? '' : 'text-red-200' }}">
                                ({{ $yearComparison['growth']['income'] > 0 ? '+' : '' }}{{ $yearComparison['growth']['income'] }}%)
                            </span>
                        @endif
                    </p>
                </div>
                <div class="p-3 bg-green-400 bg-opacity-30 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Expense -->
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">{{ __('app.expenses') }}</p>
                    <p class="text-2xl sm:text-3xl font-bold mt-1">{{ number_format($totalExpense, 0, ',', ' ') }} ₴</p>
                    <p class="text-red-100 text-sm mt-2">
                        {{ $periodLabel }}
                        @if($yearComparison['growth']['expense'] != 0)
                            <span class="{{ $yearComparison['growth']['expense'] < 0 ? 'text-green-200' : '' }}">
                                ({{ $yearComparison['growth']['expense'] > 0 ? '+' : '' }}{{ $yearComparison['growth']['expense'] }}%)
                            </span>
                        @endif
                    </p>
                </div>
                <div class="p-3 bg-red-400 bg-opacity-30 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Period Balance -->
        <div class="bg-gradient-to-br {{ $periodBalance >= 0 ? 'from-blue-500 to-blue-600' : 'from-orange-500 to-orange-600' }} rounded-xl shadow-lg p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">{{ __('app.period_result') }}</p>
                    <p class="text-2xl sm:text-3xl font-bold mt-1">{{ $periodBalance >= 0 ? '+' : '' }}{{ number_format($periodBalance, 0, ',', ' ') }} ₴</p>
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
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="chartTitle">{{ __('app.chart_dynamics', ['year' => $year]) }}</h3>
            <div class="flex rounded-lg bg-gray-100 dark:bg-gray-700 p-0.5">
                <button @click="switchChartPeriod('month')" :class="chartPeriod === 'month' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                    class="px-3 py-1 text-sm font-medium rounded-md transition-all">{{ __('app.period_month') }}</button>
                <button @click="switchChartPeriod('quarter')" :class="chartPeriod === 'quarter' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                    class="px-3 py-1 text-sm font-medium rounded-md transition-all">{{ __('app.period_quarter') }}</button>
                <button @click="switchChartPeriod('year')" :class="chartPeriod === 'year' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                    class="px-3 py-1 text-sm font-medium rounded-md transition-all">{{ __('app.period_year') }}</button>
                <button @click="switchChartPeriod('all')" :class="chartPeriod === 'all' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                    class="px-3 py-1 text-sm font-medium rounded-md transition-all">{{ __('app.period_all_time') }}</button>
            </div>
        </div>
        <div class="h-64 relative">
            <div x-show="chartLoading" class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-gray-800/50 z-10">
                <svg class="animate-spin h-6 w-6 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>
            <canvas id="financeChart"></canvas>
        </div>
    </div>

    <!-- Analytics grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Income by category -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.income_by_category') }}</h3>
                <a href="{{ route('finances.transactions', ['filter' => 'income']) }}" class="text-sm text-primary-600 hover:text-primary-500">{{ __('app.view_all') }}</a>
            </div>
            <div class="p-6">
                @if($incomeByCategory->count() > 0)
                    <div class="space-y-4">
                        @foreach($incomeByCategory->take(5) as $cat)
                            @php
                                $percent = $totalIncome > 0 ? ($cat->total_amount / $totalIncome) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $cat->icon ?? '💰' }} {{ $cat->name }}
                                    </span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($cat->total_amount, 0, ',', ' ') }} ₴
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-green-500" style="width: {{ $percent }}%; background-color: {{ $cat->color }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">{{ __('app.no_income_this_period') }}</p>
                @endif
            </div>
        </div>

        <!-- Expense by ministry -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.expense_by_ministry') }}</h3>
                <a href="{{ route('finances.transactions', ['filter' => 'expense']) }}" class="text-sm text-primary-600 hover:text-primary-500">{{ __('app.view_all') }}</a>
            </div>
            <div class="p-6">
                @if($expenseByMinistry->count() > 0)
                    <div class="space-y-4">
                        @foreach($expenseByMinistry->take(5) as $ministry)
                            @php
                                $percent = $totalExpense > 0 ? ($ministry->total_expense / $totalExpense) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $ministry->name }}
                                    </span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($ministry->total_expense, 0, ',', ' ') }} ₴
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-red-500" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">{{ __('app.no_expense_this_period') }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Payment Methods & Campaigns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Payment Methods -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.payment_methods') }}</h3>
            </div>
            <div class="p-6">
                @if($paymentMethods->count() > 0)
                <div class="relative">
                    <canvas id="paymentMethodsChart" class="max-h-40"></canvas>
                </div>
                <div class="mt-4 space-y-2">
                    @php
                        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
                        $totalPayments = $paymentMethods->sum('total');
                    @endphp
                    @foreach($paymentMethods as $index => $pm)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $colors[$index % count($colors)] }}"></span>
                            <span class="text-gray-700 dark:text-gray-300">{{ $pm['label'] }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-medium text-gray-900 dark:text-white">{{ number_format($pm['total'], 0, ',', ' ') }} ₴</span>
                            <span class="text-gray-400 dark:text-gray-500 ml-1">({{ $totalPayments > 0 ? round($pm['total'] / $totalPayments * 100) : 0 }}%)</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">{{ __('app.no_data') }}</p>
                @endif
            </div>
        </div>

        <!-- Active Campaigns -->
        <div class="lg:col-span-2">
            @if($activeCampaigns->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.active_campaigns') }}</h3>
                    <a href="{{ route('donations.index') }}" class="text-sm text-primary-600 hover:text-primary-500">{{ __('app.view_all') }}</a>
                </div>
                <div class="space-y-4">
                    @foreach($activeCampaigns as $campaign)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $campaign->name }}</span>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ number_format($campaign->raised_amount, 0, ',', ' ') }} / {{ number_format($campaign->goal_amount, 0, ',', ' ') }} ₴
                                </span>
                                <span class="text-sm font-semibold {{ $campaign->progress_percent >= 100 ? 'text-green-600' : 'text-primary-600' }}">
                                    {{ $campaign->progress_percent }}%
                                </span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                            <div class="h-3 rounded-full transition-all duration-500 {{ $campaign->progress_percent >= 100 ? 'bg-green-500' : 'bg-gradient-to-r from-primary-500 to-primary-400' }}"
                                 style="width: {{ min($campaign->progress_percent, 100) }}%"></div>
                        </div>
                        @if($campaign->days_remaining !== null)
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            {{ $campaign->days_remaining > 0 ? __('app.days_remaining', ['days' => $campaign->days_remaining]) : __('app.campaign_completed') }}
                        </p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
function financesDashboard() {
    const monthNames = ['', @js( __('app.january') ), @js( __('app.february') ), @js( __('app.march') ), @js( __('app.april') ), @js( __('app.may') ), @js( __('app.june') ), @js( __('app.july') ), @js( __('app.august') ), @js( __('app.september') ), @js( __('app.october') ), @js( __('app.november') ), @js( __('app.december') )];

    return {
        selectedYear: '{{ $year }}',
        selectedMonth: '{{ $month ?? "" }}',
        chartPeriod: 'year',
        chartTitle: @js( __('app.chart_dynamics', ['year' => $year]) ),
        chartLoading: false,
        chartInstance: null,

        init() {
            this.initChart(@json($monthlyData));
            this.initPaymentMethodsChart();
        },

        updatePeriod() {
            let url = '{{ route("finances.index") }}?year=' + this.selectedYear;
            if (this.selectedMonth) {
                url += '&month=' + this.selectedMonth;
            }
            Livewire.navigate(url);
        },

        switchChartPeriod(period) {
            if (this.chartPeriod === period) return;
            this.chartPeriod = period;
            this.chartLoading = true;

            const year = this.selectedYear;
            const month = this.selectedMonth || {{ $month ?? 'new Date().getMonth() + 1' }};

            let url = '{{ route("finances.chart-data") }}?period=' + period + '&year=' + year;
            if (month) url += '&month=' + month;

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    this.updateChartTitle(period, year, month);
                    if (this.chartInstance) {
                        this.chartInstance.data.labels = data.labels;
                        this.chartInstance.data.datasets[0].data = data.income;
                        this.chartInstance.data.datasets[1].data = data.expense;
                        this.chartInstance.update();
                    }
                    this.chartLoading = false;
                })
                .catch(() => { this.chartLoading = false; });
        },

        updateChartTitle(period, year, month) {
            switch(period) {
                case 'month': this.chartTitle = monthNames[month] + ' ' + year; break;
                case 'quarter': this.chartTitle = @js( __('app.chart_dynamics', ['year' => '']) ) + year; break;
                case 'year': this.chartTitle = @js( __('app.chart_dynamics', ['year' => '']) ) + year; break;
                case 'all': this.chartTitle = @js( __('app.period_all_time') ); break;
            }
        },

        initChart(data) {
            const ctx = document.getElementById('financeChart');
            if (!ctx) return;

            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)';

            this.chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: @js( __('app.income_action') ),
                            data: data.income,
                            backgroundColor: 'rgba(34, 197, 94, 0.7)',
                            borderColor: 'rgb(34, 197, 94)',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: @js( __('app.expenses') ),
                            data: data.expense,
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgb(239, 68, 68)',
                            borderWidth: 1,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: isDark ? '#9ca3af' : '#6b7280', usePointStyle: true, padding: 20 }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.dataset.label + ': ' + new Intl.NumberFormat('uk-UA').format(ctx.raw) + ' ₴';
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: isDark ? '#9ca3af' : '#6b7280' } },
                        y: {
                            grid: { color: gridColor },
                            ticks: {
                                color: isDark ? '#9ca3af' : '#6b7280',
                                callback: v => new Intl.NumberFormat('uk-UA', { notation: 'compact' }).format(v) + ' ₴'
                            }
                        }
                    }
                }
            });
        },

        initPaymentMethodsChart() {
            const ctx = document.getElementById('paymentMethodsChart');
            if (!ctx) return;

            const data = @json($paymentMethods);
            const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(d => d.label),
                    datasets: [{
                        data: data.map(d => d.total),
                        backgroundColor: colors.slice(0, data.length)
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    cutout: '65%'
                }
            });
        }
    };
}
</script>
</div>
@endsection
