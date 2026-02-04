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

        <form action="{{ route('support.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
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

            <div x-data="{ files: [] }">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Скріншоти (необов'язково)</label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 text-center hover:border-primary-500 transition-colors">
                    <input type="file" name="attachments[]" multiple accept="image/*,.pdf"
                           @change="files = Array.from($event.target.files)"
                           class="hidden" id="attachments">
                    <label for="attachments" class="cursor-pointer">
                        <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Натисніть для вибору файлів</p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">PNG, JPG, PDF до 5MB</p>
                    </label>
                </div>
                <template x-if="files.length > 0">
                    <div class="mt-3 space-y-2">
                        <template x-for="(file, index) in files" :key="index">
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-text="file.name"></span>
                                <span class="text-xs text-gray-400" x-text="'(' + (file.size / 1024 / 1024).toFixed(2) + ' MB)'"></span>
                            </div>
                        </template>
                    </div>
                </template>
                @error('attachments.*')
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
