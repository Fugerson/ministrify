@extends('layouts.app')

@section('title', 'Check-in')

@section('content')
<div class="max-w-3xl mx-auto">
    <form @submit.prevent="submit($refs.attendForm)" x-ref="attendForm" x-data="{ ...ajaxForm({ url: '{{ route('attendance.store') }}', method: 'POST' }) }" class="space-y-6">

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                @if($event)
                    Check-in: {{ $event->title }}
                @else
                    {{ __('app.attendance_new_checkin') }}
                @endif
            </h2>

            @if($event)
                <input type="hidden" name="event_id" value="{{ $event->id }}">
                <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="font-medium text-gray-900 dark:text-white">{{ $event->ministry?->name ?? $event->title }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $event->date->format('d.m.Y') }}@if($event->time) {{ __('app.group_at_time') }} {{ $event->time->format('H:i') }}@endif</p>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.date') }} *</label>
                    <input type="date" name="date" id="date"
                           value="{{ old('date', $date->format('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label for="total_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.attendance_total_count') }} *</label>
                    <div class="flex gap-2">
                        <input type="number" name="total_count" id="total_count"
                               value="{{ old('total_count', 0) }}" required min="0"
                               class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <button type="button" id="fill-from-selected"
                                class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors whitespace-nowrap"
                                title="{{ __('app.attendance_fill_from_selected') }}">
                            ← {{ __('app.attendance_from_selected') }}
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.notes') }}</label>
                <textarea name="notes" id="notes" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.attendance_mark_presence') }}</h3>

            <div class="mb-4">
                <input type="text" id="search" placeholder="{{ __('app.search') }}..."
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div class="space-y-2 max-h-96 overflow-y-auto" id="people-list">
                @foreach($people as $person)
                    <label class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg person-item">
                        <input type="checkbox" name="present[]" value="{{ $person->id }}"
                               class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                        <span class="ml-3 text-gray-900 dark:text-white">{{ $person->full_name }}</span>
                    </label>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                <span id="selected-count">{{ __('app.attendance_n_selected', ['count' => 0]) }}</span>
                <button type="button" onclick="selectAll()" class="text-primary-600 hover:text-primary-500">
                    {{ __('app.attendance_select_all') }}
                </button>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 sm:gap-3">
            <a href="{{ route('attendance.index') }}" class="w-full sm:w-auto px-4 py-2 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                {{ __('app.cancel') }}
            </a>
            <button type="submit" :disabled="saving" class="w-full sm:w-auto px-6 py-2 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white font-medium rounded-lg transition-colors">
                <span x-text="saving ? @js(__('app.saving')) : @js(__('app.save'))"></span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
onPageReady(function() {
document.getElementById('search').addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('.person-item').forEach(function(item) {
        const name = item.textContent.toLowerCase();
        item.style.display = name.includes(search) ? '' : 'none';
    });
});

document.querySelectorAll('input[name="present[]"]').forEach(function(checkbox) {
    checkbox.addEventListener('change', updateCount);
});

function updateCount() {
    const count = document.querySelectorAll('input[name="present[]"]:checked').length;
    document.getElementById('selected-count').textContent = count + ' ' + @js(__("app.attendance_selected") );
}

window.selectAll = function() {
    document.querySelectorAll('.person-item:not([style*="display: none"]) input[name="present[]"]').forEach(function(checkbox) {
        checkbox.checked = true;
    });
    updateCount();
}

document.getElementById('fill-from-selected').addEventListener('click', function() {
    const count = document.querySelectorAll('input[name="present[]"]:checked').length;
    document.getElementById('total_count').value = count;
});
});
</script>
@endpush
@endsection
