@extends('layouts.app')

@section('title', 'Витрати')

@section('actions')
@if(auth()->user()->canCreate('finances'))
<div class="flex items-center space-x-2">
    <a href="{{ route('finances.incomes') }}"
       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Надходження
    </a>
    <button type="button" onclick="window.openExpenseModal && window.openExpenseModal()"
       class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
        </svg>
        Витрата
    </button>
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

@php
$remaining = $totals['budget'] - $totals['spent'];
@endphp
window.expensesPage = function() {
    const saved = filterStorage.load('finance_expenses', {
        search: '',
        categoryFilter: '',
        ministryFilter: '',
        paymentFilter: '',
        sortBy: 'date_desc',
    });
    return {
        allExpenses: @json($expensesJson),
        search: saved.search,
        categoryFilter: saved.categoryFilter,
        ministryFilter: saved.ministryFilter,
        paymentFilter: saved.paymentFilter,
        sortBy: saved.sortBy,
        perPage: parseInt(localStorage.getItem('financePerPage') || '25'),
        currentPage: 1,

        init() {
            ['search', 'categoryFilter', 'ministryFilter', 'paymentFilter', 'sortBy'].forEach(key => {
                this.$watch(key, () => this._saveFilters());
            });
        },

        _saveFilters() {
            filterStorage.save('finance_expenses', {
                search: this.search,
                categoryFilter: this.categoryFilter,
                ministryFilter: this.ministryFilter,
                paymentFilter: this.paymentFilter,
                sortBy: this.sortBy,
            });
        },
        budget: {{ $totals['budget'] }},

        get filteredExpenses() {
            let items = [...this.allExpenses];

            if (this.search.trim()) {
                const q = this.search.trim().toLowerCase();
                items = items.filter(i =>
                    (i.description && i.description.toLowerCase().includes(q)) ||
                    (i.notes && i.notes.toLowerCase().includes(q)) ||
                    (i.ministry_name && i.ministry_name.toLowerCase().includes(q)) ||
                    (i.category_name && i.category_name.toLowerCase().includes(q)) ||
                    (i.amount_formatted && i.amount_formatted.includes(q))
                );
            }

            if (this.categoryFilter) {
                items = items.filter(i => String(i.category_id) === String(this.categoryFilter));
            }

            if (this.ministryFilter) {
                items = items.filter(i => String(i.ministry_id) === String(this.ministryFilter));
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
            return Math.max(1, Math.ceil(this.filteredExpenses.length / this.perPage));
        },

        get paginatedExpenses() {
            if (this.perPage === 0) return this.filteredExpenses;
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredExpenses.slice(start, start + this.perPage);
        },

        get showFrom() {
            if (this.filteredExpenses.length === 0) return 0;
            if (this.perPage === 0) return 1;
            return (this.currentPage - 1) * this.perPage + 1;
        },

        get showTo() {
            if (this.perPage === 0) return this.filteredExpenses.length;
            return Math.min(this.currentPage * this.perPage, this.filteredExpenses.length);
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
            return this.filteredExpenses.reduce((sum, i) => sum + parseFloat(i.amount_uah || i.amount), 0);
        },

        get totalFilteredFormatted() {
            return Math.round(this.totalFiltered).toLocaleString('uk-UA') + ' ₴';
        },

        get remaining() {
            return this.budget - this.totalFiltered;
        },

        get remainingFormatted() {
            return Math.round(this.remaining).toLocaleString('uk-UA') + ' ₴';
        },

        get isFiltered() {
            return this.search || this.categoryFilter || this.ministryFilter || this.paymentFilter;
        },

        clearFilters() {
            this.search = '';
            this.categoryFilter = '';
            this.ministryFilter = '';
            this.paymentFilter = '';
            this.sortBy = 'date_desc';
            this.currentPage = 1;
        },

        formatNumber(num) {
            return Math.round(num).toLocaleString('uk-UA');
        }
    };
};

window.expensesManager = function() {
    return {
        modalOpen: false,
        deleteModalOpen: false,
        isEdit: false,
        editId: null,
        deleteId: null,
        loading: false,
        errors: {},
        budgetExceeded: false,
        budgetMessage: '',
        existingAttachments: [],
        deleteAttachments: [],
        selectedFiles: [],
        formData: {
            amount: '',
            currency: 'UAH',
            ministry_id: '',
            category_id: '',
            category_name: '',
            date: new Date().toISOString().split('T')[0],
            description: '',
            payment_method: 'card',
            expense_type: '',
            notes: '',
            force_over_budget: false
        },

        init() {
            window.openExpenseModal = () => this.openCreate();
        },

        openCreate() {
            this.isEdit = false;
            this.editId = null;
            this.resetForm();
            this.errors = {};
            this.budgetExceeded = false;
            this.budgetMessage = '';
            this.existingAttachments = [];
            this.deleteAttachments = [];
            this.selectedFiles = [];
            if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            this.modalOpen = true;
        },

        async openEdit(id) {
            this.loading = true;
            this.errors = {};
            this.budgetExceeded = false;
            this.budgetMessage = '';
            this.existingAttachments = [];
            this.deleteAttachments = [];
            this.selectedFiles = [];

            try {
                const response = await fetch(`/finances/expenses/${id}/edit`, {
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
                    ministry_id: data.transaction.ministry_id || '',
                    category_id: data.transaction.category_id || '',
                    category_name: '',
                    date: data.transaction.date.substring(0, 10),
                    description: data.transaction.description || '',
                    payment_method: data.transaction.payment_method || 'card',
                    expense_type: data.transaction.expense_type || '',
                    notes: data.transaction.notes || '',
                    force_over_budget: false
                };

                this.existingAttachments = data.transaction.attachments || [];
                this.isEdit = true;
                this.editId = id;
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
                this.modalOpen = true;
            } catch (error) {
                showToast('error', 'Помилка завантаження даних');
            } finally {
                this.loading = false;
            }
        },

        handleFileSelect(event) {
            const files = Array.from(event.target.files);
            const maxSize = 10 * 1024 * 1024;
            const rejected = [];
            const accepted = [];
            for (const file of files) {
                if (accepted.length >= 10) break;
                if (file.size > maxSize) {
                    rejected.push(file.name + ' (' + (file.size / 1024 / 1024).toFixed(1) + ' МБ)');
                    continue;
                }
                accepted.push(file);
            }
            this.selectedFiles = accepted;
            if (rejected.length) {
                showToast('error', 'Файл занадто великий (макс. 10 МБ): ' + rejected.join(', '));
            }
        },

        removeFile(index) {
            this.selectedFiles.splice(index, 1);
            if (this.$refs.fileInput) this.$refs.fileInput.value = '';
        },

        toggleDeleteAttachment(id) {
            const idx = this.deleteAttachments.indexOf(id);
            if (idx === -1) {
                this.deleteAttachments.push(id);
            } else {
                this.deleteAttachments.splice(idx, 1);
            }
        },

        async submit() {
            this.loading = true;
            this.errors = {};

            const url = this.isEdit
                ? `/finances/expenses/${this.editId}`
                : '/finances/expenses';

            try {
                const formData = new FormData();
                formData.append('amount', this.formData.amount);
                formData.append('currency', this.formData.currency);
                formData.append('ministry_id', this.formData.ministry_id || '');
                if (this.formData.category_id && this.formData.category_id !== '__custom__') formData.append('category_id', this.formData.category_id);
                if (this.formData.category_id === '__custom__' && this.formData.category_name) formData.append('category_name', this.formData.category_name);
                formData.append('date', this.formData.date);
                formData.append('description', this.formData.description);
                formData.append('payment_method', this.formData.payment_method || '');
                formData.append('expense_type', this.formData.expense_type || '');
                formData.append('notes', this.formData.notes || '');
                if (this.formData.force_over_budget) {
                    formData.append('force_over_budget', '1');
                }

                this.selectedFiles.forEach((file, i) => {
                    formData.append('receipts[]', file);
                });

                this.deleteAttachments.forEach(id => {
                    formData.append('delete_attachments[]', id);
                });

                if (this.isEdit) {
                    formData.append('_method', 'PUT');
                }

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                if (response.status === 413) {
                    showToast('error', 'Файл занадто великий для завантаження. Максимум 10 МБ на файл.');
                    this.loading = false;
                    return;
                }

                const data = await response.json().catch(() => ({}));

                if (response.ok && data.success) {
                    this.modalOpen = false;
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 500);
                } else if (response.status === 422) {
                    if (data.budget_exceeded) {
                        this.budgetExceeded = true;
                        this.budgetMessage = data.message;
                    } else {
                        this.errors = data.errors || {};
                        const errorMsgs = Object.values(data.errors || {}).flat();
                        showToast('error', errorMsgs.length ? errorMsgs[0] : (data.message || 'Помилка валідації'));
                    }
                } else {
                    showToast('error', data.message || 'Помилка збереження');
                }
            } catch (error) {
                showToast('error', 'Помилка збереження. Перевірте розмір файлів (макс. 10 МБ).');
            } finally {
                this.loading = false;
            }
        },

        confirmDelete(id) {
            this.deleteId = id;
            this.deleteModalOpen = true;
        },

        async deleteExpense() {
            if (!this.deleteId) return;

            this.loading = true;

            try {
                const response = await fetch(`/finances/expenses/${this.deleteId}`, {
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
                ministry_id: '',
                category_id: '',
                category_name: '',
                date: new Date().toISOString().split('T')[0],
                description: '',
                payment_method: 'card',
                expense_type: '',
                notes: '',
                force_over_budget: false
            };
            this.existingAttachments = [];
            this.deleteAttachments = [];
            this.selectedFiles = [];
        }
    }
};
</script>

<div x-data="expensesManager()" x-cloak @expense-edit.window="openEdit($event.detail)" @expense-delete.window="confirmDelete($event.detail)">
@include('finances.partials.tabs')

<div id="finance-content" x-data="expensesPage()" @finance-period-changed.window="
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
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg px-4 py-2">
                <p class="text-xs text-blue-600 dark:text-blue-400">Бюджет</p>
                <p class="text-lg font-bold text-blue-700 dark:text-blue-300" x-text="formatNumber(budget) + ' ₴'"></p>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg px-4 py-2">
                <p class="text-xs text-red-600 dark:text-red-400">Витрачено</p>
                <p class="text-lg font-bold text-red-700 dark:text-red-300" x-text="totalFilteredFormatted"></p>
                <span x-show="isFiltered" class="text-xs text-red-500 dark:text-red-400" x-text="'(' + filteredExpenses.length + ' з ' + allExpenses.length + ')'"></span>
            </div>
            <div :class="remaining < 0 ? 'bg-orange-50 dark:bg-orange-900/20' : 'bg-green-50 dark:bg-green-900/20'" class="rounded-lg px-4 py-2">
                <p class="text-xs" :class="remaining < 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400'">Залишок</p>
                <p class="text-lg font-bold" :class="remaining < 0 ? 'text-orange-700 dark:text-orange-300' : 'text-green-700 dark:text-green-300'" x-text="remainingFormatted"></p>
            </div>
        </div>

        <!-- Filter row -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 flex-1">
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
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <!-- Ministry -->
                <select x-model="ministryFilter" @change="currentPage = 1"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500">
                    <option value="">Усі команди</option>
                    @foreach($ministries as $ministry)
                        <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
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
            <button type="button" x-show="isFiltered" @click="clearFilters()" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 whitespace-nowrap">
                Скинути
            </button>
        </div>
    </div>

    <!-- Expenses list -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дата</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Опис</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Команда</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden lg:table-cell">Категорія</th>
                        <th class="px-3 md:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Сума</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- Empty state -->
                    <template x-if="filteredExpenses.length === 0">
                        <tr>
                            <td colspan="5" class="px-3 md:px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <template x-if="isFiltered">
                                    <span>Нічого не знайдено за вашим запитом</span>
                                </template>
                                <template x-if="!isFiltered">
                                    <span>Немає витрат за цей період</span>
                                </template>
                            </td>
                        </tr>
                    </template>

                    <!-- Data rows -->
                    <template x-for="expense in paginatedExpenses" :key="expense.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ auth()->user()->canEdit('finances') ? 'cursor-pointer' : '' }}"
                            @if(auth()->user()->canEdit('finances')) @click="$dispatch('expense-edit', expense.id)" @endif
                            :data-expense-id="expense.id">
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="expense.date">
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-[150px] sm:max-w-none" x-text="expense.description"></p>
                                <template x-if="expense.notes">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 hidden sm:block truncate max-w-[250px]" x-text="expense.notes"></p>
                                </template>
                                <p class="md:hidden text-xs text-gray-400 dark:text-gray-500 mt-0.5" x-text="expense.ministry_name !== '-' ? expense.ministry_name : ''"></p>
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell" x-text="expense.ministry_name">
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 text-sm text-gray-500 dark:text-gray-400 hidden lg:table-cell" x-text="expense.category_name">
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-semibold text-red-600 dark:text-red-400" x-text="'-' + expense.amount_formatted"></span>
                                <template x-if="expense.currency !== 'UAH' && expense.amount_uah">
                                    <span class="block text-xs text-gray-400 dark:text-gray-500" x-text="Math.round(expense.amount_uah).toLocaleString('uk-UA') + ' ₴'"></span>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination footer -->
        <div x-show="filteredExpenses.length > 0" class="px-3 md:px-4 py-2 border-t border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="showFrom + '–' + showTo + ' з ' + filteredExpenses.length"></span>
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
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg relative max-h-[90vh] overflow-y-auto"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop>

            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between sticky top-0 bg-white dark:bg-gray-800 z-10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white"
                    x-text="isEdit ? 'Редагувати витрату' : 'Додати витрату'">
                </h3>
                <button @click="modalOpen = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit()" class="p-6 space-y-4">
                <!-- Ministry -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Команда</label>
                    <select x-model="formData.ministry_id"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                            :class="{ 'border-red-500': errors.ministry_id }">
                        <option value="">Без команди</option>
                        @foreach($ministries as $ministry)
                            <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-red-500 text-sm mt-1" x-show="errors.ministry_id" x-text="errors.ministry_id?.[0]"></p>
                </div>

                <!-- Amount + Currency -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Сума *</label>
                        <div class="flex gap-2">
                            <input type="number" x-model="formData.amount" step="0.01" min="0.01" required
                                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                   :class="{ 'border-red-500': errors.amount }"
                                   placeholder="0.00">
                            @if(count($enabledCurrencies) > 1)
                            <select x-model="formData.currency"
                                    class="w-20 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 text-sm">
                                @foreach($enabledCurrencies as $code)
                                <option value="{{ $code }}">{{ \App\Helpers\CurrencyHelper::SYMBOLS[$code] ?? $code }}</option>
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
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис *</label>
                    <input type="text" x-model="formData.description" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                           :class="{ 'border-red-500': errors.description }"
                           placeholder="Наприклад: струни для гітари">
                    <p class="text-red-500 text-sm mt-1" x-show="errors.description" x-text="errors.description?.[0]"></p>
                </div>

                <!-- Category + Expense Type -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія</label>
                        <select x-model="formData.category_id"
                                :class="{ 'hidden': formData.category_id === '__custom__' }"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="">Без категорії</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                            <option value="__custom__">Інше (ввести вручну)...</option>
                        </select>
                        <div x-show="formData.category_id === '__custom__'" class="flex gap-2">
                            <input type="text" x-model="formData.category_name" placeholder="Назва категорії..."
                                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <button type="button" @click="formData.category_id = ''; formData.category_name = ''"
                                    class="px-3 py-2 text-gray-500 hover:text-red-500 border border-gray-300 dark:border-gray-600 rounded-xl">✕</button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип витрати</label>
                        <select x-model="formData.expense_type"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="">Не вказано</option>
                            <option value="recurring">Регулярна</option>
                            <option value="one_time">Одноразова</option>
                        </select>
                    </div>
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Спосіб оплати</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                               :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': formData.payment_method === 'card' }">
                            <input type="radio" x-model="formData.payment_method" value="card" class="sr-only">
                            <span class="text-sm text-gray-700 dark:text-gray-300">💳 Картка</span>
                        </label>
                        <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                               :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': formData.payment_method === 'cash' }">
                            <input type="radio" x-model="formData.payment_method" value="cash" class="sr-only">
                            <span class="text-sm text-gray-700 dark:text-gray-300">💵 Готівка</span>
                        </label>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нотатки</label>
                    <textarea x-model="formData.notes" rows="2"
                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                              placeholder="Додаткова інформація..."></textarea>
                </div>

                <!-- Existing Attachments (edit mode) -->
                <div x-show="isEdit && existingAttachments.length > 0">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Прикріплені чеки</label>
                    <div class="space-y-2">
                        <template x-for="att in existingAttachments" :key="att.id">
                            <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                 :class="{ 'opacity-50 line-through': deleteAttachments.includes(att.id) }">
                                <div class="flex items-center gap-2">
                                    <template x-if="att.is_image">
                                        <img :src="att.url" class="w-16 h-16 object-cover rounded cursor-pointer hover:opacity-80 transition-opacity" @click="$dispatch('open-lightbox', att.url)">
                                    </template>
                                    <template x-if="!att.is_image">
                                        <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                    </template>
                                    <div>
                                        <template x-if="att.is_image">
                                            <a href="#" @click.prevent="$dispatch('open-lightbox', att.url)" class="text-sm text-primary-600 dark:text-primary-400 hover:underline" x-text="att.original_name"></a>
                                        </template>
                                        <template x-if="!att.is_image">
                                            <a :href="att.url" target="_blank" class="text-sm text-primary-600 dark:text-primary-400 hover:underline" x-text="att.original_name"></a>
                                        </template>
                                        <p class="text-xs text-gray-500" x-text="att.formatted_size"></p>
                                    </div>
                                </div>
                                <button type="button" @click="toggleDeleteAttachment(att.id)"
                                        class="p-1 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded"
                                        :class="{ 'bg-red-100 dark:bg-red-900/30': deleteAttachments.includes(att.id) }">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- New Attachments -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        <span x-show="isEdit">Додати чеки</span>
                        <span x-show="!isEdit">Чеки (фото/PDF)</span>
                    </label>
                    <input type="file" x-ref="fileInput" @change="handleFileSelect" multiple accept="image/*,.heic,.heif,.pdf"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-900/30 dark:file:text-primary-300">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Максимум 10 файлів по 10 МБ (JPG, PNG, HEIC, PDF)</p>
                    <!-- Selected files preview -->
                    <div x-show="selectedFiles.length > 0" class="mt-2 space-y-1">
                        <template x-for="(file, index) in selectedFiles" :key="index">
                            <div class="flex items-center justify-between p-2 bg-green-50 dark:bg-green-900/20 rounded-lg text-sm">
                                <span class="text-green-700 dark:text-green-300 truncate" x-text="file.name"></span>
                                <button type="button" @click="removeFile(index)" class="p-1 text-red-600 hover:bg-red-50 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Budget warning -->
                <div x-show="budgetExceeded" class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-orange-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">Перевищення бюджету</h3>
                            <p class="mt-1 text-sm text-orange-700 dark:text-orange-300" x-text="budgetMessage"></p>
                            <label class="mt-3 flex items-center">
                                <input type="checkbox" x-model="formData.force_over_budget"
                                       class="rounded border-orange-300 text-orange-600 focus:ring-orange-500">
                                <span class="ml-2 text-sm text-orange-800 dark:text-orange-200">Все одно додати витрату</span>
                            </label>
                        </div>
                    </div>
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
                            class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
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
                Видалити витрату?
            </h3>

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                Ви впевнені, що хочете видалити цей запис? Цю дію неможливо скасувати.
            </p>

            <div class="mt-6 flex justify-center space-x-3">
                <button type="button" @click="deleteModalOpen = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Скасувати
                </button>
                <button type="button" @click="deleteExpense()" :disabled="loading"
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
