@extends('layouts.app')

@section('title', __('app.prayer_edit_title'))

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('prayer-requests.show', $prayerRequest) }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.prayer_back') }}
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.prayer_edit_title') }}</h2>
            <button type="button"
                    @click="ajaxDelete('{{ route('prayer-requests.destroy', $prayerRequest) }}', @js( __('messages.confirm_delete_prayer_request') ), null, '{{ route('prayer-requests.index') }}')"
                    class="text-red-600 hover:text-red-700 text-sm">{{ __('app.prayer_delete') }}</button>
        </div>

        <form @submit.prevent="submit($refs.editForm)" x-ref="editForm" class="p-6 space-y-6"
              x-data="{ ...ajaxForm({ url: '{{ route('prayer-requests.update', $prayerRequest) }}', method: 'PUT' }) }">

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('app.prayer_title_label') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title', $prayerRequest->title) }}" required maxlength="255"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <template x-if="errors.title">
                    <p class="mt-1 text-sm text-red-500" x-text="errors.title[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('app.prayer_description_label') }} <span class="text-red-500">*</span>
                </label>
                <textarea name="description" rows="5" required maxlength="2000"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('description', $prayerRequest->description) }}</textarea>
                <template x-if="errors.description">
                    <p class="mt-1 text-sm text-red-500" x-text="errors.description[0]"></p>
                </template>
            </div>

            <div class="space-y-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_urgent" value="1" {{ old('is_urgent', $prayerRequest->is_urgent) ? 'checked' : '' }}
                           class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.prayer_urgent') }}</span>
                </label>

                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_public" value="1" {{ old('is_public', $prayerRequest->is_public) ? 'checked' : '' }}
                           class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.prayer_public') }}</span>
                </label>

                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_anonymous" value="1" {{ old('is_anonymous', $prayerRequest->is_anonymous) ? 'checked' : '' }}
                           class="w-4 h-4 text-gray-600 border-gray-300 rounded focus:ring-gray-500">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.prayer_anonymous') }}</span>
                </label>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('prayer-requests.show', $prayerRequest) }}"
                   class="w-full sm:w-auto px-4 py-2 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    {{ __('app.prayer_cancel') }}
                </a>
                <button type="submit" :disabled="saving"
                        class="w-full sm:w-auto px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    {{ __('app.prayer_save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
