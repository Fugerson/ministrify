@extends('layouts.app')

@section('title', __('app.meeting_edit_title'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('meetings.show', [$ministry, $meeting]) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ $meeting->title }}
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">{{ __('app.meeting_edit_title') }}</h2>

        <form @submit.prevent="submit($refs.f)" x-ref="f"
              x-data="{ ...ajaxForm({ url: '{{ route('meetings.update', [$ministry, $meeting]) }}', method: 'PUT' }) }"
              class="space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.meeting_name_label') }} *</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $meeting->title) }}" required
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                    <template x-if="errors.title"><p class="mt-1 text-sm text-red-500" x-text="errors.title[0]"></p></template>
                </div>

                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.meeting_date_label') }} *</label>
                    <input type="date" name="date" id="date" value="{{ old('date', $meeting->date->format('Y-m-d')) }}" required
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.meeting_status_label') }} *</label>
                    <select name="status" id="status" required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                        <option value="planned" {{ $meeting->status === 'planned' ? 'selected' : '' }}>{{ __('app.meeting_status_planned') }}</option>
                        <option value="in_progress" {{ $meeting->status === 'in_progress' ? 'selected' : '' }}>{{ __('app.meeting_status_in_progress') }}</option>
                        <option value="completed" {{ $meeting->status === 'completed' ? 'selected' : '' }}>{{ __('app.meeting_status_completed') }}</option>
                        <option value="cancelled" {{ $meeting->status === 'cancelled' ? 'selected' : '' }}>{{ __('app.meeting_status_cancelled') }}</option>
                    </select>
                </div>

                <div>
                    <label for="theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.meeting_theme_label') }}</label>
                    <input type="text" name="theme" id="theme" value="{{ old('theme', $meeting->theme) }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.meeting_start_time') }}</label>
                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time', $meeting->start_time?->format('H:i')) }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.meeting_end_time') }}</label>
                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time', $meeting->end_time?->format('H:i')) }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div class="md:col-span-2">
                    <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.meeting_location') }}</label>
                    <input type="text" name="location" id="location" value="{{ old('location', $meeting->location) }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.meeting_description') }}</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ old('description', $meeting->description) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.meeting_notes_label') }}</label>
                    <textarea name="notes" id="notes" rows="4"
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ old('notes', $meeting->notes) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label for="summary" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.meeting_summary_label') }}</label>
                    <textarea name="summary" id="summary" rows="4"
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ old('summary', $meeting->summary) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button"
                        @click="ajaxDelete('{{ route('meetings.destroy', [$ministry, $meeting]) }}', '{{ __('messages.confirm_delete_meeting') }}', null, '{{ route('meetings.index', $ministry) }}')"
                        class="text-red-600 hover:text-red-700 text-sm font-medium">
                    {{ __('app.meeting_delete') }}
                </button>

                <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-2 sm:gap-3">
                    <a href="{{ route('meetings.show', [$ministry, $meeting]) }}" class="w-full sm:w-auto px-5 py-2.5 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                        {{ __('app.meeting_cancel') }}
                    </a>
                    <button type="submit" :disabled="saving" class="w-full sm:w-auto px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors disabled:opacity-50">
                        <span x-show="!saving">{{ __('app.meeting_save') }}</span>
                        <span x-show="saving" x-cloak>{{ __('app.meeting_saving') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
