@extends('layouts.app')

@section('title', 'Новий запит')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('support.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Новий запит до підтримки</h1>
        </div>

        <form action="{{ route('support.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Тип запиту</label>
                <select name="category" id="category" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="question" {{ old('category') === 'question' ? 'selected' : '' }}>Питання</option>
                    <option value="bug" {{ old('category') === 'bug' ? 'selected' : '' }}>Повідомити про помилку</option>
                    <option value="feature" {{ old('category') === 'feature' ? 'selected' : '' }}>Пропозиція</option>
                    <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Інше</option>
                </select>
                @error('category')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Тема</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                       placeholder="Коротко опишіть проблему або питання"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                @error('subject')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Опис</label>
                <textarea name="message" id="message" rows="6" required
                          placeholder="Детально опишіть вашу проблему або питання. Чим більше деталей, тим швидше ми зможемо допомогти."
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('message') }}</textarea>
                @error('message')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Надіслати
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
