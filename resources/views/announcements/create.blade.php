@extends('layouts.app')

@section('title', 'Нове оголошення')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('announcements.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Назад до оголошень
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Нове оголошення</h1>

        <form action="{{ route('announcements.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Заголовок *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                       placeholder="Введіть заголовок оголошення">
                @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Текст оголошення *</label>
                <textarea name="content" rows="8" required
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
                          placeholder="Напишіть текст оголошення...">{{ old('content') }}</textarea>
                @error('content')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Актуально до (необов'язково)</label>
                <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Після цієї дати оголошення автоматично сховається</p>
                @error('expires_at')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_pinned" value="1" id="is_pinned" {{ old('is_pinned') ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:bg-gray-700">
                <label for="is_pinned" class="ml-3 text-gray-700 dark:text-gray-300">
                    Закріпити зверху
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('announcements.index') }}"
                   class="px-6 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-xl">
                    Скасувати
                </a>
                <button type="submit"
                        class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl">
                    Опублікувати
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
