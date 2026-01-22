@extends('layouts.app')

@section('title', 'Фінансовий журнал')

@section('actions')
<div class="flex items-center space-x-2" x-data="exportButton()">
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
    <a href="{{ route('finances.exchange.create') }}"
       class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        Обмін
    </a>
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
        <span x-text="exporting ? 'Генерація...' : 'CSV'"></span>
    </button>
</div>

<script>
function exportButton() {
    return {
        exporting: false,
        async downloadExport() {
            this.exporting = true;
            try {
                const response = await fetch('{{ route("finances.journal.export") }}?' + new URLSearchParams(window.location.search).toString(), {
                    headers: { 'Accept': 'text/csv' }
                });

                if (!response.ok) throw new Error('Export failed');

                const blob = await response.blob();
                const filename = response.headers.get('Content-Disposition')?.match(/filename="(.+)"/)?.[1] || 'journal.csv';

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
                alert('Помилка експорту');
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
<div x-data="journalApp()" class="space-y-4">
    <!-- Period Selector -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Quick Period Buttons -->
            <div class="flex flex-wrap gap-2">
                <template x-for="[key, label] in Object.entries(periodLabels)" :key="key">
                    <button type="button"
                            @click="setPeriod(key)"
                            :class="activePeriod === key ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors"
                            x-text="label">
                    </button>
                </template>
            </div>

            <!-- Date Display -->
            <div class="flex-1 text-center">
                <span class="text-sm text-gray-500 dark:text-gray-400" x-text="dateRangeDisplay"></span>
            </div>

            <!-- Balance Info -->
            <div class="text-right">
                <span class="text-sm text-gray-500 dark:text-gray-400">Баланс:</span>
                <span class="ml-1 font-semibold" :class="currentBalance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                      x-text="formatNumber(currentBalance) + ' ₴'">
                </span>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Баланс на початок</p>
            <p class="text-xl font-bold" :class="periodStats.balanceBefore >= 0 ? 'text-gray-900 dark:text-white' : 'text-red-600'"
               x-text="formatNumber(periodStats.balanceBefore) + ' ₴'"></p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Надходження</p>
            <p class="text-xl font-bold text-green-600 dark:text-green-400" x-text="'+' + formatNumber(periodStats.income) + ' ₴'"></p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Витрати</p>
            <p class="text-xl font-bold text-red-600 dark:text-red-400" x-text="'-' + formatNumber(periodStats.expense) + ' ₴'"></p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Баланс на кінець</p>
            <p class="text-xl font-bold" :class="periodStats.balanceAfter >= 0 ? 'text-primary-600 dark:text-primary-400' : 'text-red-600'"
               x-text="formatNumber(periodStats.balanceAfter) + ' ₴'"></p>
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Команда</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Сума</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Баланс</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16"></th>
                    </tr>
                    <!-- Filter Row -->
                    <tr class="bg-gray-100 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                        <td class="px-4 py-2"></td>
                        <td class="px-4 py-2">
                            <div class="relative">
                                <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" x-model.debounce.300ms="filters.search" placeholder="Пошук..."
                                       class="w-full pl-8 pr-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs">
                            </div>
                        </td>
                        <td class="px-4 py-2">
                            <select x-model="filters.category_id"
                                    class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs">
                                <option value="">Всі</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-4 py-2">
                            <select x-model="filters.ministry_id"
                                    class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs">
                                <option value="">Всі</option>
                                @foreach($ministries as $ministry)
                                <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-4 py-2">
                            <select x-model="filters.direction"
                                    class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs">
                                <option value="">Всі</option>
                                <option value="in">+</option>
                                <option value="out">-</option>
                            </select>
                        </td>
                        <td class="px-4 py-2"></td>
                        <td class="px-4 py-2 text-center">
                            <button type="button" x-show="hasActiveFilters" @click="resetFilters()"
                               class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" title="Скинути фільтри">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </td>
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
                                    <span class="text-xs" x-text="item.transaction.currency || '₴'"></span>
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
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
                            <p class="text-lg font-medium">Немає транзакцій</p>
                            <p class="text-sm">за вибраний період з вказаними фільтрами</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Results count -->
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400" x-show="hasActiveFilters">
            Знайдено: <span x-text="displayedTransactions.length"></span> з <span x-text="periodTransactions.length"></span>
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
                                  x-text="(transaction?.direction === 'in' ? '+' : '-') + formatNumber(transaction?.amount || 0) + ' ' + (transaction?.currency || '₴')">
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
        showModal: false,
        loading: false,
        transaction: null,

        // Period state
        activePeriod: '{{ $initialPeriod }}',
        periodLabels: {
            'today': 'Сьогодні',
            'week': 'Тиждень',
            'month': 'Місяць',
            'quarter': 'Квартал',
            'year': 'Рік'
        },

        // Filter state
        filters: {
            search: '',
            category_id: '',
            ministry_id: '',
            direction: ''
        },

        // Data
        allTransactions: @json($transactions),
        balanceBeforeYear: {{ $balanceBeforeYear }},
        currentBalance: {{ $currentBalance }},
        paymentMethods: @json(\App\Models\Transaction::PAYMENT_METHODS),

        get hasActiveFilters() {
            return this.filters.search || this.filters.category_id || this.filters.ministry_id || this.filters.direction;
        },

        // Calculate date range for current period
        get dateRange() {
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

        get dateRangeDisplay() {
            const { start, end } = this.dateRange;
            const formatDate = (d) => d.toLocaleDateString('uk-UA', { day: '2-digit', month: '2-digit', year: 'numeric' });
            return formatDate(start) + ' — ' + formatDate(end);
        },

        // Filter transactions by current period
        get periodTransactions() {
            const { start, end } = this.dateRange;
            return this.allTransactions.filter(t => {
                const date = new Date(t.date);
                return date >= start && date <= end;
            });
        },

        // Calculate period stats
        get periodStats() {
            const transactions = this.periodTransactions;
            const { start } = this.dateRange;

            // Calculate balance before period start
            let balanceBefore = this.balanceBeforeYear;
            for (let t of this.allTransactions) {
                const date = new Date(t.date);
                if (date < start) {
                    if (t.direction === 'in') {
                        balanceBefore += parseFloat(t.amount);
                    } else {
                        balanceBefore -= parseFloat(t.amount);
                    }
                }
            }

            // Calculate income and expense for period
            let income = 0, expense = 0;
            for (let t of transactions) {
                if (t.direction === 'in') {
                    income += parseFloat(t.amount);
                } else {
                    expense += parseFloat(t.amount);
                }
            }

            return {
                balanceBefore,
                income,
                expense,
                balanceAfter: balanceBefore + income - expense
            };
        },

        // Apply filters to period transactions and calculate running balance
        get displayedTransactions() {
            const { balanceBefore, income, expense } = this.periodStats;
            let currentBalance = balanceBefore + income - expense;
            let result = [];

            for (let t of this.periodTransactions) {
                const balance = currentBalance;
                if (t.direction === 'in') {
                    currentBalance -= parseFloat(t.amount);
                } else {
                    currentBalance += parseFloat(t.amount);
                }

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
                if (passes && this.filters.direction && t.direction !== this.filters.direction) passes = false;

                if (passes) {
                    result.push({ transaction: t, balance: balance });
                }
            }

            return result;
        },

        setPeriod(period) {
            this.activePeriod = period;
            // Update URL without reload
            const url = new URL(window.location.href);
            url.searchParams.set('period', period);
            window.history.replaceState({}, '', url.toString());
        },

        resetFilters() {
            this.filters = {
                search: '',
                category_id: '',
                ministry_id: '',
                direction: ''
            };
        },

        formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('uk-UA', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },

        formatWeekday(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('uk-UA', { weekday: 'long' });
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
                const found = this.allTransactions.find(t => t.id === id);

                if (found) {
                    this.transaction = {
                        ...found,
                        date_formatted: new Date(found.date).toLocaleDateString('uk-UA', { day: 'numeric', month: 'long', year: 'numeric' }),
                        payment_method_label: this.paymentMethods[found.payment_method] || found.payment_method || '—',
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
</div><!-- /finance-content -->
@endsection
