@extends('layouts.app')

@section('title', 'Редагувати період недоступності')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header with back link -->
    <div class="flex items-center gap-4">
        <a href="{{ route('blockouts.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Редагувати період</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $blockout->date_range }}</p>
        </div>
    </div>

    <form action="{{ route('blockouts.update', $blockout) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Date Range Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Дати
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Початок <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="start_date" id="start_date"
                           value="{{ old('start_date', $blockout->start_date->format('Y-m-d')) }}"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                           required>
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Кінець <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="end_date" id="end_date"
                           value="{{ old('end_date', $blockout->end_date->format('Y-m-d')) }}"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                           required>
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- All Day Toggle -->
            <div class="mt-4">
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="all_day" id="all_day" value="1"
                           {{ old('all_day', $blockout->all_day) ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500"
                           onchange="toggleTimeFields()">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Весь день</span>
                </label>
            </div>

            <!-- Time Fields -->
            <div id="time-fields" class="mt-4 grid grid-cols-2 gap-4 {{ old('all_day', $blockout->all_day) ? 'hidden' : '' }}">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Час початку
                    </label>
                    <input type="time" name="start_time" id="start_time"
                           value="{{ old('start_time', $blockout->start_time?->format('H:i')) }}"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Час закінчення
                    </label>
                    <input type="time" name="end_time" id="end_time"
                           value="{{ old('end_time', $blockout->end_time?->format('H:i')) }}"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>
        </div>

        <!-- Reason Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
                Причина
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach(\App\Models\BlockoutDate::REASONS as $value => $label)
                <label class="relative flex items-center justify-center p-4 rounded-xl border-2 cursor-pointer transition-all
                              {{ old('reason', $blockout->reason) === $value ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                    <input type="radio" name="reason" value="{{ $value }}"
                           {{ old('reason', $blockout->reason) === $value ? 'checked' : '' }}
                           class="sr-only"
                           onchange="updateReasonSelection()">
                    <div class="text-center">
                        <span class="text-2xl block mb-1">
                            @switch($value)
                                @case('vacation') 🏖️ @break
                                @case('travel') ✈️ @break
                                @case('sick') 🏥 @break
                                @case('family') 👨‍👩‍👧 @break
                                @case('work') 💼 @break
                                @default 📅
                            @endswitch
                        </span>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                    </div>
                    <div class="absolute top-2 right-2 {{ old('reason', $blockout->reason) === $value ? '' : 'hidden' }} check-icon">
                        <svg class="w-5 h-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </label>
                @endforeach
            </div>
            @error('reason')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="mt-4">
                <label for="reason_note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Додаткова інформація (необов'язково)
                </label>
                <input type="text" name="reason_note" id="reason_note"
                       value="{{ old('reason_note', $blockout->reason_note) }}"
                       class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500">
            </div>
        </div>

        <!-- Ministry Scope Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Сфера застосування
            </h2>

            @php $appliesToAll = old('applies_to_all', $blockout->applies_to_all); @endphp

            <div class="space-y-3">
                <label class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all
                              {{ $appliesToAll ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-600' }}">
                    <input type="radio" name="applies_to_all" value="1"
                           {{ $appliesToAll ? 'checked' : '' }}
                           class="mt-0.5 text-primary-600 focus:ring-primary-500"
                           onchange="toggleMinistrySelection()">
                    <div>
                        <span class="font-medium text-gray-900 dark:text-white">Всі служіння</span>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Я недоступний для будь-якого служіння</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all
                              {{ !$appliesToAll ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-600' }}">
                    <input type="radio" name="applies_to_all" value="0"
                           {{ !$appliesToAll ? 'checked' : '' }}
                           class="mt-0.5 text-primary-600 focus:ring-primary-500"
                           onchange="toggleMinistrySelection()">
                    <div>
                        <span class="font-medium text-gray-900 dark:text-white">Окремі служіння</span>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Я недоступний тільки для вибраних служінь</p>
                    </div>
                </label>
            </div>

            <!-- Ministry Selection -->
            @php $selectedMinistries = old('ministry_ids', $blockout->ministries->pluck('id')->toArray()); @endphp
            <div id="ministry-selection" class="mt-4 {{ $appliesToAll ? 'hidden' : '' }}">
                @if($ministries->count() > 0)
                <div class="space-y-2">
                    @foreach($ministries as $ministry)
                    <label class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                        <input type="checkbox" name="ministry_ids[]" value="{{ $ministry->id }}"
                               {{ in_array($ministry->id, $selectedMinistries) ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                        <span class="text-gray-700 dark:text-gray-300">{{ $ministry->name }}</span>
                    </label>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-500 dark:text-gray-400 italic">Ви не належите до жодного служіння</p>
                @endif
            </div>
        </div>

        <!-- Recurrence Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Повторення
            </h2>

            @php $recurrence = old('recurrence', $blockout->recurrence); @endphp

            <div class="space-y-2">
                @foreach(\App\Models\BlockoutDate::RECURRENCE_OPTIONS as $value => $label)
                <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                    <input type="radio" name="recurrence" value="{{ $value }}"
                           {{ $recurrence === $value ? 'checked' : '' }}
                           class="text-primary-600 focus:ring-primary-500"
                           onchange="toggleRecurrenceEnd()">
                    <span class="text-gray-700 dark:text-gray-300">{{ $label }}</span>
                </label>
                @endforeach
            </div>

            <!-- Recurrence End Date -->
            <div id="recurrence-end" class="mt-4 {{ $recurrence === 'none' ? 'hidden' : '' }}">
                <label for="recurrence_end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Повторювати до
                </label>
                <input type="date" name="recurrence_end_date" id="recurrence_end_date"
                       value="{{ old('recurrence_end_date', $blockout->recurrence_end_date?->format('Y-m-d')) }}"
                       class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Залиште порожнім для безкінечного повторення</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <button type="button" onclick="confirmDelete()"
                    class="px-4 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors">
                Видалити
            </button>
            <div class="flex items-center gap-3">
                <a href="{{ route('blockouts.index') }}"
                   class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
                    Скасувати
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                    Зберегти
                </button>
            </div>
        </div>
    </form>

    <!-- Delete Form (hidden) -->
    <form id="delete-form" action="{{ route('blockouts.destroy', $blockout) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

@push('scripts')
<script>
function toggleTimeFields() {
    const allDay = document.getElementById('all_day').checked;
    const timeFields = document.getElementById('time-fields');
    timeFields.classList.toggle('hidden', allDay);
}

function toggleMinistrySelection() {
    const appliesToAll = document.querySelector('input[name="applies_to_all"]:checked').value === '1';
    const ministrySelection = document.getElementById('ministry-selection');
    ministrySelection.classList.toggle('hidden', appliesToAll);

    // Update radio button styling
    document.querySelectorAll('input[name="applies_to_all"]').forEach(input => {
        const label = input.closest('label');
        if (input.checked) {
            label.classList.add('border-primary-500', 'bg-primary-50', 'dark:bg-primary-900/20');
            label.classList.remove('border-gray-200', 'dark:border-gray-600');
        } else {
            label.classList.remove('border-primary-500', 'bg-primary-50', 'dark:bg-primary-900/20');
            label.classList.add('border-gray-200', 'dark:border-gray-600');
        }
    });
}

function toggleRecurrenceEnd() {
    const recurrence = document.querySelector('input[name="recurrence"]:checked').value;
    const recurrenceEnd = document.getElementById('recurrence-end');
    recurrenceEnd.classList.toggle('hidden', recurrence === 'none');
}

function updateReasonSelection() {
    document.querySelectorAll('input[name="reason"]').forEach(input => {
        const label = input.closest('label');
        const checkIcon = label.querySelector('.check-icon');
        if (input.checked) {
            label.classList.add('border-primary-500', 'bg-primary-50', 'dark:bg-primary-900/20');
            label.classList.remove('border-gray-200', 'dark:border-gray-600');
            checkIcon.classList.remove('hidden');
        } else {
            label.classList.remove('border-primary-500', 'bg-primary-50', 'dark:bg-primary-900/20');
            label.classList.add('border-gray-200', 'dark:border-gray-600');
            checkIcon.classList.add('hidden');
        }
    });
}

function confirmDelete() {
    if (confirm('Ви впевнені, що хочете видалити цей період недоступності?')) {
        document.getElementById('delete-form').submit();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Sync end date with start date
    document.getElementById('start_date').addEventListener('change', function() {
        const endDate = document.getElementById('end_date');
        if (endDate.value < this.value) {
            endDate.value = this.value;
        }
    });
});
</script>
@endpush
@endsection
