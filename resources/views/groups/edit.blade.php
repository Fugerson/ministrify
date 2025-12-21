@extends('layouts.app')

@section('title', 'Редагувати групу')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <form method="POST" action="{{ route('groups.update', $group) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Назва групи *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $group->name) }}" required
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Опис</label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ old('description', $group->description) }}</textarea>
            </div>

            <div>
                <label for="leader_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Лідер групи</label>
                <select name="leader_id" id="leader_id"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                    <option value="">Обрати лідера...</option>
                    @foreach($people as $person)
                    <option value="{{ $person->id }}" {{ old('leader_id', $group->leader_id) == $person->id ? 'selected' : '' }}>
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
                        @foreach(['monday' => 'Понеділок', 'tuesday' => 'Вівторок', 'wednesday' => 'Середа', 'thursday' => 'Четвер', 'friday' => "П'ятниця", 'saturday' => 'Субота', 'sunday' => 'Неділя'] as $value => $label)
                        <option value="{{ $value }}" {{ old('meeting_day', $group->meeting_day) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="meeting_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Час зустрічі</label>
                    <input type="time" name="meeting_time" id="meeting_time" value="{{ old('meeting_time', $group->meeting_time?->format('H:i')) }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>
            </div>

            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Місце зустрічі</label>
                <input type="text" name="location" id="location" value="{{ old('location', $group->location) }}"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
            </div>

            <div>
                <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Колір групи</label>
                <input type="color" name="color" id="color" value="{{ old('color', $group->color) }}"
                       class="w-12 h-12 rounded-xl border-0 cursor-pointer">
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       {{ old('is_active', $group->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Активна група</label>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
                @can('delete', $group)
                <form method="POST" action="{{ route('groups.destroy', $group) }}" onsubmit="return confirm('Видалити групу?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">
                        Видалити групу
                    </button>
                </form>
                @endcan

                <div class="flex items-center space-x-3">
                    <a href="{{ route('groups.show', $group) }}" class="px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                        Скасувати
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors">
                        Зберегти
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
