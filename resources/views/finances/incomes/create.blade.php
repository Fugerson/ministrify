@extends('layouts.app')

@section('title', 'Додати надходження')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('finances.incomes') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Нове надходження</h2>
        </div>

        <form action="{{ route('finances.incomes.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ currency: '{{ old('currency', 'UAH') }}', exchangeRates: {{ json_encode($exchangeRates) }} }">
                <!-- Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Сума <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01" required
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                   placeholder="0.00">
                        </div>
                        @if(count($enabledCurrencies) > 1)
                        <select name="currency" x-model="currency"
                                class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @foreach($enabledCurrencies as $curr)
                                <option value="{{ $curr }}">{{ \App\Helpers\CurrencyHelper::symbol($curr) }} {{ $curr }}</option>
                            @endforeach
                        </select>
                        @else
                        <input type="hidden" name="currency" value="UAH">
                        <span class="inline-flex items-center px-3 py-2 text-gray-500 dark:text-gray-400">₴</span>
                        @endif
                    </div>
                    <template x-if="currency !== 'UAH' && exchangeRates[currency]">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Курс НБУ: 1 <span x-text="currency"></span> = <span x-text="exchangeRates[currency]?.toFixed(2)"></span> ₴
                        </p>
                    </template>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Дата <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('date')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Категорія <span class="text-red-500">*</span>
                </label>
                @php
                    $categoriesWithIcon = $categories->map(function($c) {
                        $c->display_name = ($c->icon ?? '') . ' ' . $c->name;
                        return $c;
                    });
                @endphp
                <x-searchable-select
                    name="category_id"
                    :items="$categoriesWithIcon"
                    :selected="old('category_id')"
                    labelKey="display_name"
                    valueKey="id"
                    colorKey="color"
                    :searchKeys="['name', 'display_name']"
                    placeholder="Пошук категорії..."
                    nullText="Оберіть категорію"
                    :nullable="false"
                    required
                />
                @error('category_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Payment Method -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Спосіб оплати <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    @foreach(['cash' => '💵 Готівка', 'card' => '💳 Картка'] as $value => $label)
                        <label class="relative flex items-center justify-center px-4 py-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <input type="radio" name="payment_method" value="{{ $value }}" {{ old('payment_method', 'cash') == $value ? 'checked' : '' }} class="sr-only peer">
                            <span class="text-sm text-gray-700 dark:text-gray-300 peer-checked:text-primary-600 dark:peer-checked:text-primary-400 peer-checked:font-medium">{{ $label }}</span>
                            <div class="absolute inset-0 border-2 border-transparent peer-checked:border-primary-500 rounded-lg pointer-events-none"></div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Always anonymous -->
            <input type="hidden" name="is_anonymous" value="1">

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Нотатки
                </label>
                <textarea name="notes" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="Внутрішні нотатки...">{{ old('notes') }}</textarea>
            </div>

            <!-- Submit -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('finances.incomes') }}"
                   class="w-full sm:w-auto px-4 py-2 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    Скасувати
                </a>
                <button type="submit"
                        class="w-full sm:w-auto px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                    Зберегти
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
