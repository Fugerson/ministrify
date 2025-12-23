@extends('layouts.app')

@section('title', 'Редагувати подію')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('events.show', $event) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Назад до події
        </a>
    </div>

    <form method="POST" action="{{ route('events.update', $event) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Редагувати подію</h2>

            <div class="space-y-4">
                <div>
                    <label for="ministry_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Служіння *</label>
                    <select name="ministry_id" id="ministry_id" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @foreach($ministries as $ministry)
                            <option value="{{ $ministry->id }}" {{ $event->ministry_id == $ministry->id ? 'selected' : '' }}>
                                {{ $ministry->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва *</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $event->title) }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата *</label>
                        <input type="date" name="date" id="date" value="{{ old('date', $event->date->format('Y-m-d')) }}" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label for="time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Час *</label>
                        <input type="time" name="time" id="time" value="{{ old('time', $event->time->format('H:i')) }}" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нотатки</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ old('notes', $event->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Видалити подію?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                    Видалити подію
                </button>
            </form>

            <div class="flex items-center space-x-4">
                <a href="{{ route('events.show', $event) }}" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
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
