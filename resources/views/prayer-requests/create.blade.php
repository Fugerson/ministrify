@extends('layouts.app')

@section('title', 'Нове молитовне прохання')

@section('content')
<div class="max-w-2xl mx-auto" x-data="prayerRequestCreateForm()">
    <a href="{{ route('prayer-requests.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <span class="text-2xl mr-2">🙏</span>
                Нове молитовне прохання
            </h2>
        </div>

        <form @submit.prevent="submitForm" class="p-6 space-y-6" x-ref="form">

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Заголовок <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="255"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Коротко опишіть прохання">
                <template x-if="errors.title">
                    <p class="mt-1 text-sm text-red-500" x-text="errors.title[0]"></p>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Опис <span class="text-red-500">*</span>
                </label>
                <textarea name="description" rows="5" required maxlength="2000"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="Детально опишіть ваше молитовне прохання...">{{ old('description') }}</textarea>
                <template x-if="errors.description">
                    <p class="mt-1 text-sm text-red-500" x-text="errors.description[0]"></p>
                </template>
            </div>

            <div class="space-y-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_urgent" value="1" {{ old('is_urgent') ? 'checked' : '' }}
                           class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">🔥 Терміново</span>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Позначити як термінове прохання</p>
                    </div>
                </label>

                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_public" value="1" {{ old('is_public', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">👥 Публічне</span>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Інші члени церкви зможуть бачити та молитися</p>
                    </div>
                </label>

                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }}
                           class="w-4 h-4 text-gray-600 border-gray-300 rounded focus:ring-gray-500">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">🎭 Анонімно</span>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Ваше ім'я не буде відображатися</p>
                    </div>
                </label>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('prayer-requests.index') }}"
                   class="w-full sm:w-auto px-4 py-2 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    Скасувати
                </a>
                <button type="submit" :disabled="saving"
                        class="w-full sm:w-auto px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                    <span x-show="!saving">Надіслати прохання</span>
                    <span x-show="saving" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Збереження...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function prayerRequestCreateForm() {
    return {
        saving: false,
        errors: {},
        async submitForm() {
            this.saving = true;
            this.errors = {};
            const formData = new FormData(this.$refs.form);
            try {
                const response = await fetch('{{ route("prayer-requests.store") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    if (response.status === 422 && data.errors) { this.errors = data.errors; showToast('error', 'Перевірте правильність заповнення форми.'); }
                    else { showToast('error', data.message || 'Помилка збереження.'); }
                    this.saving = false; return;
                }
                showToast('success', data.message || 'Збережено!');
                setTimeout(() => Livewire.navigate(data.redirect_url), 800);
            } catch (e) { showToast('error', "Помилка з'єднання з сервером."); this.saving = false; }
        }
    }
}
</script>
@endsection
