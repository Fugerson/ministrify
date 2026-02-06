@extends('layouts.app')

@section('title', 'Бюджети команд')

@section('content')
@include('finances.partials.tabs')

<div id="finance-content">
<div class="space-y-6" x-data="budgetsPage()">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Бюджети команд</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Розподіл та контроль витрат по командах
            </p>
        </div>

        {{-- Period Selector --}}
        <div class="flex items-center gap-2">
            <select x-model="month" @change="updatePeriod()"
                    class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                @foreach([1 => 'Січень', 2 => 'Лютий', 3 => 'Березень', 4 => 'Квітень', 5 => 'Травень', 6 => 'Червень', 7 => 'Липень', 8 => 'Серпень', 9 => 'Вересень', 10 => 'Жовтень', 11 => 'Листопад', 12 => 'Грудень'] as $m => $name)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <select x-model="year" @change="updatePeriod()"
                    class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                @for($y = now()->year + 1; $y >= 2020; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Загальний бюджет</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        {{ number_format($totals['budget'], 0, ',', ' ') }} <span class="text-lg text-gray-500">₴</span>
                    </p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Витрачено</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
                        {{ number_format($totals['spent'], 0, ',', ' ') }} <span class="text-lg">₴</span>
                    </p>
                </div>
                <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Залишок</p>
                    <p class="text-2xl font-bold {{ $totals['remaining'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} mt-1">
                        {{ number_format($totals['remaining'], 0, ',', ' ') }} <span class="text-lg">₴</span>
                    </p>
                </div>
                <div class="p-3 {{ $totals['remaining'] >= 0 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} rounded-lg">
                    <svg class="w-6 h-6 {{ $totals['remaining'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress Overview --}}
    @if($totals['budget'] > 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Використано загального бюджету</span>
            <span class="text-sm font-semibold {{ ($totals['spent'] / $totals['budget']) * 100 > 100 ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                {{ number_format(($totals['spent'] / $totals['budget']) * 100, 1) }}%
            </span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
            @php
                $percentage = min(100, ($totals['spent'] / $totals['budget']) * 100);
                $color = $percentage > 100 ? 'bg-red-600' : ($percentage > 80 ? 'bg-orange-500' : 'bg-green-500');
            @endphp
            <div class="{{ $color }} h-3 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
        </div>
    </div>
    @endif

    {{-- Ministry Budgets Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Бюджети по командах</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Команда
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Бюджет
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Витрачено
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Залишок
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48">
                            Прогрес
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20">
                            Дії
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($ministries as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $item['ministry']->color ?? '#6b7280' }}"></div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $item['ministry']->name }}</div>
                                    @if($item['ministry']->leader)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item['ministry']->leader->full_name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ number_format($item['monthly_budget'], 0, ',', ' ') }} ₴
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <span class="text-red-600 dark:text-red-400">
                                {{ number_format($item['spent'], 0, ',', ' ') }} ₴
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <span class="{{ $item['remaining'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ number_format($item['remaining'], 0, ',', ' ') }} ₴
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($item['monthly_budget'] > 0)
                                @php
                                    $pct = min(100, $item['percentage']);
                                    $barColor = $item['percentage'] > 100 ? 'bg-red-600' : ($item['percentage'] > 80 ? 'bg-orange-500' : 'bg-green-500');
                                @endphp
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                        <div class="{{ $barColor }} h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs {{ $item['percentage'] > 100 ? 'text-red-600' : 'text-gray-500' }} w-12 text-right">{{ number_format($item['percentage'], 0) }}%</span>
                                </div>
                            @else
                                <span class="text-xs text-gray-400 dark:text-gray-500">Не задано</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button @click="openBudgetModal({{ $item['ministry']->id }}, '{{ $item['ministry']->name }}', {{ $item['monthly_budget'] }}, '{{ $item['budget']?->notes ?? '' }}')"
                                    class="p-2 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    title="Редагувати бюджет">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            Немає команд
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Expenses Missing Receipts --}}
    @if($expensesMissingReceipts->count() > 0)
    <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl border border-orange-200 dark:border-orange-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-orange-200 dark:border-orange-800 flex items-center">
            <svg class="w-5 h-5 text-orange-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <h2 class="text-lg font-semibold text-orange-800 dark:text-orange-200">Витрати без чеків</h2>
        </div>

        <div class="divide-y divide-orange-200 dark:divide-orange-800">
            @foreach($expensesMissingReceipts as $expense)
            <div class="px-6 py-3 flex items-center justify-between">
                <div>
                    <div class="font-medium text-orange-900 dark:text-orange-100">{{ $expense->description }}</div>
                    <div class="text-sm text-orange-600 dark:text-orange-400">
                        {{ $expense->ministry?->name ?? 'Без команди' }} &bull; {{ $expense->date->format('d.m.Y') }}
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="font-semibold text-orange-900 dark:text-orange-100">
                        {{ number_format($expense->amount, 0, ',', ' ') }} ₴
                    </span>
                    <button type="button" onclick="window.openExpenseEdit && window.openExpenseEdit({{ $expense->id }})"
                       class="px-3 py-1 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors">
                        Додати чек
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Budget Edit Modal --}}
    <div x-show="showBudgetModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" @click="showBudgetModal = false"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Бюджет: <span x-text="budgetMinistryName"></span>
                </h3>

                <form :action="'/finances/budgets/' + budgetMinistryId" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="year" :value="year">
                    <input type="hidden" name="month" :value="month">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Місячний бюджет (₴)
                        </label>
                        <input type="number" name="monthly_budget" x-model="budgetAmount" min="0" step="100"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Нотатки
                        </label>
                        <textarea name="notes" x-model="budgetNotes" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showBudgetModal = false"
                                class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            Скасувати
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                            Зберегти
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function budgetsPage() {
    return {
        year: {{ $year }},
        month: {{ $month }},
        showBudgetModal: false,
        budgetMinistryId: null,
        budgetMinistryName: '',
        budgetAmount: 0,
        budgetNotes: '',

        updatePeriod() {
            window.location.href = `{{ route('finances.budgets') }}?year=${this.year}&month=${this.month}`;
        },

        openBudgetModal(ministryId, ministryName, amount, notes) {
            this.budgetMinistryId = ministryId;
            this.budgetMinistryName = ministryName;
            this.budgetAmount = amount;
            this.budgetNotes = notes;
            this.showBudgetModal = true;
        }
    }
}
</script>
</div><!-- /finance-content -->

