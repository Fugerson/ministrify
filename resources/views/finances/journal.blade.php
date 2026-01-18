@extends('layouts.app')

@section('title', 'Фінансовий журнал')

@section('actions')
<div class="flex items-center space-x-2">
    <a href="{{ route('finances.journal.export', request()->query()) }}"
       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Експорт CSV
    </a>
    <a href="{{ route('finances.index') }}"
       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
        </svg>
        Dashboard
    </a>
</div>
@endsection

@section('content')
<div x-data="journalApp()" class="space-y-4">
    <!-- Period Selector -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Quick Period Buttons -->
            <div class="flex flex-wrap gap-2">
                @foreach(['today' => 'Сьогодні', 'week' => 'Тиждень', 'month' => 'Місяць', 'quarter' => 'Квартал', 'year' => 'Рік'] as $key => $label)
                <button type="button"
                        @click="setPeriod('{{ $key }}')"
                        class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $period === $key ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    {{ $label }}
                </button>
                @endforeach
                <button type="button"
                        @click="showCustomDates = !showCustomDates"
                        class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $period === 'custom' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Період
                </button>
            </div>

            <!-- Date Display -->
            <div class="flex-1 text-center">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $startDate->format('d.m.Y') }} — {{ $endDate->format('d.m.Y') }}
                </span>
            </div>

            <!-- Balance Info -->
            <div class="text-right">
                <span class="text-sm text-gray-500 dark:text-gray-400">Баланс:</span>
                <span class="ml-1 font-semibold {{ $currentBalance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ number_format($currentBalance, 0, ',', ' ') }} ₴
                </span>
            </div>
        </div>

        <!-- Custom Date Range -->
        <div x-show="showCustomDates" x-collapse class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="period" value="custom">
                @foreach(request()->except(['period', 'start_date', 'end_date', 'page']) as $key => $value)
                    @if($value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Від</label>
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                           class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">До</label>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                           class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                </div>
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Застосувати
                </button>
            </form>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="period" value="{{ $period }}">
            @if($period === 'custom')
            <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
            <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
            @endif

            <!-- Search -->
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Пошук..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
            </div>

            <!-- Direction Filter -->
            <select name="direction" onchange="this.form.submit()"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                <option value="">Всі операції</option>
                <option value="in" {{ request('direction') === 'in' ? 'selected' : '' }}>Надходження</option>
                <option value="out" {{ request('direction') === 'out' ? 'selected' : '' }}>Витрати</option>
            </select>

            <!-- Category Filter -->
            <select name="category_id" onchange="this.form.submit()"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                <option value="">Всі категорії</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->icon }} {{ $category->name }}
                </option>
                @endforeach
            </select>

            <!-- Ministry Filter -->
            <select name="ministry_id" onchange="this.form.submit()"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                <option value="">Всі команди</option>
                @foreach($ministries as $ministry)
                <option value="{{ $ministry->id }}" {{ request('ministry_id') == $ministry->id ? 'selected' : '' }}>
                    {{ $ministry->name }}
                </option>
                @endforeach
            </select>

            <!-- Payment Method Filter -->
            <select name="payment_method" onchange="this.form.submit()"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                <option value="">Спосіб оплати</option>
                @foreach(\App\Models\Transaction::PAYMENT_METHODS as $key => $label)
                <option value="{{ $key }}" {{ request('payment_method') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <!-- Person Filter -->
            <select name="person_id" onchange="this.form.submit()"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                <option value="">Всі особи</option>
                @foreach($people as $person)
                <option value="{{ $person->id }}" {{ request('person_id') == $person->id ? 'selected' : '' }}>
                    {{ $person->first_name }} {{ $person->last_name }}
                </option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                Фільтр
            </button>

            @if(request()->hasAny(['search', 'direction', 'category_id', 'ministry_id', 'payment_method', 'person_id']))
            <a href="{{ route('finances.journal', ['period' => $period, 'start_date' => $period === 'custom' ? $startDate->format('Y-m-d') : null, 'end_date' => $period === 'custom' ? $endDate->format('Y-m-d') : null]) }}"
               class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 text-sm font-medium">
                Скинути
            </a>
            @endif
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Баланс на початок</p>
            <p class="text-xl font-bold {{ $balanceBeforePeriod >= 0 ? 'text-gray-900 dark:text-white' : 'text-red-600' }}">
                {{ number_format($balanceBeforePeriod, 0, ',', ' ') }} ₴
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Надходження</p>
            <p class="text-xl font-bold text-green-600 dark:text-green-400">
                +{{ number_format($periodTotals['income'], 0, ',', ' ') }} ₴
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Витрати</p>
            <p class="text-xl font-bold text-red-600 dark:text-red-400">
                -{{ number_format($periodTotals['expense'], 0, ',', ' ') }} ₴
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Баланс на кінець</p>
            <p class="text-xl font-bold {{ ($balanceBeforePeriod + $periodTotals['balance']) >= 0 ? 'text-primary-600 dark:text-primary-400' : 'text-red-600' }}">
                {{ number_format($balanceBeforePeriod + $periodTotals['balance'], 0, ',', ' ') }} ₴
            </p>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Дата</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Опис</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Категорія</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Команда / Особа</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Сума</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Баланс</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16"></th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @php
                        // Calculate running balance from end of period backwards
                        $runningBalance = $balanceBeforePeriod + $periodTotals['balance'];
                        $transactionsWithBalance = $transactions->map(function ($t) use (&$runningBalance) {
                            $balance = $runningBalance;
                            if ($t->direction === 'in') {
                                $runningBalance -= $t->amount;
                            } else {
                                $runningBalance += $t->amount;
                            }
                            return ['transaction' => $t, 'balance' => $balance];
                        });
                    @endphp

                    @forelse($transactionsWithBalance as $item)
                        @php $t = $item['transaction']; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors"
                            @click="openDetails({{ $t->id }})"
                            x-data="{ details: false }">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $t->date->format('d.m.Y') }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $t->date->format('l') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900 dark:text-white">{{ Str::limit($t->description, 40) }}</div>
                                @if($t->payment_method)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ \App\Models\Transaction::PAYMENT_METHODS[$t->payment_method] ?? $t->payment_method }}
                                </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($t->category)
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium"
                                      style="background-color: {{ $t->category->color }}20; color: {{ $t->category->color }}">
                                    {{ $t->category->icon }} {{ $t->category->name }}
                                </span>
                                @else
                                <span class="text-gray-400 dark:text-gray-500 text-sm">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($t->direction === 'out' && $t->ministry)
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $t->ministry->name }}</div>
                                @elseif($t->direction === 'in' && $t->person)
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $t->person->first_name }} {{ Str::limit($t->person->last_name, 1, '.') }}</div>
                                @elseif($t->is_anonymous)
                                    <span class="text-gray-400 dark:text-gray-500 text-sm italic">Анонімно</span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500 text-sm">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <span class="text-sm font-semibold {{ $t->direction === 'in' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $t->direction === 'in' ? '+' : '-' }}{{ number_format($t->amount, 0, ',', ' ') }}
                                    <span class="text-xs">{{ $t->currency ?? '₴' }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <span class="text-sm font-medium {{ $item['balance'] >= 0 ? 'text-gray-900 dark:text-white' : 'text-red-600' }}">
                                    {{ number_format($item['balance'], 0, ',', ' ') }} ₴
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    @if($t->attachments && $t->attachments->count() > 0)
                                    <span class="inline-flex items-center text-gray-400 dark:text-gray-500" title="{{ $t->attachments->count() }} файл(ів)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        <span class="text-xs ml-0.5">{{ $t->attachments->count() }}</span>
                                    </span>
                                    @endif
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-lg font-medium">Немає транзакцій</p>
                                <p class="text-sm">за вибраний період з вказаними фільтрами</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($transactions->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $transactions->withQueryString()->links() }}
        </div>
        @endif
    </div>

    <!-- Transaction Details Modal -->
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity" @click="showModal = false"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl transform transition-all sm:max-w-2xl sm:w-full mx-auto"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 @click.away="showModal = false">

                <!-- Loading -->
                <div x-show="loading" class="p-8 text-center">
                    <svg class="animate-spin h-8 w-8 mx-auto text-primary-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Content -->
                <div x-show="!loading && transaction" class="text-left">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Деталі транзакції</h3>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-4">
                        <!-- Amount -->
                        <div class="text-center py-4">
                            <span class="text-3xl font-bold" :class="transaction?.direction === 'in' ? 'text-green-600' : 'text-red-600'"
                                  x-text="(transaction?.direction === 'in' ? '+' : '-') + new Intl.NumberFormat('uk-UA').format(transaction?.amount || 0) + ' ' + (transaction?.currency || '₴')">
                            </span>
                        </div>

                        <!-- Details Grid -->
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Дата:</span>
                                <span class="ml-2 text-gray-900 dark:text-white" x-text="transaction?.date_formatted"></span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Тип:</span>
                                <span class="ml-2 text-gray-900 dark:text-white" x-text="transaction?.direction === 'in' ? 'Надходження' : 'Витрата'"></span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Категорія:</span>
                                <span class="ml-2 text-gray-900 dark:text-white" x-text="transaction?.category?.name || '—'"></span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Спосіб оплати:</span>
                                <span class="ml-2 text-gray-900 dark:text-white" x-text="transaction?.payment_method_label || '—'"></span>
                            </div>
                            <div x-show="transaction?.ministry">
                                <span class="text-gray-500 dark:text-gray-400">Команда:</span>
                                <span class="ml-2 text-gray-900 dark:text-white" x-text="transaction?.ministry?.name"></span>
                            </div>
                            <div x-show="transaction?.person">
                                <span class="text-gray-500 dark:text-gray-400">Особа:</span>
                                <span class="ml-2 text-gray-900 dark:text-white" x-text="transaction?.person?.full_name"></span>
                            </div>
                            <div x-show="transaction?.recorder">
                                <span class="text-gray-500 dark:text-gray-400">Записав:</span>
                                <span class="ml-2 text-gray-900 dark:text-white" x-text="transaction?.recorder?.name"></span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div x-show="transaction?.description">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Опис:</span>
                            <p class="mt-1 text-gray-900 dark:text-white" x-text="transaction?.description"></p>
                        </div>

                        <!-- Attachments -->
                        <div x-show="transaction?.attachments?.length > 0">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Прикріплені файли:</span>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <template x-for="attachment in transaction?.attachments" :key="attachment.id">
                                    <a :href="'/storage/' + attachment.path" target="_blank"
                                       class="flex items-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                        <template x-if="attachment.path.match(/\.(jpg|jpeg|png|gif|webp)$/i)">
                                            <img :src="'/storage/' + attachment.path" class="w-10 h-10 object-cover rounded">
                                        </template>
                                        <template x-if="!attachment.path.match(/\.(jpg|jpeg|png|gif|webp)$/i)">
                                            <div class="w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                        </template>
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 truncate" x-text="attachment.original_name || 'Файл'"></span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                        <a :href="transaction?.direction === 'in' ? '/finances/incomes/' + transaction?.id + '/edit' : '/finances/expenses/' + transaction?.id + '/edit'"
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-primary-600 hover:text-primary-700">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Редагувати
                        </a>
                        <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            Закрити
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function journalApp() {
    return {
        showCustomDates: {{ $period === 'custom' ? 'true' : 'false' }},
        showModal: false,
        loading: false,
        transaction: null,

        setPeriod(period) {
            const url = new URL(window.location.href);
            url.searchParams.set('period', period);
            url.searchParams.delete('start_date');
            url.searchParams.delete('end_date');
            url.searchParams.delete('page');
            window.location.href = url.toString();
        },

        async openDetails(id) {
            this.showModal = true;
            this.loading = true;
            this.transaction = null;

            try {
                // Find transaction in current page data
                const transactions = @json($transactions->items());
                const found = transactions.find(t => t.id === id);

                if (found) {
                    this.transaction = {
                        ...found,
                        date_formatted: new Date(found.date).toLocaleDateString('uk-UA', { day: 'numeric', month: 'long', year: 'numeric' }),
                        payment_method_label: @json(\App\Models\Transaction::PAYMENT_METHODS)[found.payment_method] || found.payment_method || '—',
                        person: found.person ? { full_name: found.person.first_name + ' ' + found.person.last_name } : null,
                    };
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection
