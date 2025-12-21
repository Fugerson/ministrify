@extends('layouts.app')

@section('title', 'Додати витрату')

@section('content')
<div class="max-w-2xl mx-auto">
    <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Нова витрата</h2>

            <div class="space-y-4">
                <div>
                    <label for="ministry_id" class="block text-sm font-medium text-gray-700 mb-1">Служіння *</label>
                    <select name="ministry_id" id="ministry_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Виберіть служіння</option>
                        @foreach($ministries as $ministry)
                            <option value="{{ $ministry->id }}" {{ old('ministry_id') == $ministry->id ? 'selected' : '' }}>
                                {{ $ministry->icon }} {{ $ministry->name }}
                                @if($ministry->monthly_budget)
                                    (залишок: {{ number_format($ministry->remaining_budget, 0, ',', ' ') }} &#8372;)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Сума *</label>
                        <div class="relative">
                            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required min="0.01" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                   placeholder="0.00">
                            <span class="absolute right-3 top-2 text-gray-500">&#8372;</span>
                        </div>
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Дата *</label>
                        <input type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Опис *</label>
                    <input type="text" name="description" id="description" value="{{ old('description') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Струни для гітари">
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Категорія</label>
                    <select name="category_id" id="category_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Без категорії</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="receipt_photo" class="block text-sm font-medium text-gray-700 mb-1">Фото чека</label>
                    <input type="file" name="receipt_photo" id="receipt_photo" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Нотатки</label>
                    <textarea name="notes" id="notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('expenses.index') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900">
                Скасувати
            </a>
            <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Зберегти
            </button>
        </div>
    </form>
</div>
@endsection
