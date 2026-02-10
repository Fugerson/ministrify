@extends('layouts.app')

@section('title', 'Записати зустріч: ' . $group->name)

@section('content')
<div class="max-w-2xl mx-auto">
    @if($existingToday)
    <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="text-amber-800 dark:text-amber-200 font-medium">Сьогодні вже є запис</p>
                <p class="text-amber-700 dark:text-amber-300 text-sm mt-1">
                    <a href="{{ route('groups.attendance.edit', [$group, $existingToday]) }}" class="underline hover:no-underline">Редагувати запис за сьогодні</a>
                </p>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Записати зустріч</h2>

        <form method="POST" action="{{ route('groups.attendance.store', $group) }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Дата *</label>
                    <input type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>
                <div>
                    <label for="time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Час</label>
                    <input type="time" name="time" id="time" value="{{ old('time', $group->meeting_time?->format('H:i')) }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>
            </div>

            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Місце</label>
                <input type="text" name="location" id="location" value="{{ old('location', $group->location) }}"
                       placeholder="Адреса або назва місця"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
            </div>

            <div>
                <label for="guests_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Кількість гостей</label>
                <input type="number" name="guests_count" id="guests_count" value="{{ old('guests_count', 0) }}" min="0"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
            </div>

            <!-- Members Checklist -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Присутні учасники</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($group->members->sortBy('first_name') as $member)
                    <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-xl cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <input type="checkbox" name="present[]" value="{{ $member->id }}"
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
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('groups.show', $group) }}" class="px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:text-gray-900 font-medium">
                    Скасувати
                </a>
                <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors">
                    Зберегти
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
