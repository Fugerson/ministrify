@extends('layouts.app')

@section('title', 'Імпорт календаря')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Імпорт календаря</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Завантажте файл iCal (.ics) для імпорту подій
            </p>
        </div>

        <form action="{{ route('calendar.import.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <!-- Info Box -->
            <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Як імпортувати з Google Calendar?</h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-400 space-y-1">
                            <p>1. Відкрийте Google Calendar на комп'ютері</p>
                            <p>2. Натисніть на шестерню (Налаштування)</p>
                            <p>3. Виберіть "Імпорт та експорт" -> "Експортувати"</p>
                            <p>4. Завантажиться .zip архів з файлами .ics</p>
                            <p>5. Розпакуйте та завантажте потрібний .ics файл тут</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ministry Selection -->
            <div>
                <label for="ministry_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Служіння для імпортованих подій
                </label>
                <select name="ministry_id" id="ministry_id" required
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">Виберіть служіння...</option>
                    @foreach($ministries as $ministry)
                        <option value="{{ $ministry->id }}">{{ $ministry->icon }} {{ $ministry->name }}</option>
                    @endforeach
                </select>
                @error('ministry_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- File Upload -->
            <div>
                <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Файл календаря (.ics)
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-xl hover:border-primary-400 dark:hover:border-primary-500 transition-colors">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div class="flex text-sm text-gray-600 dark:text-gray-400">
                            <label for="file" class="relative cursor-pointer rounded-md font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 focus-within:outline-none">
                                <span>Виберіть файл</span>
                                <input id="file" name="file" type="file" class="sr-only" accept=".ics,.txt" required>
                            </label>
                            <p class="pl-1">або перетягніть сюди</p>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">iCal файл до 5MB</p>
                    </div>
                </div>
                <p id="file-name" class="mt-2 text-sm text-gray-600 dark:text-gray-400 hidden"></p>
                @error('file')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('schedule') }}"
                   class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
                    Скасувати
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Імпортувати
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('file').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        const fileNameEl = document.getElementById('file-name');
        if (fileName) {
            fileNameEl.textContent = 'Вибрано: ' + fileName;
            fileNameEl.classList.remove('hidden');
        } else {
            fileNameEl.classList.add('hidden');
        }
    });
</script>
@endsection
