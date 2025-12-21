@extends('layouts.app')

@section('title', 'Нова група')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <form method="POST" action="{{ route('groups.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Назва групи *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       placeholder="Домашня група Центр"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Опис</label>
                <textarea name="description" id="description" rows="3"
                          placeholder="Коротко про групу..."
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="leader_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Лідер групи</label>
                <select name="leader_id" id="leader_id"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                    <option value="">Обрати лідера...</option>
                    @foreach($people as $person)
                    <option value="{{ $person->id }}" {{ old('leader_id') == $person->id ? 'selected' : '' }}>
                        {{ $person->full_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="meeting_day" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">День зустрічі</label>
                    <select name="meeting_day" id="meeting_day"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                        <option value="">Обрати день...</option>
                        <option value="monday" {{ old('meeting_day') == 'monday' ? 'selected' : '' }}>Понеділок</option>
                        <option value="tuesday" {{ old('meeting_day') == 'tuesday' ? 'selected' : '' }}>Вівторок</option>
                        <option value="wednesday" {{ old('meeting_day') == 'wednesday' ? 'selected' : '' }}>Середа</option>
                        <option value="thursday" {{ old('meeting_day') == 'thursday' ? 'selected' : '' }}>Четвер</option>
                        <option value="friday" {{ old('meeting_day') == 'friday' ? 'selected' : '' }}>П'ятниця</option>
                        <option value="saturday" {{ old('meeting_day') == 'saturday' ? 'selected' : '' }}>Субота</option>
                        <option value="sunday" {{ old('meeting_day') == 'sunday' ? 'selected' : '' }}>Неділя</option>
                    </select>
                </div>

                <div>
                    <label for="meeting_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Час зустрічі</label>
                    <input type="time" name="meeting_time" id="meeting_time" value="{{ old('meeting_time') }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>
            </div>

            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Місце зустрічі</label>
                <input type="text" name="location" id="location" value="{{ old('location') }}"
                       placeholder="вул. Хрещатик 1, кв. 5"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
            </div>

            <div>
                <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Колір групи</label>
                <div class="flex items-center space-x-3">
                    <input type="color" name="color" id="color" value="{{ old('color', '#3b82f6') }}"
                           class="w-12 h-12 rounded-xl border-0 cursor-pointer">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Виберіть колір для ідентифікації групи</span>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('groups.index') }}" class="px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                    Скасувати
                </a>
                <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors">
                    Створити групу
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
