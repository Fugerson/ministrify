@extends('layouts.app')

@section('title', 'Витрати')

@section('actions')
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
    <a href="{{ route('finances.exchange.create') }}"
       class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        Обмін
    </a>
</div>
@endsection

@section('content')
<div x-data="expensesManager" x-cloak>
@include('finances.partials.tabs')

<div id="finance-content">
@php
    $months = ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];
@endphp

<div class="space-y-6">

    <!-- Summary card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 md:p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-2 sm:space-x-4">
                <a href="{{ route('finances.expenses.index', ['year' => $month == 1 ? $year - 1 : $year, 'month' => $month == 1 ? 12 : $month - 1]) }}"
                   class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">{{ $months[$month - 1] }} {{ $year }}</h2>
                <a href="{{ route('finances.expenses.index', ['year' => $month == 12 ? $year + 1 : $year, 'month' => $month == 12 ? 1 : $month + 1]) }}"
                   class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            @admin
            <a href="{{ route('finances.index') }}" class="text-primary-600 dark:text-primary-400 hover:text-primary-500 text-sm hidden sm:inline">
                Повний звіт
            </a>
            @endadmin
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-6">
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <p class="text-sm text-blue-600 dark:text-blue-400">Бюджет</p>
                <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ number_format($totals['budget'], 0, ',', ' ') }} ₴</p>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                <p class="text-sm text-red-600 dark:text-red-400">Витрачено</p>
                <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ number_format($totals['spent'], 0, ',', ' ') }} ₴</p>
            </div>
            <div class="bg-{{ $totals['budget'] - $totals['spent'] < 0 ? 'orange' : 'green' }}-50 dark:bg-{{ $totals['budget'] - $totals['spent'] < 0 ? 'orange' : 'green' }}-900/20 rounded-lg p-4">
                <p class="text-sm {{ $totals['budget'] - $totals['spent'] < 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400' }}">Залишок</p>
                <p class="text-2xl font-bold {{ $totals['budget'] - $totals['spent'] < 0 ? 'text-orange-700 dark:text-orange-300' : 'text-green-700 dark:text-green-300' }}">
                    {{ number_format($totals['budget'] - $totals['spent'], 0, ',', ' ') }} ₴
                </p>
            </div>
        </div>
    </div>

    <!-- Expenses list -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="px-3 md:px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <form method="GET" class="flex items-center space-x-4">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <select name="ministry" onchange="this.form.submit()"
                        class="w-full sm:w-auto px-3 py-2.5 sm:py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    <option value="">Всі команди</option>
                    @foreach($ministries as $ministry)
                        <option value="{{ $ministry->id }}" {{ request('ministry') == $ministry->id ? 'selected' : '' }}>
                            {{ $ministry->name }}
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
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Опис</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Команда</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden lg:table-cell">Категорія</th>
                        <th class="px-3 md:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Сума</th>
                        <th class="px-3 md:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50" data-expense-id="{{ $expense->id }}">
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $expense->date->format('d.m') }}
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-[150px] sm:max-w-none">{{ $expense->description }}</p>
                                @if($expense->notes)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 hidden sm:block">{{ Str::limit($expense->notes, 50) }}</p>
                                @endif
                                <!-- Mobile: show ministry under description -->
                                <p class="md:hidden text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $expense->ministry?->name ?? '' }}</p>
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                {{ $expense->ministry?->name ?? '-' }}
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 text-sm text-gray-500 dark:text-gray-400 hidden lg:table-cell">
                                {{ $expense->category?->name ?? '-' }}
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                                    -{{ \App\Helpers\CurrencyHelper::format($expense->amount, $expense->currency ?? 'UAH') }}
                                </span>
                                @if(($expense->currency ?? 'UAH') !== 'UAH' && $expense->amount_uah)
                                <span class="block text-xs text-gray-400 dark:text-gray-500">
                                    {{ number_format($expense->amount_uah, 0, ',', ' ') }} ₴
                                </span>
                                @endif
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" @click.prevent.stop="openEdit({{ $expense->id }})"
                                            class="p-2 text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button type="button" @click.prevent.stop="confirmDelete({{ $expense->id }})"
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
                            <td colspan="6" class="px-3 md:px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                Немає витрат за цей місяць
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $expenses->withQueryString()->links() }}
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Команда *</label>
                    <select x-model="formData.ministry_id" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                            :class="{ 'border-red-500': errors.ministry_id }">
                        <option value="">Оберіть команду</option>
                        @foreach($ministries as $ministry)
                            <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-red-500 text-sm mt-1" x-show="errors.ministry_id" x-text="errors.ministry_id?.[0]"></p>
                </div>

                <!-- Amount + Currency -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Сума *</label>
                        <div class="flex gap-2">
                            <input type="number" x-model="formData.amount" step="0.01" min="0.01" required
                                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                   :class="{ 'border-red-500': errors.amount }"
                                   placeholder="0.00">
                            <select x-model="formData.currency"
                                    class="w-20 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 text-sm">
                                <option value="UAH">₴</option>
                                <option value="USD">$</option>
                                <option value="EUR">€</option>
                            </select>
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
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія</label>
                        <select x-model="formData.category_id"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="">Без категорії</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
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
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
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

            <!-- Title -->
            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white text-center">
                Видалити витрату?
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

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('expensesManager', () => ({
        modalOpen: false,
        deleteModalOpen: false,
        isEdit: false,
        editId: null,
        deleteId: null,
        loading: false,
        errors: {},
        budgetExceeded: false,
        budgetMessage: '',
        formData: {
            amount: '',
            currency: 'UAH',
            ministry_id: '',
            category_id: '',
            date: new Date().toISOString().split('T')[0],
            description: '',
            payment_method: 'card',
            expense_type: '',
            notes: '',
            force_over_budget: false
        },

        init() {
            // Expose openCreate to window for header button
            window.openExpenseModal = () => this.openCreate();
        },

        openCreate() {
            this.isEdit = false;
            this.editId = null;
            this.resetForm();
            this.errors = {};
            this.budgetExceeded = false;
            this.budgetMessage = '';
            this.modalOpen = true;
        },

        async openEdit(id) {
            this.loading = true;
            this.errors = {};
            this.budgetExceeded = false;
            this.budgetMessage = '';

            try {
                const response = await fetch(`/finances/expenses/${id}/edit`, {
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
                    ministry_id: data.transaction.ministry_id || '',
                    category_id: data.transaction.category_id || '',
                    date: data.transaction.date.split('T')[0],
                    description: data.transaction.description || '',
                    payment_method: data.transaction.payment_method || 'card',
                    expense_type: data.transaction.expense_type || '',
                    notes: data.transaction.notes || '',
                    force_over_budget: false
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
                ? `/finances/expenses/${this.editId}`
                : '/finances/expenses';

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
                    // Reload page to show updated data
                    setTimeout(() => location.reload(), 500);
                } else if (response.status === 422) {
                    if (data.budget_exceeded) {
                        this.budgetExceeded = true;
                        this.budgetMessage = data.message;
                    } else {
                        this.errors = data.errors || {};
                        if (data.message && !data.errors) {
                            showToast('error', data.message);
                        }
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

                const data = await response.json();

                if (response.ok && data.success) {
                    this.deleteModalOpen = false;
                    showToast('success', data.message);

                    // Remove row from table
                    const row = document.querySelector(`tr[data-expense-id="${this.deleteId}"]`);
                    if (row) {
                        row.style.transition = 'opacity 0.3s';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    }

                    // Reload after short delay to update totals
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
                ministry_id: '',
                category_id: '',
                date: new Date().toISOString().split('T')[0],
                description: '',
                payment_method: 'card',
                expense_type: '',
                notes: '',
                force_over_budget: false
            };
        }
    }));
});
</script>
@endsection
