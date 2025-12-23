@extends('layouts.app')

@section('title', 'Редагувати запис: ' . $attendance->date->format('d.m.Y'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Редагувати запис</h2>

        <form method="POST" action="{{ route('groups.attendance.update', [$group, $attendance]) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Дата *</label>
                    <input type="date" name="date" id="date" value="{{ old('date', $attendance->date->format('Y-m-d')) }}" required
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>
                <div>
                    <label for="time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Час</label>
                    <input type="time" name="time" id="time" value="{{ old('time', $attendance->time?->format('H:i')) }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>
            </div>

            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Місце</label>
                <input type="text" name="location" id="location" value="{{ old('location', $attendance->location) }}"
                       placeholder="Адреса або назва місця"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
            </div>

            <div>
                <label for="guests_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Кількість гостей</label>
                <input type="number" name="guests_count" id="guests_count" value="{{ old('guests_count', $attendance->guests_count) }}" min="0"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
            </div>

            <!-- Members Checklist -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Присутні учасники</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($group->members->sortBy('first_name') as $member)
                    <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-xl cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <input type="checkbox" name="present[]" value="{{ $member->id }}"
                               {{ in_array($member->id, $presentIds) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="ml-3 text-gray-900 dark:text-white">{{ $member->full_name }}</span>
                        @if($member->pivot->role !== 'member')
                        <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                            {{ $member->pivot->role === 'leader' ? 'Лідер' : 'Помічник' }}
                        </span>
                        @endif
                    </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Нотатки</label>
                <textarea name="notes" id="notes" rows="3"
                          placeholder="Що обговорювали, молитовні потреби..."
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ old('notes', $attendance->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
                <div></div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('groups.attendance.show', [$group, $attendance]) }}" class="px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:text-gray-900 font-medium">
                        Скасувати
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors">
                        Зберегти
                    </button>
                </div>
            </div>
        </form>

        <!-- Delete form outside main form -->
        <div class="mt-4 text-center">
            <form method="POST" action="{{ route('groups.attendance.destroy', [$group, $attendance]) }}" onsubmit="return confirm('Видалити запис?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium hover:underline">
                    Видалити запис
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
