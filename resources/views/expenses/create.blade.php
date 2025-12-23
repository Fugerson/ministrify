@extends('layouts.app')

@section('title', 'Додати витрату')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('finances.expenses.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад
    </a>

    <form method="POST" action="{{ route('finances.expenses.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Нова витрата</h2>

            <div class="space-y-4">
                <div>
                    <label for="ministry_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Служіння <span class="text-red-500">*</span></label>
                    <select name="ministry_id" id="ministry_id" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Виберіть служіння</option>
                        @foreach($ministries as $ministry)
                            <option value="{{ $ministry->id }}" {{ old('ministry_id') == $ministry->id ? 'selected' : '' }}>
                                {{ $ministry->name }}
                                @if($ministry->monthly_budget)
                                    (залишок: {{ number_format($ministry->remaining_budget, 0, ',', ' ') }} ₴)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('ministry_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Сума <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required min="0.01" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                   placeholder="0.00">
                            <span class="absolute right-3 top-2 text-gray-500 dark:text-gray-400">₴</span>
                        </div>
                        @error('amount')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата <span class="text-red-500">*</span></label>
                        <input type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('date')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис <span class="text-red-500">*</span></label>
                    <input type="text" name="description" id="description" value="{{ old('description') }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Струни для гітари">
                    @error('description')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія</label>
                    <select name="category_id" id="category_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Без категорії</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="receipt_photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Фото чека</label>
                    <input type="file" name="receipt_photo" id="receipt_photo" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нотатки</label>
                    <textarea name="notes" id="notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('finances.expenses.index') }}" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                Скасувати
            </a>
            <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                Зберегти
            </button>
        </div>
    </form>
</div>
@endsection
