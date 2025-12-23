@extends('layouts.app')

@section('title', 'Копіювати зустріч')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('meetings.show', [$ministry, $meeting]) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ $meeting->title }}
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Копіювати зустріч</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Створити нову зустріч на основі "{{ $meeting->title }}"</p>
        </div>

        <!-- What will be copied -->
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 mb-6">
            <h3 class="font-medium text-gray-900 dark:text-white mb-3">Буде скопійовано:</h3>
            <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    План зустрічі ({{ $meeting->agendaItems->count() }} пунктів)
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Матеріали ({{ $meeting->materials->count() }} шт.)
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Опис, місце та час
                </li>
            </ul>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Нотатки та підсумок не копіюються. Статус буде "Заплановано".
            </p>
        </div>

        <form method="POST" action="{{ route('meetings.copy.store', [$ministry, $meeting]) }}" class="space-y-6">
            @csrf

            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Дата нової зустрічі *</label>
                <input type="date" name="date" id="date" value="{{ old('date', now()->addWeek()->format('Y-m-d')) }}" required
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
            </div>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Назва (залиште пустим щоб зберегти оригінальну)</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}"
                       placeholder="{{ $meeting->title }}"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('meetings.show', [$ministry, $meeting]) }}" class="px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                    Скасувати
                </a>
                <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors">
                    Створити копію
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
