@extends('layouts.app')

@section('title', 'Нова дошка')

@section('content')
<div class="max-w-2xl mx-auto">
    <form method="POST" action="{{ route('boards.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Інформація про дошку</h2>

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Назва *</label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}"
                           placeholder="Наприклад: Підготовка до Різдва"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Опис</label>
                    <textarea name="description" id="description" rows="3"
                              placeholder="Короткий опис дошки..."
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ old('description') }}</textarea>
                </div>

                <div x-data="{ color: '{{ old('color', '#6366f1') }}' }">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Колір</label>
                    <div class="flex items-center gap-3">
                        @php
                            $colors = ['#ef4444', '#f97316', '#f59e0b', '#84cc16', '#22c55e', '#14b8a6', '#06b6d4', '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#ec4899'];
                        @endphp
                        @foreach($colors as $c)
                            <button type="button"
                                    @click="color = '{{ $c }}'"
                                    class="w-8 h-8 rounded-lg transition-transform hover:scale-110"
                                    :class="color === '{{ $c }}' ? 'ring-2 ring-offset-2 dark:ring-offset-gray-800 ring-gray-900 dark:ring-white scale-110' : ''"
                                    style="background-color: {{ $c }}"></button>
                        @endforeach
                    </div>
                    <input type="hidden" name="color" :value="color">
                </div>
            </div>
        </div>

        <!-- Info about default columns -->
        <div class="bg-blue-50 dark:bg-blue-900/30 rounded-xl p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    Дошка буде створена з 4 колонками за замовчуванням: "До виконання", "В процесі", "На перевірці", "Завершено". Ви зможете змінити їх пізніше.
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('boards.index') }}"
               class="px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                Скасувати
            </a>
            <button type="submit"
                    class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                Створити дошку
            </button>
        </div>
    </form>
</div>
@endsection
