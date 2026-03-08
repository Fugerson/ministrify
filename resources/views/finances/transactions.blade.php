@extends('layouts.app')

@section('title', __('app.transactions'))

@section('actions')
<div class="flex items-center space-x-2" x-data="exportButton()">
    @if(auth()->user()->canCreate('finances'))
    <button type="button" onclick="window.openIncomeModal && window.openIncomeModal()"
       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('app.income_action') }}
    </button>
    <button type="button" onclick="window.openExpenseModal && window.openExpenseModal()"
       class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
        </svg>
        {{ __('app.expense_action') }}
    </button>
    @if(count($enabledCurrencies) > 1)
    <button type="button" onclick="window.openExchangeModal && window.openExchangeModal()"
       class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        {{ __('app.exchange') }}
    </button>
    @endif
    @endif
    <button @click="downloadExport()"
            :disabled="exporting"
            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 disabled:bg-gray-400 text-white text-sm font-medium rounded-lg transition-colors">
        <template x-if="!exporting">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </template>
        <template x-if="exporting">
            <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </template>
        <span x-text="exporting ? @js( __('app.generating') ) : 'CSV'"></span>
    </button>
</div>

<script>
function exportButton() {
    return {
        exporting: false,
        async downloadExport() {
            this.exporting = true;
            try {
                const response = await fetch('{{ route("finances.transactions.export") }}?' + new URLSearchParams(window.location.search).toString(), {
                    headers: { 'Accept': 'text/csv' }
                });

                if (!response.ok) throw new Error('Export failed');

                const blob = await response.blob();
                const filename = response.headers.get('Content-Disposition')?.match(/filename="(.+)"/)?.[1] || 'transactions.csv';

                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                a.remove();
            } catch (error) {
                console.error('Export error:', error);
                alert(@js( __('app.export_error') ));
            } finally {
                this.exporting = false;
            }
        }
    }
}
</script>
@endsection

@section('content')
@include('finances.partials.tabs')

