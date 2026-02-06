@extends('layouts.app')

@section('title', 'Надходження')

@section('actions')
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
@endsection

@section('content')
<!-- Scripts must be defined BEFORE Alpine components that use them -->
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

                const data = await response.json();

                this.formData = {
                    amount: data.transaction.amount,
                    currency: data.transaction.currency || 'UAH',
                    category_id: data.transaction.category_id || '',
                    date: data.transaction.date.substring(0, 10),
                    payment_method: data.transaction.payment_method || 'cash',
                    notes: data.transaction.notes || '',
                    is_anonymous: true
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
                const response = await fetch(url, {
                    method: this.isEdit ? 'PUT' : 'POST',
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

                const data = await response.json();

                if (response.ok && data.success) {
                    this.deleteModalOpen = false;
                    showToast('success', data.message);

                    const row = document.querySelector(`tr[data-income-id="${this.deleteId}"]`);
                    if (row) {
                        row.style.transition = 'opacity 0.3s';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    }

                    setTimeout(() => location.reload(), 1000);
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
                date: new Date().toISOString().split('T')[0],
                payment_method: 'cash',
                notes: '',
                is_anonymous: true
            };
        }
    }
};
</script>

<div x-data="incomesManager()" x-cloak>
@include('finances.partials.tabs')

<div id="finance-content" x-data @finance-period-changed.window="handlePeriodReload($event.detail)">
<div class="space-y-6">

    <!-- Summary card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 md:p-6">
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 inline-block">
            <p class="text-sm text-green-600 dark:text-green-400">Загалом за період</p>
            <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ number_format($totals['total'], 0, ',', ' ') }} ₴</p>
        </div>
    </div>

    <!-- Incomes list -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="px-3 md:px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <form method="GET" class="flex items-center space-x-4">
                @if(request('start_date'))
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                @endif
                @if(request('end_date'))
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                @endif
                <select name="category" onchange="this.form.submit()"
                        class="w-full sm:w-auto px-3 py-2.5 sm:py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    <option value="">Усі категорії</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->icon ?? '💰' }} {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дата</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden sm:table-cell">Категорія</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Спосіб</th>
                        <th class="px-3 md:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Сума</th>
                        <th class="px-3 md:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($incomes as $income)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50" data-income-id="{{ $income->id }}">
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $income->date->format('d.m') }}</div>
                                <!-- Mobile: show category under date -->
                                <div class="sm:hidden text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $income->category?->icon ?? '💰' }} {{ $income->category?->name ?? 'Без категорії' }}
                                </div>
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap hidden sm:table-cell">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: {{ $income->category?->color ?? '#3B82F6' }}30; color: {{ $income->category?->color ?? '#3B82F6' }}">
                                    {{ $income->category?->icon ?? '💰' }} {{ $income->category?->name ?? 'Без категорії' }}
                                </span>
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                {{ $income->payment_method_label }}
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                                    +{{ \App\Helpers\CurrencyHelper::format($income->amount, $income->currency ?? 'UAH') }}
                                </span>
                                @if(($income->currency ?? 'UAH') !== 'UAH' && $income->amount_uah)
                                <span class="block text-xs text-gray-400 dark:text-gray-500">
                                    {{ number_format($income->amount_uah, 0, ',', ' ') }} ₴
                                </span>
                                @endif
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" @click.prevent.stop="openEdit({{ $income->id }})"
                                            class="p-2 text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button type="button" @click.prevent.stop="confirmDelete({{ $income->id }})"
                                            class="p-2 text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 md:px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                Немає надходжень за цей місяць
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($incomes->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $incomes->withQueryString()->links() }}
            </div>
        @endif
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
                        @if(count($categories) > 0)
                        <select x-model="formData.currency"
                                class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="UAH">₴ UAH</option>
                            <option value="USD">$ USD</option>
                            <option value="EUR">€ EUR</option>
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
                    <select x-model="formData.category_id" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                            :class="{ 'border-red-500': errors.category_id }">
                        <option value="">Оберіть категорію</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->icon ?? '💰' }} {{ $cat->name }}</option>
                        @endforeach
                    </select>
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
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
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

            <!-- Title -->
            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white text-center">
                Видалити надходження?
            </h3>

            <!-- Message -->
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                Ви впевнені, що хочете видалити цей запис? Цю дію неможливо скасувати.
            </p>

            <!-- Actions -->
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
                                @foreach($enabledCurrencies ?? ['UAH', 'USD', 'EUR'] as $curr)
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
                                   class="flex-1 px-4 py-2 border border-green-200 dark:border-green-800 rounded-lg bg-gray-50 dark:bg-gray-600 text-gray-900 dark:text-white">
                            <select x-model="formData.to_currency" @change="updateRate()"
                                    class="w-24 px-2 py-2 border border-green-200 dark:border-green-800 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                @foreach($enabledCurrencies ?? ['UAH', 'USD', 'EUR'] as $curr)
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
