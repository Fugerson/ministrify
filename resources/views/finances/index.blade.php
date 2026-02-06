@extends('layouts.app')

@section('title', 'Фінанси')

@section('actions')
<div id="finance-actions" class="flex flex-wrap gap-2">
    <button type="button" onclick="window.openIncomeModal && window.openIncomeModal()"
       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Надходження
    </button>
    <button type="button" onclick="window.openExpenseModal && window.openExpenseModal()"
       class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
        </svg>
        Витрата
    </button>
    <button type="button" onclick="window.openExchangeModal && window.openExchangeModal()"
       class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        Обмін
    </button>
</div>
@endsection

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
                    <option value="" {{ empty($month) ? 'selected' : '' }}>Весь рік</option>
                    <option value="1" {{ $month == 1 ? 'selected' : '' }}>Січень</option>
                    <option value="2" {{ $month == 2 ? 'selected' : '' }}>Лютий</option>
                    <option value="3" {{ $month == 3 ? 'selected' : '' }}>Березень</option>
                    <option value="4" {{ $month == 4 ? 'selected' : '' }}>Квітень</option>
                    <option value="5" {{ $month == 5 ? 'selected' : '' }}>Травень</option>
                    <option value="6" {{ $month == 6 ? 'selected' : '' }}>Червень</option>
                    <option value="7" {{ $month == 7 ? 'selected' : '' }}>Липень</option>
                    <option value="8" {{ $month == 8 ? 'selected' : '' }}>Серпень</option>
                    <option value="9" {{ $month == 9 ? 'selected' : '' }}>Вересень</option>
                    <option value="10" {{ $month == 10 ? 'selected' : '' }}>Жовтень</option>
                    <option value="11" {{ $month == 11 ? 'selected' : '' }}>Листопад</option>
                    <option value="12" {{ $month == 12 ? 'selected' : '' }}>Грудень</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Current Balance Card - Multi-currency -->
    <div id="finance-balance" class="bg-gradient-to-br {{ $currentBalance >= 0 ? 'from-indigo-600 to-purple-600' : 'from-orange-500 to-red-500' }} rounded-xl shadow-lg p-4 sm:p-6 text-white">
        <div>
            <p class="text-indigo-100 text-sm font-medium mb-3">Поточний баланс каси</p>

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
                Еквівалент у гривні: {{ number_format($currentBalance, 0, ',', ' ') }} ₴
            </p>
            @endif

            <div class="mt-4 text-sm text-indigo-100 space-y-1 max-w-sm">
                @if($initialBalance > 0)
                <div class="flex justify-between gap-4">
                    <span>Початковий баланс{{ $initialBalanceDate ? ' (' . $initialBalanceDate->format('d.m.Y') . ')' : '' }}:</span>
                    <span class="font-medium whitespace-nowrap">{{ number_format($initialBalance, 0, ',', ' ') }} ₴</span>
                </div>
                @endif
                <div class="flex justify-between gap-4">
                    <span>+ Всього надходжень:</span>
                    <span class="font-medium text-green-200 whitespace-nowrap">{{ number_format($allTimeIncome, 0, ',', ' ') }} ₴</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span>- Всього витрат:</span>
                    <span class="font-medium text-red-200 whitespace-nowrap">{{ number_format($allTimeExpense, 0, ',', ' ') }} ₴</span>
                </div>
            </div>

            @if(count($incomeByCurrency) > 1 || count($expenseByCurrency) > 1 || !empty($incomeByCurrency['USD']) || !empty($incomeByCurrency['EUR']) || !empty($expenseByCurrency['USD']) || !empty($expenseByCurrency['EUR']))
            <div class="mt-3 pt-3 border-t border-white/20">
                <p class="text-xs text-indigo-200 mb-2">Рух за період по валютах:</p>
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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
        <!-- Total Income -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Надходження</p>
                    <p class="text-2xl sm:text-3xl font-bold mt-1">{{ number_format($totalIncome, 0, ',', ' ') }} ₴</p>
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
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Витрати</p>
                    <p class="text-2xl sm:text-3xl font-bold mt-1">{{ number_format($totalExpense, 0, ',', ' ') }} ₴</p>
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
        <div class="bg-gradient-to-br {{ $periodBalance >= 0 ? 'from-blue-500 to-blue-600' : 'from-orange-500 to-orange-600' }} rounded-xl shadow-lg p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Результат за період</p>
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
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Немає витрат за цей період</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
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
                                    {{ $income->date->format('d.m.Y') }} • {{ $income->payment_method_label ?? 'Готівка' }}
                                </p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                            +{{ \App\Helpers\CurrencyHelper::format($income->amount, $income->currency ?? 'UAH') }}
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
                            <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: {{ $expense->ministry?->color ?? '#ef4444' }}30;">
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
                            -{{ \App\Helpers\CurrencyHelper::format($expense->amount, $expense->currency ?? 'UAH') }}
                        </span>
                    </div>
                @empty
                    <p class="px-6 py-8 text-gray-500 dark:text-gray-400 text-center">Немає витрат</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Activity Feed & Payment Methods -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Activity Feed -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Стрічка активності</h3>
            </div>
            <div class="p-4 max-h-80 overflow-y-auto">
                <div class="space-y-3">
                    @forelse($activityFeed as $activity)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 mt-1">
                            @if($activity->direction === 'in')
                            <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                </svg>
                            </div>
                            @else
                            <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                </svg>
                            </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-white">
                                @if($activity->direction === 'in')
                                    <span class="font-medium text-green-600 dark:text-green-400">+{{ number_format($activity->amount, 0, ',', ' ') }} ₴</span>
                                    <span class="text-gray-500 dark:text-gray-400">{{ $activity->category?->name ?? 'Надходження' }}</span>
                                @else
                                    <span class="font-medium text-red-600 dark:text-red-400">-{{ number_format($activity->amount, 0, ',', ' ') }} ₴</span>
                                    <span class="text-gray-500 dark:text-gray-400">{{ Str::limit($activity->description, 25) }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                {{ $activity->created_at->diffForHumans() }}
                                @if($activity->direction === 'in' && $activity->person)
                                    • {{ $activity->person->first_name }}
                                @endif
                            </p>
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">Немає активності</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Способи оплати</h3>
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
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">Немає даних</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Active Campaigns -->
    @if($activeCampaigns->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Активні збори</h3>
            <a href="{{ route('donations.index') }}" class="text-sm text-primary-600 hover:text-primary-500">Усі →</a>
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
                    {{ $campaign->days_remaining > 0 ? 'Залишилось ' . $campaign->days_remaining . ' днів' : 'Завершено' }}
                </p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
function financesDashboard() {
    return {
        selectedYear: '{{ $year }}',
        selectedMonth: '{{ $month ?? "" }}',

        init() {
            this.initChart();
            this.initPaymentMethodsChart();
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
        },

        initPaymentMethodsChart() {
            const ctx = document.getElementById('paymentMethodsChart');
            if (!ctx) return;

            const paymentMethods = @json($paymentMethods);
            if (!paymentMethods || paymentMethods.length === 0) return;

            const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: paymentMethods.map(pm => pm.label),
                    datasets: [{
                        data: paymentMethods.map(pm => pm.total),
                        backgroundColor: paymentMethods.map((_, i) => colors[i % colors.length]),
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percent = Math.round((value / total) * 100);
                                    return context.label + ': ' + value.toLocaleString('uk-UA') + ' ₴ (' + percent + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    }
}
</script>
</div><!-- /finance-content -->

<!-- Income Modal -->
<div x-data="incomeModal()" x-cloak>
    <div x-show="modalOpen"
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-black/50" @click="modalOpen = false"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg relative"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 @click.stop>
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Додати надходження</h3>
                    <button @click="modalOpen = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submit()" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Сума *</label>
                        <div class="flex gap-2">
                            <input type="number" x-model="formData.amount" step="0.01" min="0.01" required
                                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                   :class="{ 'border-red-500': errors.amount }">
                            <select x-model="formData.currency"
                                    class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                @foreach($enabledCurrencies ?? ['UAH', 'USD', 'EUR'] as $curr)
                                    <option value="{{ $curr }}">{{ $curr }}</option>
                                @endforeach
                            </select>
                        </div>
                        <p class="text-red-500 text-sm mt-1" x-show="errors.amount" x-text="errors.amount?.[0]"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата *</label>
                        <input type="date" x-model="formData.date" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія *</label>
                        <select x-model="formData.category_id" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="">Оберіть категорію</option>
                            @foreach($incomeCategories ?? [] as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->icon ?? '💰' }} {{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Спосіб оплати *</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                                   :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': formData.payment_method === 'cash' }">
                                <input type="radio" x-model="formData.payment_method" value="cash" class="sr-only">
                                <span class="text-sm text-gray-700 dark:text-gray-300">💵 Готівка</span>
                            </label>
                            <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                                   :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': formData.payment_method === 'card' }">
                                <input type="radio" x-model="formData.payment_method" value="card" class="sr-only">
                                <span class="text-sm text-gray-700 dark:text-gray-300">💳 Картка</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нотатки</label>
                        <textarea x-model="formData.notes" rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" @click="modalOpen = false"
                                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl">
                            Скасувати
                        </button>
                        <button type="submit" :disabled="loading"
                                class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl disabled:opacity-50">
                            <span x-show="!loading">Додати</span>
                            <span x-show="loading">Збереження...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Expense Modal -->
<div x-data="expenseModal()" x-cloak>
    <div x-show="modalOpen"
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-black/50" @click="modalOpen = false"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg relative"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 @click.stop>
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Додати витрату</h3>
                    <button @click="modalOpen = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submit()" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Сума *</label>
                        <div class="flex gap-2">
                            <input type="number" x-model="formData.amount" step="0.01" min="0.01" required
                                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                   :class="{ 'border-red-500': errors.amount }">
                            <select x-model="formData.currency"
                                    class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                @foreach($enabledCurrencies ?? ['UAH', 'USD', 'EUR'] as $curr)
                                    <option value="{{ $curr }}">{{ $curr }}</option>
                                @endforeach
                            </select>
                        </div>
                        <p class="text-red-500 text-sm mt-1" x-show="errors.amount" x-text="errors.amount?.[0]"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис *</label>
                        <input type="text" x-model="formData.description" required maxlength="255"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата *</label>
                        <input type="date" x-model="formData.date" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Команда</label>
                        <select x-model="formData.ministry_id"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="">Без команди</option>
                            @foreach($ministries ?? [] as $ministry)
                                <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія</label>
                        <select x-model="formData.category_id"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="">Без категорії</option>
                            @foreach($expenseCategories ?? [] as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->icon ?? '💸' }} {{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Спосіб оплати</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                                   :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': formData.payment_method === 'cash' }">
                                <input type="radio" x-model="formData.payment_method" value="cash" class="sr-only">
                                <span class="text-sm text-gray-700 dark:text-gray-300">💵 Готівка</span>
                            </label>
                            <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                                   :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': formData.payment_method === 'card' }">
                                <input type="radio" x-model="formData.payment_method" value="card" class="sr-only">
                                <span class="text-sm text-gray-700 dark:text-gray-300">💳 Картка</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" @click="modalOpen = false"
                                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl">
                            Скасувати
                        </button>
                        <button type="submit" :disabled="loading"
                                class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl disabled:opacity-50">
                            <span x-show="!loading">Додати</span>
                            <span x-show="loading">Збереження...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Exchange Modal -->
<div x-data="exchangeModal()" x-cloak>
    <div x-show="modalOpen"
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-black/50" @click="modalOpen = false"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg relative"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 @click.stop>
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Обмін валюти</h3>
                    <button @click="modalOpen = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submit()" class="p-6 space-y-4">
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                        <label class="block text-sm font-medium text-red-700 dark:text-red-300 mb-2">Віддаєте</label>
                        <div class="flex gap-2">
                            <input type="number" x-model="formData.from_amount" @input="calculate()" step="0.01" min="0.01" required
                                   class="flex-1 px-4 py-2 border border-red-200 dark:border-red-800 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <select x-model="formData.from_currency" @change="updateRate()"
                                    class="w-24 px-2 py-2 border border-red-200 dark:border-red-800 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                @foreach($enabledCurrencies ?? ['UAH', 'USD', 'EUR'] as $curr)
                                    <option value="{{ $curr }}">{{ $curr }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 flex items-center justify-center gap-2 text-sm">
                        <span>1</span>
                        <span x-text="formData.from_currency !== 'UAH' ? formData.from_currency : formData.to_currency" class="font-medium"></span>
                        <span>=</span>
                        <input type="number" x-model="rate" @input="calculate()" step="0.0001" min="0.0001"
                               class="w-24 px-2 py-1 text-center border border-amber-200 dark:border-amber-800 rounded-lg bg-white dark:bg-gray-700">
                        <span class="font-medium">₴</span>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <label class="block text-sm font-medium text-green-700 dark:text-green-300 mb-2">Отримуєте</label>
                        <div class="flex gap-2">
                            <input type="number" x-model="formData.to_amount" step="0.01" min="0.01" required readonly
                                   class="flex-1 px-4 py-2 border border-green-200 dark:border-green-800 rounded-lg bg-gray-50 dark:bg-gray-600 text-gray-900 dark:text-white">
                            <select x-model="formData.to_currency" @change="updateRate()"
                                    class="w-24 px-2 py-2 border border-green-200 dark:border-green-800 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                @foreach($enabledCurrencies ?? ['UAH', 'USD', 'EUR'] as $curr)
                                    <option value="{{ $curr }}">{{ $curr }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата</label>
                        <input type="date" x-model="formData.date" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нотатки</label>
                        <input type="text" x-model="formData.notes" maxlength="500"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="Де обміняли...">
                    </div>
                    <div x-show="formData.from_currency === formData.to_currency" class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3">
                        <p class="text-sm text-red-700 dark:text-red-300">Оберіть різні валюти</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" @click="modalOpen = false"
                                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                            Скасувати
                        </button>
                        <button type="submit" :disabled="loading || formData.from_currency === formData.to_currency"
                                class="px-6 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-lg disabled:opacity-50">
                            <span x-show="!loading">Обміняти</span>
                            <span x-show="loading">Обмін...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.incomeModal = function() {
    return {
        modalOpen: false,
        loading: false,
        errors: {},
        formData: {
            amount: '',
            currency: 'UAH',
            category_id: '',
            date: new Date().toISOString().split('T')[0],
            payment_method: 'cash',
            notes: '',
            is_anonymous: true
        },
        init() {
            window.openIncomeModal = () => this.openModal();
        },
        openModal() {
            this.formData = {
                amount: '',
                currency: 'UAH',
                category_id: '',
                date: new Date().toISOString().split('T')[0],
                payment_method: 'cash',
                notes: '',
                is_anonymous: true
            };
            this.errors = {};
            this.modalOpen = true;
        },
        async submit() {
            this.loading = true;
            this.errors = {};
            try {
                const response = await fetch('/finances/incomes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.formData)
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    this.modalOpen = false;
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 500);
                } else if (response.status === 422) {
                    this.errors = data.errors || {};
                } else {
                    showToast('error', data.message || 'Помилка збереження');
                }
            } catch (e) {
                showToast('error', 'Помилка з\'єднання');
            } finally {
                this.loading = false;
            }
        }
    };
};

window.expenseModal = function() {
    return {
        modalOpen: false,
        loading: false,
        errors: {},
        formData: {
            amount: '',
            currency: 'UAH',
            description: '',
            category_id: '',
            ministry_id: '',
            date: new Date().toISOString().split('T')[0],
            payment_method: 'cash'
        },
        init() {
            window.openExpenseModal = () => this.openModal();
        },
        openModal() {
            this.formData = {
                amount: '',
                currency: 'UAH',
                description: '',
                category_id: '',
                ministry_id: '',
                date: new Date().toISOString().split('T')[0],
                payment_method: 'cash'
            };
            this.errors = {};
            this.modalOpen = true;
        },
        async submit() {
            this.loading = true;
            this.errors = {};
            try {
                const response = await fetch('/finances/expenses', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.formData)
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    this.modalOpen = false;
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 500);
                } else if (response.status === 422) {
                    this.errors = data.errors || {};
                    if (data.message) showToast('error', data.message);
                } else {
                    showToast('error', data.message || 'Помилка збереження');
                }
            } catch (e) {
                showToast('error', 'Помилка з\'єднання');
            } finally {
                this.loading = false;
            }
        }
    };
};

window.exchangeModal = function() {
    return {
        modalOpen: false,
        loading: false,
        rate: {{ $exchangeRates['USD'] ?? 41 }},
        nbuRates: @json($exchangeRates ?? ['USD' => 41, 'EUR' => 45]),
        formData: {
            from_currency: 'USD',
            to_currency: 'UAH',
            from_amount: '',
            to_amount: '',
            date: new Date().toISOString().split('T')[0],
            notes: ''
        },
        init() {
            window.openExchangeModal = () => this.openModal();
        },
        openModal() {
            this.formData = {
                from_currency: 'USD',
                to_currency: 'UAH',
                from_amount: '',
                to_amount: '',
                date: new Date().toISOString().split('T')[0],
                notes: ''
            };
            this.updateRate();
            this.modalOpen = true;
        },
        updateRate() {
            const from = this.formData.from_currency;
            const to = this.formData.to_currency;
            if (from !== 'UAH' && to === 'UAH') {
                this.rate = this.nbuRates[from] || 1;
            } else if (from === 'UAH' && to !== 'UAH') {
                this.rate = this.nbuRates[to] || 1;
            } else {
                this.rate = 1;
            }
            this.calculate();
        },
        calculate() {
            const from = this.formData.from_currency;
            const to = this.formData.to_currency;
            const amount = parseFloat(this.formData.from_amount) || 0;
            if (amount <= 0) {
                this.formData.to_amount = '';
                return;
            }
            if (from !== 'UAH' && to === 'UAH') {
                this.formData.to_amount = (amount * this.rate).toFixed(2);
            } else if (from === 'UAH' && to !== 'UAH') {
                this.formData.to_amount = (amount / this.rate).toFixed(2);
            } else {
                this.formData.to_amount = (amount * this.rate).toFixed(2);
            }
        },
        async submit() {
            this.loading = true;
            try {
                const response = await fetch('/finances/exchange', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.formData)
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    this.modalOpen = false;
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast('error', data.message || 'Помилка');
                }
            } catch (e) {
                showToast('error', 'Помилка з\'єднання');
            } finally {
                this.loading = false;
            }
        }
    };
};
</script>
@endpush
@endsection