<div id="finance-content">
<div x-data="transactionsApp()" class="space-y-4" @finance-period-changed.window="handlePeriodChange($event.detail)">

    <!-- Sub-filter tabs: All / Income / Expense -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm px-4 py-3">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex rounded-lg bg-gray-100 dark:bg-gray-700 p-0.5">
                <button @click="setSubFilter('')"
                        :class="subFilter === '' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                        class="px-4 py-1.5 text-sm font-medium rounded-md transition-all">
                    {{ __('app.all_transactions') }}
                </button>
                <button @click="setSubFilter('in')"
                        :class="subFilter === 'in' ? 'bg-green-100 dark:bg-green-900/40 shadow-sm text-green-700 dark:text-green-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                        class="px-4 py-1.5 text-sm font-medium rounded-md transition-all">
                    {{ __('app.income_action') }}
                </button>
                <button @click="setSubFilter('out')"
                        :class="subFilter === 'out' ? 'bg-red-100 dark:bg-red-900/40 shadow-sm text-red-700 dark:text-red-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                        class="px-4 py-1.5 text-sm font-medium rounded-md transition-all">
                    {{ __('app.expenses') }}
                </button>
            </div>

            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <input type="text" x-model.debounce.300ms="filters.search" placeholder="{{ __('app.search') }}..."
                       class="w-full px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
            </div>

            <!-- Category filter -->
            <select x-model="filters.category_id"
                    class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                <option value="">{{ __('app.all_categories') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->icon ?? '' }} {{ $cat->name }}</option>
                @endforeach
            </select>

            <!-- Ministry filter -->
            <select x-model="filters.ministry_id"
                    class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                <option value="">{{ __('app.all_teams_filter') }}</option>
                @foreach($ministries as $ministry)
                    <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                @endforeach
            </select>

            <!-- Reset -->
            <button x-show="hasActiveFilters" @click="resetFilters()" x-cloak
                    class="px-3 py-1.5 text-sm text-gray-500 hover:text-red-500 transition-colors">
                {{ __('app.reset_filters') }}
            </button>
        </div>
    </div>

    <!-- Summary Cards (adaptive based on sub-filter) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Balance Before (all / income) -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4" x-show="subFilter !== 'out'">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('app.start_balance') }}</p>
            <p class="text-xl font-bold" :class="periodStats.balanceBefore >= 0 ? 'text-gray-900 dark:text-white' : 'text-red-600'"
               x-text="formatNumber(periodStats.balanceBefore) + ' ₴'"></p>
        </div>
        <!-- Income -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase" x-text="subFilter === 'out' ? @js( __('app.total_expenses') ) : @js( __('app.income_action') )"></p>
            <p class="text-xl font-bold"
               :class="subFilter === 'out' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'"
               x-text="subFilter === 'out' ? ('-' + formatNumber(periodStats.expense) + ' ₴') : ('+' + formatNumber(periodStats.income) + ' ₴')"></p>
        </div>
        <!-- Expense (only when not in expense-only mode) -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4" x-show="subFilter !== 'in' && subFilter !== 'out'">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('app.expenses') }}</p>
            <p class="text-xl font-bold text-red-600 dark:text-red-400" x-text="'-' + formatNumber(periodStats.expense) + ' ₴'"></p>
        </div>
        <!-- Balance After -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4" x-show="subFilter !== 'out'">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('app.finance_balance_end') }}</p>
            <p class="text-xl font-bold" :class="periodStats.balanceAfter >= 0 ? 'text-primary-600 dark:text-primary-400' : 'text-red-600'"
               x-text="formatNumber(periodStats.balanceAfter) + ' ₴'"></p>
        </div>
        <!-- Count of transactions (shown in filtered modes) -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4" x-show="subFilter === 'in' || subFilter === 'out'">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('app.finance_count') }}</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="displayedTransactions.length"></p>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th @click="toggleSort('date')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-200 select-none">
                            <span class="inline-flex items-center gap-1">{{ __('app.finance_date_header') }} <template x-if="sortColumn === 'date'"><svg class="w-3 h-3" :class="sortDir === 'asc' && 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></span>
                        </th>
                        <th @click="toggleSort('description')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-200 select-none">
                            <span class="inline-flex items-center gap-1">{{ __('app.finance_description_header') }} <template x-if="sortColumn === 'description'"><svg class="w-3 h-3" :class="sortDir === 'asc' && 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></span>
                        </th>
                        <th @click="toggleSort('category')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-200 select-none">
                            <span class="inline-flex items-center gap-1">{{ __('app.finance_category_header') }} <template x-if="sortColumn === 'category'"><svg class="w-3 h-3" :class="sortDir === 'asc' && 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></span>
                        </th>
                        <th @click="toggleSort('ministry')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-200 select-none">
                            <span class="inline-flex items-center gap-1">{{ __('app.finance_team_header') }} <template x-if="sortColumn === 'ministry'"><svg class="w-3 h-3" :class="sortDir === 'asc' && 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></span>
                        </th>
                        <th @click="toggleSort('amount')" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-200 select-none">
                            <span class="inline-flex items-center justify-end gap-1">{{ __('app.finance_amount_header') }} <template x-if="sortColumn === 'amount'"><svg class="w-3 h-3" :class="sortDir === 'asc' && 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></span>
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider" x-show="subFilter === ''">{{ __('app.finance_balance_header') }}</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16"></th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="item in displayedTransactions" :key="item.transaction.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors"
                            @click="openDetails(item.transaction.id)">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="formatDate(item.transaction.date)"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400" x-text="formatWeekday(item.transaction.date)"></div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900 dark:text-white" x-text="truncate(item.transaction.description, 40)"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400" x-show="item.transaction.payment_method" x-text="paymentMethods[item.transaction.payment_method] || item.transaction.payment_method"></div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <template x-if="item.transaction.category">
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium"
                                          :style="'background-color: ' + item.transaction.category.color + '20; color: ' + item.transaction.category.color">
                                        <span x-text="(item.transaction.category.icon || '') + ' ' + item.transaction.category.name"></span>
                                    </span>
                                </template>
                                <template x-if="!item.transaction.category">
                                    <span class="text-gray-400 dark:text-gray-500 text-sm">—</span>
                                </template>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <template x-if="item.transaction.ministry">
                                    <div class="text-sm text-gray-900 dark:text-white" x-text="item.transaction.ministry.name"></div>
                                </template>
                                <template x-if="!item.transaction.ministry">
                                    <span class="text-gray-400 dark:text-gray-500 text-sm">—</span>
                                </template>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <span class="text-sm font-semibold" :class="item.transaction.direction === 'in' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                    <span x-text="(item.transaction.direction === 'in' ? '+' : '-') + formatNumber(item.transaction.amount)"></span>
                                    <span class="text-xs" x-text="currencySymbol(item.transaction.currency)"></span>
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right" x-show="subFilter === ''">
                                <span class="text-sm font-medium" :class="item.balance >= 0 ? 'text-gray-900 dark:text-white' : 'text-red-600'" x-text="formatNumber(item.balance) + ' ₴'"></span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <template x-if="item.transaction.attachments && item.transaction.attachments.length > 0">
                                        <span class="inline-flex items-center text-gray-400 dark:text-gray-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                            </svg>
                                            <span class="text-xs ml-0.5" x-text="item.transaction.attachments.length"></span>
                                        </span>
                                    </template>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="displayedTransactions.length === 0">
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-lg font-medium">{{ __('app.finance_no_transactions') }}</p>
                            <p class="text-sm">{{ __('app.finance_for_selected_period') }}</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Results count -->
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400" x-show="hasActiveFilters">
            {{ __('app.finance_found_count') }} <span x-text="displayedTransactions.length"></span> з <span x-text="periodTransactions.length"></span>
        </div>
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
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.finance_transaction_details') }}</h3>
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
                                  x-text="(transaction?.direction === 'in' ? '+' : '-') + formatNumber(transaction?.amount || 0) + ' ' + currencySymbol(transaction?.currency)">
                            </span>
                        </div>

                        <!-- Details Grid -->
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('app.finance_date_colon') }}</span>
                                <span class="ml-2 text-gray-900 dark:text-white" x-text="transaction?.date_formatted"></span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('app.finance_type_colon') }}</span>
                                <span class="ml-2 text-gray-900 dark:text-white" x-text="transaction?.direction === 'in' ? @js( __('app.finance_type_income') ) : @js( __('app.finance_type_expense') )"></span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('app.finance_category_colon') }}</span>
                                <span class="ml-2 text-gray-900 dark:text-white" x-text="transaction?.category?.name || '—'"></span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('app.finance_payment_method_colon') }}</span>
                                <span class="ml-2 text-gray-900 dark:text-white" x-text="transaction?.payment_method_label || '—'"></span>
                            </div>
                            <div x-show="transaction?.ministry">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('app.finance_team_colon') }}</span>
                                <span class="ml-2 text-gray-900 dark:text-white" x-text="transaction?.ministry?.name"></span>
                            </div>
                            <div x-show="transaction?.recorder">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('app.finance_recorded_by') }}</span>
                                <span class="ml-2 text-gray-900 dark:text-white" x-text="transaction?.recorder?.name"></span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div x-show="transaction?.description">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">{{ __('app.finance_description_colon') }}</span>
                            <p class="mt-1 text-gray-900 dark:text-white" x-text="transaction?.description"></p>
                        </div>

                        <!-- Attachments -->
                        <div x-show="transaction?.attachments?.length > 0">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">{{ __('app.finance_attached_files') }}</span>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <template x-for="attachment in transaction?.attachments" :key="attachment.id">
                                    <div>
                                        <template x-if="attachment.path.match(/\.(jpg|jpeg|png|gif|webp)$/i)">
                                            <div @click="$dispatch('open-lightbox', '/storage/' + attachment.path)"
                                                 class="flex items-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors cursor-pointer">
                                                <img :src="'/storage/' + attachment.path" class="w-16 h-16 object-cover rounded">
                                                <a href="#" @click.prevent="$dispatch('open-lightbox', '/storage/' + attachment.path)"
                                                   class="ml-2 text-sm text-gray-700 dark:text-gray-300 truncate" x-text="attachment.original_name || 'Файл'"></a>
                                            </div>
                                        </template>
                                        <template x-if="!attachment.path.match(/\.(jpg|jpeg|png|gif|webp)$/i)">
                                            <a :href="'/storage/' + attachment.path" target="_blank"
                                               class="flex items-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                                <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                </div>
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 truncate" x-text="attachment.original_name || 'Файл'"></span>
                                            </a>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                        @if(auth()->user()->canEdit('finances'))
                        <button type="button" @click="openEditModal(transaction)"
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-primary-600 hover:text-primary-700">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            {{ __('app.finance_edit') }}
                        </button>
                        @else
                        <div></div>
                        @endif
                        <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            {{ __('app.finance_close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function transactionsApp() {
    return {
        showModal: false,
        loading: false,
        transaction: null,

        // Period state - synced with global filter
        activePeriod: localStorage.getItem('financePeriod') || 'month',
        customDateRange: null,

        // Sub-filter (direction): '' = all, 'in' = income, 'out' = expense
        subFilter: '{{ $initialFilter ?? '' }}',

        // Sorting
        sortColumn: 'date',
        sortDir: 'desc',

        // Filter state
        filters: {
            search: '',
            category_id: '',
            ministry_id: '',
        },

        // Data
        allTransactions: @json($transactions),
        balanceBeforeYear: {{ $balanceBeforeYear }},
        currentBalance: {{ $currentBalance }},
        paymentMethods: @json(\App\Models\Transaction::PAYMENT_METHODS),

        setSubFilter(filter) {
            this.subFilter = filter;
        },

        toggleSort(column) {
            if (this.sortColumn === column) {
                this.sortDir = this.sortDir === 'desc' ? 'asc' : 'desc';
            } else {
                this.sortColumn = column;
                this.sortDir = column === 'date' ? 'desc' : 'asc';
            }
        },

        handlePeriodChange(detail) {
            if (detail) {
                if (detail.customMode && detail.dateRange) {
                    this.customDateRange = detail.dateRange;
                    this.activePeriod = null;
                } else if (detail.period) {
                    this.customDateRange = null;
                    this.activePeriod = detail.period;
                }
            }
        },

        get hasActiveFilters() {
            return this.filters.search || this.filters.category_id || this.filters.ministry_id || this.subFilter;
        },

        get dateRange() {
            if (this.customDateRange && this.customDateRange.start && this.customDateRange.end) {
                return this.customDateRange;
            }

            const now = new Date();
            let start, end;

            switch (this.activePeriod) {
                case 'today':
                    start = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    end = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
                    break;
                case 'week':
                    const dayOfWeek = now.getDay();
                    const diff = now.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1);
                    start = new Date(now.getFullYear(), now.getMonth(), diff);
                    end = new Date(start.getTime() + 6 * 24 * 60 * 60 * 1000);
                    end.setHours(23, 59, 59);
                    break;
                case 'month':
                    start = new Date(now.getFullYear(), now.getMonth(), 1);
                    end = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
                    break;
                case 'quarter':
                    const quarter = Math.floor(now.getMonth() / 3);
                    start = new Date(now.getFullYear(), quarter * 3, 1);
                    end = new Date(now.getFullYear(), quarter * 3 + 3, 0, 23, 59, 59);
                    break;
                case 'year':
                default:
                    start = new Date(now.getFullYear(), 0, 1);
                    end = new Date(now.getFullYear(), 11, 31, 23, 59, 59);
                    break;
            }

            return { start, end };
        },

        get periodTransactions() {
            const { start, end } = this.dateRange;
            return this.allTransactions.filter(t => {
                const date = new Date(t.date);
                return date >= start && date <= end;
            });
        },

        get periodStats() {
            const transactions = this.periodTransactions;
            const { start } = this.dateRange;

            let balanceBefore = this.balanceBeforeYear;
            for (let t of this.allTransactions) {
                const date = new Date(t.date);
                if (date < start) {
                    const amt = parseFloat(t.amount_uah || t.amount);
                    if (t.direction === 'in') {
                        balanceBefore += amt;
                    } else {
                        balanceBefore -= amt;
                    }
                }
            }

            let income = 0, expense = 0;
            for (let t of transactions) {
                const amt = parseFloat(t.amount_uah || t.amount);
                if (t.direction === 'in') {
                    income += amt;
                } else {
                    expense += amt;
                }
            }

            return {
                balanceBefore,
                income,
                expense,
                balanceAfter: balanceBefore + income - expense
            };
        },

        get displayedTransactions() {
            const { balanceBefore, income, expense } = this.periodStats;
            let currentBalance = balanceBefore + income - expense;
            let result = [];

            for (let t of this.periodTransactions) {
                const balance = currentBalance;
                const amt = parseFloat(t.amount_uah || t.amount);
                if (t.direction === 'in') {
                    currentBalance -= amt;
                } else {
                    currentBalance += amt;
                }

                // Apply sub-filter (direction)
                if (this.subFilter && t.direction !== this.subFilter) continue;

                // Apply filters
                let passes = true;

                if (this.filters.search) {
                    const search = this.filters.search.toLowerCase();
                    const matchDesc = t.description && t.description.toLowerCase().includes(search);
                    const matchCategory = t.category && t.category.name.toLowerCase().includes(search);
                    const matchMinistry = t.ministry && t.ministry.name.toLowerCase().includes(search);
                    if (!matchDesc && !matchCategory && !matchMinistry) passes = false;
                }
                if (passes && this.filters.category_id && (!t.category || t.category_id != this.filters.category_id)) passes = false;
                if (passes && this.filters.ministry_id && (!t.ministry || t.ministry_id != this.filters.ministry_id)) passes = false;

                if (passes) {
                    result.push({ transaction: t, balance: balance });
                }
            }

            // Sort
            const col = this.sortColumn;
            const dir = this.sortDir === 'asc' ? 1 : -1;
            result.sort((a, b) => {
                let va, vb;
                switch (col) {
                    case 'date': va = a.transaction.date; vb = b.transaction.date; break;
                    case 'description': va = (a.transaction.description || '').toLowerCase(); vb = (b.transaction.description || '').toLowerCase(); break;
                    case 'category': va = (a.transaction.category?.name || '').toLowerCase(); vb = (b.transaction.category?.name || '').toLowerCase(); break;
                    case 'ministry': va = (a.transaction.ministry?.name || '').toLowerCase(); vb = (b.transaction.ministry?.name || '').toLowerCase(); break;
                    case 'amount':
                        va = parseFloat(a.transaction.amount_uah || a.transaction.amount) * (a.transaction.direction === 'in' ? 1 : -1);
                        vb = parseFloat(b.transaction.amount_uah || b.transaction.amount) * (b.transaction.direction === 'in' ? 1 : -1);
                        return (va - vb) * dir;
                    default: return 0;
                }
                if (va < vb) return -1 * dir;
                if (va > vb) return 1 * dir;
                return 0;
            });

            return result;
        },

        resetFilters() {
            this.filters = {
                search: '',
                category_id: '',
                ministry_id: '',
            };
            this.subFilter = '';
        },

        formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('uk-UA', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },

        formatWeekday(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('uk-UA', { weekday: 'long' });
        },

        currencySymbol(code) {
            const symbols = { UAH: '₴', USD: '$', EUR: '€' };
            return symbols[code] || code || '₴';
        },

        formatNumber(num) {
            return new Intl.NumberFormat('uk-UA').format(Math.round(num));
        },

        truncate(str, len) {
            if (!str) return '';
            return str.length > len ? str.substring(0, len) + '...' : str;
        },

        async openDetails(id) {
            this.showModal = true;
            this.loading = true;
            this.transaction = null;

            try {
                // First check local data
                const local = this.allTransactions.find(t => t.id === id);
                if (local) {
                    this.transaction = {
                        ...local,
                        date_formatted: this.formatDate(local.date),
                        payment_method_label: this.paymentMethods[local.payment_method] || local.payment_method || null
                    };
                    this.loading = false;
                    return;
                }
            } catch (e) {
                console.error(e);
            }
            this.loading = false;
        },

        openEditModal(transaction) {
            this.showModal = false;
            setTimeout(() => {
                if (transaction.direction === 'in') {
                    if (typeof window.openIncomeEditModal === 'function') {
                        window.openIncomeEditModal(transaction);
                    }
                } else {
                    if (typeof window.openExpenseEditModal === 'function') {
                        window.openExpenseEditModal(transaction);
                    }
                }
            }, 50);
        },

        updateTransaction(transaction, isNew = false) {
            if (isNew) {
                this.allTransactions.unshift(transaction);
            } else {
                const index = this.allTransactions.findIndex(t => t.id === transaction.id);
                if (index !== -1) {
                    this.allTransactions[index] = transaction;
                }
            }
        },

        removeTransaction(id) {
            const index = this.allTransactions.findIndex(t => t.id === id);
            if (index !== -1) {
                this.allTransactions.splice(index, 1);
            }
        },

        init() {
            window.journalUpdateTransaction = (t, isNew) => this.updateTransaction(t, isNew);
            window.journalRemoveTransaction = (id) => this.removeTransaction(id);
        }
    }
}
</script>
</div><!-- /finance-content -->

@include('finances.partials.modals.income')
@include('finances.partials.modals.expense')
@include('finances.partials.modals.exchange')
@endsection
