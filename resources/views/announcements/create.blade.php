@extends('layouts.app')

@section('title', __('app.ann_new_title'))

@section('content')
<div class="max-w-2xl mx-auto" x-data="announcementCreateForm()">
    <div class="mb-6">
        <a href="{{ route('announcements.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ __('app.ann_back_to_list') }}
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-6">{{ __('app.ann_new_title') }}</h1>

        <form @submit.prevent="submitForm" class="space-y-6" x-ref="form">

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.ann_heading_label') }}</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                       placeholder="{{ __('app.ann_heading_placeholder') }}">
                <template x-if="errors.title">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.title[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.ann_content_label') }}</label>
                <textarea name="content" rows="8" required
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
                          placeholder="{{ __('app.ann_content_placeholder') }}">{{ old('content') }}</textarea>
                <template x-if="errors.content">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.content[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.ann_expires_label') }}</label>
                <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('app.ann_expires_hint') }}</p>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_pinned" value="1" id="is_pinned" {{ old('is_pinned') ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:bg-gray-700">
                <label for="is_pinned" class="ml-3 text-gray-700 dark:text-gray-300">
                    {{ __('app.ann_pin_top') }}
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('announcements.index') }}"
                   class="px-6 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-xl">
                    {{ __('app.msg_cancel') }}
                </a>
                <button type="submit" :disabled="saving"
                        class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl disabled:opacity-50">
                    <span x-show="!saving">{{ __('app.ann_publish') }}</span>
                    <span x-show="saving" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        {{ __('app.ann_saving') }}
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
var _annI18n = @json([
    'check_form' => __('app.ann_check_form'),
    'save_error' => __('app.ann_save_error'),
    'saved' => __('app.ann_saved'),
    'connection_error' => __('app.ann_connection_error'),
]);

function announcementCreateForm() {
    return {
        saving: false,
        errors: {},
        async submitForm() {
            this.saving = true;
            this.errors = {};
            const formData = new FormData(this.$refs.form);
            try {
                const response = await fetch('{{ route("announcements.store") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    if (response.status === 422 && data.errors) { this.errors = data.errors; showToast('error', _annI18n.check_form); }
                    else { showToast('error', data.message || _annI18n.save_error); }
                    this.saving = false; return;
                }
                showToast('success', data.message || _annI18n.saved);
                setTimeout(() => Livewire.navigate(data.redirect_url), 800);
            } catch (e) { showToast('error', _annI18n.connection_error); this.saving = false; }
        }
    }
}
</script>
@endsection
