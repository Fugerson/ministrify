@extends('layouts.app')

@section('title', 'Надходження')

@section('actions')
@if(auth()->user()->canCreate('finances'))
<div class="flex items-center space-x-2">
    <button type="button" onclick="window.openIncomeModal && window.openIncomeModal()"
       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Надходження
    </button>
    <a href="{{ route('finances.expenses.index') }}"
       class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
        </svg>
        Витрата
    </a>
    @if(count($enabledCurrencies) > 1)
    <button type="button" onclick="window.openExchangeModal && window.openExchangeModal()"
       class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        Обмін
    </button>
    @endif
</div>
@endif
@endsection

@section('content')
<script>
window.exchangeManager = function() {
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
                const data = await response.json().catch(() => ({}));
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

window.incomesPage = function() {
    return {
        allIncomes: @json($incomesJson),
        search: '',
        categoryFilter: '',
        paymentFilter: '',
        sortBy: 'date_desc',
        perPage: parseInt(localStorage.getItem('financePerPage') || '25'),
        currentPage: 1,

        get filteredIncomes() {
            let items = [...this.allIncomes];

            if (this.search.trim()) {
                const q = this.search.trim().toLowerCase();
                items = items.filter(i =>
                    (i.category_name && i.category_name.toLowerCase().includes(q)) ||
                    (i.person_name && i.person_name.toLowerCase().includes(q)) ||
                    (i.notes && i.notes.toLowerCase().includes(q)) ||
                    (i.amount_formatted && i.amount_formatted.includes(q))
                );
            }

            if (this.categoryFilter) {
                items = items.filter(i => String(i.category_id) === String(this.categoryFilter));
            }

            if (this.paymentFilter) {
                items = items.filter(i => i.payment_method === this.paymentFilter);
            }

            switch (this.sortBy) {
                case 'date_asc':
                    items.sort((a, b) => a.date_full.localeCompare(b.date_full));
                    break;
                case 'date_desc':
                    items.sort((a, b) => b.date_full.localeCompare(a.date_full));
                    break;
                case 'amount_desc':
                    items.sort((a, b) => parseFloat(b.amount_uah || b.amount) - parseFloat(a.amount_uah || a.amount));
                    break;
                case 'amount_asc':
                    items.sort((a, b) => parseFloat(a.amount_uah || a.amount) - parseFloat(b.amount_uah || b.amount));
                    break;
            }

            return items;
        },

        get totalPages() {
            if (this.perPage === 0) return 1;
            return Math.max(1, Math.ceil(this.filteredIncomes.length / this.perPage));
        },

        get paginatedIncomes() {
            if (this.perPage === 0) return this.filteredIncomes;
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredIncomes.slice(start, start + this.perPage);
        },

        get showFrom() {
            if (this.filteredIncomes.length === 0) return 0;
            if (this.perPage === 0) return 1;
            return (this.currentPage - 1) * this.perPage + 1;
        },

        get showTo() {
            if (this.perPage === 0) return this.filteredIncomes.length;
            return Math.min(this.currentPage * this.perPage, this.filteredIncomes.length);
        },

        get visiblePages() {
            const pages = [];
            const total = this.totalPages;
            const current = this.currentPage;
            if (total <= 7) {
                for (let i = 1; i <= total; i++) pages.push(i);
            } else {
                pages.push(1);
                if (current > 3) pages.push('...');
                for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
                    pages.push(i);
                }
                if (current < total - 2) pages.push('...');
                pages.push(total);
            }
            return pages;
        },

        setPerPage(val) {
            this.perPage = parseInt(val);
            this.currentPage = 1;
            localStorage.setItem('financePerPage', val);
        },

        goToPage(page) {
            if (page === '...' || page < 1 || page > this.totalPages) return;
            this.currentPage = page;
            document.getElementById('finance-content')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        get totalFiltered() {
            return this.filteredIncomes.reduce((sum, i) => sum + parseFloat(i.amount_uah || i.amount), 0);
        },

        get totalFilteredFormatted() {
            return Math.round(this.totalFiltered).toLocaleString('uk-UA') + ' ₴';
        },

        get isFiltered() {
            return this.search || this.categoryFilter || this.paymentFilter;
        },

        clearFilters() {
            this.search = '';
            this.categoryFilter = '';
            this.paymentFilter = '';
            this.sortBy = 'date_desc';
            this.currentPage = 1;
        },

        formatNumber(num) {
            return Math.round(num).toLocaleString('uk-UA');
        }
    };
};

window.incomesManager = function() {
    return {
        modalOpen: false,
        deleteModalOpen: false,
        isEdit: false,
        editId: null,
        deleteId: null,
        loading: false,
        errors: {},
        formData: {
            amount: '',
            currency: 'UAH',
            category_id: '',
            category_name: '',
            date: new Date().toISOString().split('T')[0],
            payment_method: 'cash',
            notes: '',
            is_anonymous: true
        },

        init() {
            window.openIncomeModal = () => this.openCreate();
        },

        openCreate() {
            this.isEdit = false;
            this.editId = null;
            this.resetForm();
            this.errors = {};
            this.modalOpen = true;
        },

        async openEdit(id) {
            this.loading = true;
            this.errors = {};

            try {
                const response = await fetch(`/finances/incomes/${id}/edit`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Failed to load');

                const data = await response.json().catch(() => ({}));

                this.formData = {
                    amount: data.transaction.amount,
                    currency: data.transaction.currency || 'UAH',
                    category_id: data.transaction.category_id || '',
                    category_name: '',
                    date: data.transaction.date.substring(0, 10),
                    payment_method: data.transaction.payment_method || 'cash',
                    notes: data.transaction.notes || '',
                    is_anonymous: data.transaction.is_anonymous ?? true
                };

                this.isEdit = true;
                this.editId = id;
                this.modalOpen = true;
            } catch (error) {
                showToast('error', 'Помилка завантаження даних');
            } finally {
                this.loading = false;
            }
        },

        async submit() {
            this.loading = true;
            this.errors = {};

            const url = this.isEdit
                ? `/finances/incomes/${this.editId}`
                : '/finances/incomes';

            try {
                const payload = {...this.formData};
                if (payload.category_id === '__custom__') { payload.category_id = ''; }
                else { delete payload.category_name; }
                const response = await fetch(url, {
                    method: this.isEdit ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json().catch(() => ({}));

                if (response.ok && data.success) {
                    this.modalOpen = false;
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 500);
                } else if (response.status === 422) {
                    this.errors = data.errors || {};
                    if (data.message && !data.errors) {
                        showToast('error', data.message);
                    }
                } else {
                    showToast('error', data.message || 'Помилка збереження');
                }
            } catch (error) {
                showToast('error', 'Помилка з\'єднання');
            } finally {
                this.loading = false;
            }
        },

        confirmDelete(id) {
            this.deleteId = id;
            this.deleteModalOpen = true;
        },

        async deleteIncome() {
            if (!this.deleteId) return;

            this.loading = true;

            try {
                const response = await fetch(`/finances/incomes/${this.deleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json().catch(() => ({}));

                if (response.ok && data.success) {
                    this.deleteModalOpen = false;
                    this.modalOpen = false;
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast('error', data.message || 'Помилка видалення');
                }
            } catch (error) {
                showToast('error', 'Помилка з\'єднання');
            } finally {
                this.loading = false;
                this.deleteId = null;
            }
        },

        resetForm() {
            this.formData = {
                amount: '',
                currency: 'UAH',
                category_id: '',
                category_name: '',
                date: new Date().toISOString().split('T')[0],
                payment_method: 'cash',
                notes: '',
                is_anonymous: true
            };
        }
    }
};
</script>

<div x-data="incomesManager()" x-cloak @income-edit.window="openEdit($event.detail)" @income-delete.window="confirmDelete($event.detail)">
@include('finances.partials.tabs')

<div id="finance-content" x-data="incomesPage()" @finance-period-changed.window="
    const d = $event.detail;
    if (d.isUserAction) {
        handlePeriodReload(d);
    } else if (!new URL(window.location.href).searchParams.has('start_date')) {
        if (d.customMode || d.period !== 'month') handlePeriodReload(d);
    }
">
<div class="space-y-4">

    <!-- Summary + Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg px-4 py-2 inline-flex items-center gap-2">
                <p class="text-sm text-green-600 dark:text-green-400">Загалом:</p>
                <p class="text-lg font-bold text-green-700 dark:text-green-300" x-text="totalFilteredFormatted"></p>
                <span x-show="isFiltered" class="text-xs text-green-500 dark:text-green-400" x-text="'(' + filteredIncomes.length + ' з ' + allIncomes.length + ')'"></span>
            </div>
            <button type="button" x-show="isFiltered" @click="clearFilters()" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                Скинути фільтри
            </button>
        </div>

        <!-- Filter row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Search -->
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" x-model="search" @input="currentPage = 1" placeholder="Пошук..."
                       class="w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <!-- Category -->
            <select x-model="categoryFilter" @change="currentPage = 1"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500">
                <option value="">Усі категорії</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->icon_emoji }} {{ $category->name }}</option>
                @endforeach
            </select>

            <!-- Payment method -->
            <select x-model="paymentFilter" @change="currentPage = 1"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500">
                <option value="">Усі способи</option>
                <option value="cash">💵 Готівка</option>
                <option value="card">💳 Картка</option>
                <option value="liqpay">💳 LiqPay</option>
                <option value="monobank">💳 Monobank</option>
            </select>

            <!-- Sort -->
            <select x-model="sortBy" @change="currentPage = 1"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500">
                <option value="date_desc">Дата (нові)</option>
                <option value="date_asc">Дата (старі)</option>
                <option value="amount_desc">Сума (більше)</option>
                <option value="amount_asc">Сума (менше)</option>
            </select>
        </div>
    </div>

    <!-- Incomes list -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дата</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden sm:table-cell">Категорія</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden lg:table-cell">Дарувальник</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Спосіб</th>
                        <th class="px-3 md:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Сума</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- Empty state -->
                    <template x-if="filteredIncomes.length === 0">
                        <tr>
                            <td colspan="5" class="px-3 md:px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <template x-if="isFiltered">
                                    <span>Нічого не знайдено за вашим запитом</span>
                                </template>
                                <template x-if="!isFiltered">
                                    <span>Немає надходжень за цей період</span>
                                </template>
                            </td>
                        </tr>
                    </template>

                    <!-- Data rows (paginated) -->
                    <template x-for="income in paginatedIncomes" :key="income.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ auth()->user()->canEdit('finances') ? 'cursor-pointer' : '' }}"
                            @if(auth()->user()->canEdit('finances')) @click="$dispatch('income-edit', income.id)" @endif
                            :data-income-id="income.id">
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white" x-text="income.date"></div>
                                <div class="sm:hidden text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    <span x-text="income.category_icon"></span> <span x-text="income.category_name"></span>
                                </div>
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap hidden sm:table-cell">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="`background-color: ${income.category_color}20; color: ${income.category_color}`">
                                    <span x-text="income.category_icon"></span>&nbsp;<span x-text="income.category_name"></span>
                                </span>
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap hidden lg:table-cell">
                                <span class="text-sm text-gray-600 dark:text-gray-400" x-text="income.person_name || '—'"></span>
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell" x-text="income.payment_method_label">
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-semibold text-green-600 dark:text-green-400" x-text="'+' + income.amount_formatted"></span>
                                <template x-if="income.currency !== 'UAH' && income.amount_uah">
                                    <span class="block text-xs text-gray-400 dark:text-gray-500" x-text="Math.round(income.amount_uah).toLocaleString('uk-UA') + ' ₴'"></span>
                                </template>
                                <template x-if="income.notes">
                                    <span class="block text-xs text-gray-400 dark:text-gray-500 md:hidden truncate max-w-[120px]" x-text="income.notes"></span>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination footer -->
        <div x-show="filteredIncomes.length > 0" class="px-3 md:px-4 py-2 border-t border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="showFrom + '–' + showTo + ' з ' + filteredIncomes.length"></span>
                <select @change="setPerPage($event.target.value)" :value="perPage"
                        class="px-1.5 py-0.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs focus:ring-1 focus:ring-primary-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="0">Усі</option>
                </select>
                <div x-show="totalPages > 1" class="flex items-center gap-0.5">
                    <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1"
                            class="px-1.5 py-0.5 text-xs rounded border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed">&laquo;</button>
                    <template x-for="page in visiblePages" :key="'p'+page">
                        <button @click="goToPage(page)"
                                :class="page === currentPage ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                                :disabled="page === '...'"
                                class="px-2 py-0.5 text-xs rounded border min-w-[28px]"
                                x-text="page"></button>
                    </template>
                    <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= totalPages"
                            class="px-1.5 py-0.5 text-xs rounded border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed">&raquo;</button>
                </div>
            </div>
        </div>
    </div>
</div>
</div><!-- /finance-content -->

<!-- Create/Edit Modal -->
<div x-show="modalOpen" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     x-transition:enter="ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50" @click="modalOpen = false"></div>

    <!-- Modal -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg relative"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop>

            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white"
                    x-text="isEdit ? 'Редагувати надходження' : 'Додати надходження'">
                </h3>
                <button @click="modalOpen = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit()" class="p-6 space-y-4">
                <!-- Amount + Currency -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Сума *</label>
                    <div class="flex gap-2">
                        <input type="number" x-model="formData.amount" step="0.01" min="0.01" required
                               class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               :class="{ 'border-red-500': errors.amount }"
                               placeholder="0.00">
                        @if(count($enabledCurrencies) > 1)
                        <select x-model="formData.currency"
                                class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            @foreach($enabledCurrencies as $code)
                            <option value="{{ $code }}">{{ \App\Helpers\CurrencyHelper::SYMBOLS[$code] ?? $code }} {{ $code }}</option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                    <p class="text-red-500 text-sm mt-1" x-show="errors.amount" x-text="errors.amount?.[0]"></p>
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата *</label>
                    <input type="date" x-model="formData.date" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                           :class="{ 'border-red-500': errors.date }">
                    <p class="text-red-500 text-sm mt-1" x-show="errors.date" x-text="errors.date?.[0]"></p>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія *</label>
                    <select x-model="formData.category_id" :required="formData.category_id !== '__custom__'"
                            :class="{ 'border-red-500': errors.category_id, 'hidden': formData.category_id === '__custom__' }"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                        <option value="">Оберіть категорію</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->icon ?? '💰' }} {{ $cat->name }}</option>
                        @endforeach
                        <option value="__custom__">Інше (ввести вручну)...</option>
                    </select>
                    <div x-show="formData.category_id === '__custom__'" class="flex gap-2">
                        <input type="text" x-model="formData.category_name" placeholder="Назва категорії..." :required="formData.category_id === '__custom__'"
                               class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                        <button type="button" @click="formData.category_id = ''; formData.category_name = ''"
                                class="px-3 py-2 text-gray-500 hover:text-red-500 border border-gray-300 dark:border-gray-600 rounded-xl">✕</button>
                    </div>
                    <p class="text-red-500 text-sm mt-1" x-show="errors.category_id" x-text="errors.category_id?.[0]"></p>
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Спосіб оплати *</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                               :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': formData.payment_method === 'cash' }">
                            <input type="radio" x-model="formData.payment_method" value="cash" class="sr-only">
                            <span class="text-sm text-gray-700 dark:text-gray-300">💵 Готівка</span>
                        </label>
                        <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                               :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': formData.payment_method === 'card' }">
                            <input type="radio" x-model="formData.payment_method" value="card" class="sr-only">
                            <span class="text-sm text-gray-700 dark:text-gray-300">💳 Картка</span>
                        </label>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нотатки</label>
                    <textarea x-model="formData.notes" rows="2"
                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                              placeholder="Внутрішні нотатки..."></textarea>
                </div>

                <!-- Buttons -->
                <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    @if(auth()->user()->canDelete('finances'))
                    <button x-show="isEdit" type="button" @click="confirmDelete(editId)"
                            class="px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors text-sm">
                        Видалити
                    </button>
                    @endif
                    <div class="flex-1"></div>
                    <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
                        Скасувати
                    </button>
                    <button type="submit" :disabled="loading"
                            class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading" x-text="isEdit ? 'Зберегти' : 'Додати'"></span>
                        <span x-show="loading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Збереження...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div x-show="deleteModalOpen" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     x-transition:enter="ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50" @click="deleteModalOpen = false"></div>

    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop>

            <!-- Warning Icon -->
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white text-center">
                Видалити надходження?
            </h3>

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                Ви впевнені, що хочете видалити цей запис? Цю дію неможливо скасувати.
            </p>

            <div class="mt-6 flex justify-center space-x-3">
                <button type="button" @click="deleteModalOpen = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Скасувати
                </button>
                <button type="button" @click="deleteIncome()" :disabled="loading"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors disabled:opacity-50">
                    <span x-show="!loading">Видалити</span>
                    <span x-show="loading">Видалення...</span>
                </button>
            </div>
        </div>
    </div>
</div>

</div>

@if(count($enabledCurrencies) > 1)
<!-- Exchange Modal -->
<div x-data="exchangeManager()" x-cloak>
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
                    <!-- From -->
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                        <label class="block text-sm font-medium text-red-700 dark:text-red-300 mb-2">Віддаєте</label>
                        <div class="flex gap-2">
                            <input type="number" x-model="formData.from_amount" @input="calculate()" step="0.01" min="0.01" required
                                   class="flex-1 px-4 py-2 border border-red-200 dark:border-red-800 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <select x-model="formData.from_currency" @change="updateRate()"
                                    class="w-24 px-2 py-2 border border-red-200 dark:border-red-800 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                @foreach($enabledCurrencies ?? ['UAH'] as $curr)
                                    <option value="{{ $curr }}">{{ $curr }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Rate -->
                    <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 flex items-center justify-center gap-2 text-sm">
                        <span>1</span>
                        <span x-text="formData.from_currency !== 'UAH' ? formData.from_currency : formData.to_currency" class="font-medium"></span>
                        <span>=</span>
                        <input type="number" x-model="rate" @input="calculate()" step="0.0001" min="0.0001"
                               class="w-24 px-2 py-1 text-center border border-amber-200 dark:border-amber-800 rounded-lg bg-white dark:bg-gray-700">
                        <span class="font-medium">₴</span>
                    </div>

                    <!-- To -->
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <label class="block text-sm font-medium text-green-700 dark:text-green-300 mb-2">Отримуєте</label>
                        <div class="flex gap-2">
                            <input type="number" x-model="formData.to_amount" step="0.01" min="0.01" required readonly
                                   class="flex-1 px-4 py-2 border border-green-200 dark:border-green-800 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                            <select x-model="formData.to_currency" @change="updateRate()"
                                    class="w-24 px-2 py-2 border border-green-200 dark:border-green-800 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                @foreach($enabledCurrencies ?? ['UAH'] as $curr)
                                    <option value="{{ $curr }}">{{ $curr }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата</label>
                        <input type="date" x-model="formData.date" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нотатки</label>
                        <input type="text" x-model="formData.notes" maxlength="500"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="Де обміняли...">
                    </div>

                    <!-- Warning -->
                    <div x-show="formData.from_currency === formData.to_currency" class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3">
                        <p class="text-sm text-red-700 dark:text-red-300">Оберіть різні валюти</p>
                    </div>

                    <!-- Buttons -->
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
@endif
@endsection
