@extends('layouts.app')

@section('title', 'Імпорт календаря')

@section('content')
<div class="max-w-2xl mx-auto" x-data="{ importMethod: 'url' }">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Імпорт календаря</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Синхронізуйте події з Google Calendar або завантажте файл
            </p>
        </div>

        <!-- Import Method Toggle -->
        <div class="px-6 pt-6">
            <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-xl p-1">
                <button type="button" @click="importMethod = 'url'"
                        :class="importMethod === 'url' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' : 'text-gray-600 dark:text-gray-400'"
                        class="flex-1 px-4 py-2 text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    З Google Calendar URL
                </button>
                <button type="button" @click="importMethod = 'file'"
                        :class="importMethod === 'file' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' : 'text-gray-600 dark:text-gray-400'"
                        class="flex-1 px-4 py-2 text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Завантажити файл
                </button>
            </div>
        </div>

        <!-- Google Calendar URL Import -->
        <form x-show="importMethod === 'url'" action="{{ route('calendar.import.url') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Info Box -->
            <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Як отримати URL Google Calendar?</h3>
                        <div class="mt-2 text-sm text-green-700 dark:text-green-400 space-y-1">
                            <p>1. Відкрийте Google Calendar на комп'ютері</p>
                            <p>2. Знайдіть потрібний календар у лівій панелі</p>
                            <p>3. Натисніть три крапки → "Налаштування"</p>
                            <p>4. Прокрутіть до "Інтеграція календаря"</p>
                            <p>5. Скопіюйте "Публічна адреса у форматі iCal"</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Google Calendar URL -->
            <div>
                <label for="calendar_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    URL Google Calendar (iCal)
                </label>
                <input type="url" name="calendar_url" id="calendar_url" required
                       placeholder="https://calendar.google.com/calendar/ical/..."
                       value="{{ old('calendar_url') }}"
                       class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                @error('calendar_url')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Ministry Selection -->
            <div>
                <label for="ministry_id_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Команда для імпортованих подій
                </label>
                <select name="ministry_id" id="ministry_id_url" required
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">Виберіть команду...</option>
                    @foreach($ministries as $ministry)
                        <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                    @endforeach
                </select>
                @error('ministry_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Імпортувати з (необов'язково)
                    </label>
                    <input type="date" name="start_date" id="start_date"
                           value="{{ old('start_date', now()->subMonth()->format('Y-m-d')) }}"
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Імпортувати до (необов'язково)
                    </label>
                    <input type="date" name="end_date" id="end_date"
                           value="{{ old('end_date', now()->addMonths(3)->format('Y-m-d')) }}"
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
            </div>

            <!-- Save Settings -->
            <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                <input type="checkbox" name="save_settings" id="save_settings" value="1" checked
                       class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-600 border-gray-300 dark:border-gray-500 rounded focus:ring-primary-500">
                <label for="save_settings" class="text-sm text-gray-700 dark:text-gray-300">
                    <span class="font-medium">Запам'ятати налаштування</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">Для швидкої синхронізації одним кліком на сторінці розкладу</span>
                </label>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Синхронізувати
                </button>
            </div>
        </form>

        <!-- File Import -->
        <form x-show="importMethod === 'file'" action="{{ route('calendar.import.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
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
                    Команда для імпортованих подій
                </label>
                <select name="ministry_id" id="ministry_id" required
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">Виберіть команду...</option>
                    @foreach($ministries as $ministry)
                        <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
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
