@extends('layouts.app')

@section('title', 'Редагувати витрату')

@section('content')
<div class="max-w-2xl mx-auto">
    <form method="POST" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Редагувати витрату</h2>

            <div class="space-y-4">
                <div>
                    <label for="ministry_id" class="block text-sm font-medium text-gray-700 mb-1">Служіння *</label>
                    <select name="ministry_id" id="ministry_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @foreach($ministries as $ministry)
                            <option value="{{ $ministry->id }}" {{ $expense->ministry_id == $ministry->id ? 'selected' : '' }}>
                                {{ $ministry->icon }} {{ $ministry->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Сума *</label>
                        <input type="number" name="amount" id="amount" value="{{ old('amount', $expense->amount) }}" required min="0.01" step="0.01"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Дата *</label>
                        <input type="date" name="date" id="date" value="{{ old('date', $expense->date->format('Y-m-d')) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Опис *</label>
                    <input type="text" name="description" id="description" value="{{ old('description', $expense->description) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Категорія</label>
                    <select name="category_id" id="category_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Без категорії</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $expense->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="receipt_photo" class="block text-sm font-medium text-gray-700 mb-1">Фото чека</label>
                    @if($expense->receipt_photo)
                        <div class="mb-2">
                            <img src="{{ Storage::url($expense->receipt_photo) }}" class="max-w-xs rounded">
                        </div>
                    @endif
                    <input type="file" name="receipt_photo" id="receipt_photo" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Нотатки</label>
                    <textarea name="notes" id="notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('notes', $expense->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                  onsubmit="return confirm('Видалити витрату?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800">
                    Видалити
                </button>
            </form>

            <div class="flex items-center space-x-4">
                <a href="{{ route('expenses.index') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900">
                    Скасувати
                </a>
                <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Зберегти
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