<!-- Expense Edit Modal -->
<div x-data="expenseEditModal()" x-cloak>
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
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Редагувати витрату</h3>
                    <button @click="modalOpen = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Loading -->
                <div x-show="loadingData" class="p-8 text-center">
                    <svg class="animate-spin h-8 w-8 mx-auto text-primary-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>

                <form x-show="!loadingData" @submit.prevent="submit()" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Сума *</label>
                        <div class="flex gap-2">
                            <input type="number" x-model="formData.amount" step="0.01" min="0.01" required
                                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <select x-model="formData.currency"
                                    class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                @foreach($enabledCurrencies ?? ['UAH', 'USD', 'EUR'] as $curr)
                                    <option value="{{ $curr }}">{{ $curr }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис *</label>
                        <input type="text" x-model="formData.description" required maxlength="255"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата *</label>
                        <input type="date" x-model="formData.date" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія</label>
                        <select x-model="formData.category_id"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Без категорії</option>
                            @foreach($expenseCategories ?? [] as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->icon ?? '💸' }} {{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Команда</label>
                        <select x-model="formData.ministry_id"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Без команди</option>
                            @foreach($ministries as $m)
                                <option value="{{ $m['ministry']->id }}">{{ $m['ministry']->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- File upload notice -->
                    <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3">
                        <p class="text-sm text-amber-700 dark:text-amber-300">
                            Для додавання чеку (файлу) перейдіть на
                            <a :href="'/finances/expenses/' + editId + '/edit'" class="underline font-medium">повну сторінку редагування</a>
                        </p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" @click="modalOpen = false"
                                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl">
                            Скасувати
                        </button>
                        <button type="submit" :disabled="loading"
                                class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl disabled:opacity-50">
                            <span x-show="!loading">Зберегти</span>
                            <span x-show="loading">Збереження...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.expenseEditModal = function() {
    return {
        modalOpen: false,
        loading: false,
        loadingData: false,
        editId: null,
        formData: {
            amount: '',
            currency: 'UAH',
            description: '',
            category_id: '',
            ministry_id: '',
            date: ''
        },
        init() {
            window.openExpenseEdit = (id) => this.openEdit(id);
        },
        async openEdit(id) {
            this.editId = id;
            this.loadingData = true;
            this.modalOpen = true;

            try {
                const response = await fetch(`/finances/expenses/${id}/edit`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                const t = data.transaction;

                this.formData = {
                    amount: t.amount,
                    currency: t.currency || 'UAH',
                    description: t.description || '',
                    category_id: t.category_id || '',
                    ministry_id: t.ministry_id || '',
                    date: t.date.split('T')[0]
                };
            } catch (e) {
                showToast('error', 'Помилка завантаження');
                this.modalOpen = false;
            } finally {
                this.loadingData = false;
            }
        },
        async submit() {
            this.loading = true;
            try {
                const response = await fetch(`/finances/expenses/${this.editId}`, {
                    method: 'PUT',
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
</script>
@endpush
@endsection
