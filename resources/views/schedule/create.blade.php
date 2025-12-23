@extends('layouts.app')

@section('title', 'Створити подію')

@section('content')
<div class="max-w-2xl mx-auto">
    <form method="POST" action="{{ route('events.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Нова подія</h2>

            <div class="space-y-4">
                <div>
                    <label for="ministry_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Служіння *</label>
                    <select name="ministry_id" id="ministry_id" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">Виберіть служіння</option>
                        @foreach($ministries as $ministry)
                            <option value="{{ $ministry->id }}" {{ $selectedMinistry == $ministry->id ? 'selected' : '' }}>
                                {{ $ministry->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                           placeholder="Недільне служіння">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата *</label>
                        <input type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label for="time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Час *</label>
                        <input type="time" name="time" id="time" value="{{ old('time', '10:00') }}" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label for="recurrence_rule" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Повторення</label>
                    <select name="recurrence_rule" id="recurrence_rule"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">Не повторювати</option>
                        <option value="weekly">Щотижня (наступні 4 тижні)</option>
                    </select>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нотатки</label>
                    <textarea name="notes" id="notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                              placeholder="Додаткова інформація...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('schedule') }}" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                Скасувати
            </a>
            <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Створити
            </button>
        </div>
    </form>
</div>
@endsection
