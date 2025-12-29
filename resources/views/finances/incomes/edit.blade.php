@extends('layouts.app')

@section('title', 'Редагувати надходження')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('finances.incomes') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Редагувати надходження</h2>
            <x-delete-confirm
                :action="route('finances.incomes.destroy', $income)"
                title="Видалити надходження?"
                message="Ви впевнені, що хочете видалити це надходження? Цю дію неможливо скасувати."
                button-text="Видалити"
                :icon="false"
                class="text-sm"
            />
        </div>

        <form action="{{ route('finances.incomes.update', $income) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Сума <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="amount" value="{{ old('amount', $income->amount) }}" step="0.01" min="0.01" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="0.00">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">₴</span>
                    </div>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Дата <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date" value="{{ old('date', $income->date->format('Y-m-d')) }}" required
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
                <select name="category_id" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Оберіть категорію</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $income->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->icon ?? '💰' }} {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Payment Method -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Спосіб оплати <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach(['cash' => '💵 Готівка', 'card' => '💳 Картка', 'transfer' => '🏦 Переказ', 'online' => '🌐 Онлайн'] as $value => $label)
                        <label class="relative flex items-center justify-center px-4 py-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <input type="radio" name="payment_method" value="{{ $value }}" {{ old('payment_method', $income->payment_method) == $value ? 'checked' : '' }} class="sr-only peer">
                            <span class="text-sm text-gray-700 dark:text-gray-300 peer-checked:text-primary-600 dark:peer-checked:text-primary-400 peer-checked:font-medium">{{ $label }}</span>
                            <div class="absolute inset-0 border-2 border-transparent peer-checked:border-primary-500 rounded-lg pointer-events-none"></div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Anonymous checkbox -->
            <div x-data="{ anonymous: {{ old('is_anonymous', $income->is_anonymous) ? 'true' : 'false' }} }">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_anonymous" x-model="anonymous" value="1"
                           class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Анонімне пожертвування</span>
                </label>

                <!-- Donor (if not anonymous) -->
                <div x-show="!anonymous" x-transition class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Жертводавець
                    </label>
                    <x-person-select
                        name="person_id"
                        :people="$people"
                        :selected="old('person_id', $income->person_id)"
                        placeholder="Почніть вводити ім'я..."
                        null-text="Не вказано"
                    />
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Опис
                </label>
                <input type="text" name="description" value="{{ old('description', $income->description) }}" maxlength="255"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Додатковий опис (необов'язково)">
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Нотатки
                </label>
                <textarea name="notes" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="Внутрішні нотатки...">{{ old('notes', $income->notes) }}</textarea>
            </div>

            <!-- Submit -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('finances.incomes') }}"
                   class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    Скасувати
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                    Зберегти
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
