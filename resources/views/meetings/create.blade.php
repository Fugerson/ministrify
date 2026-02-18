@extends('layouts.app')

@section('title', 'Нова зустріч - ' . $ministry->name)

@section('content')
<div class="max-w-3xl mx-auto" x-data="meetingCreateForm()">
    <div class="mb-6">
        <a href="{{ route('meetings.index', $ministry) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Зустрічі {{ $ministry->name }}
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6" x-data="{ copyFrom: null }">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Нова зустріч</h2>

        <form @submit.prevent="submitForm" class="space-y-6" x-ref="form">

            <!-- Copy from previous -->
            @if($previousMeetings->isNotEmpty())
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-4">
                <label class="flex items-center gap-2 text-sm font-medium text-blue-900 dark:text-blue-300 mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Скопіювати з попередньої зустрічі
                </label>
                <select name="copy_from_id" x-model="copyFrom"
                        class="w-full px-4 py-3 bg-white dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                    <option value="">Не копіювати (створити з нуля)</option>
                    @foreach($previousMeetings as $prev)
                    <option value="{{ $prev->id }}">
                        {{ $prev->date->format('d.m.Y') }} - {{ $prev->title }}
                        @if($prev->theme) ({{ $prev->theme }}) @endif
                    </option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-blue-700 dark:text-blue-400">
                    При копіюванні буде скопійовано план (пункти) та матеріали
                </p>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Назва зустрічі *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                           placeholder="Репетиція прославлення, Молодіжна зустріч..."
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                    <template x-if="errors.title">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.title[0]"></p>
                    </template>
                </div>

                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Дата *</label>
                    <input type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div>
                    <label for="theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Тема зустрічі</label>
                    <input type="text" name="theme" id="theme" value="{{ old('theme') }}"
                           placeholder="Підготовка до Різдва, Навчання..."
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Час початку</label>
                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Час закінчення</label>
                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div class="md:col-span-2">
                    <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Місце</label>
                    <input type="text" name="location" id="location" value="{{ old('location') }}"
                           placeholder="Церква, онлайн, дім лідера..."
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Опис</label>
                    <textarea name="description" id="description" rows="3"
                              placeholder="Додаткова інформація про зустріч..."
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 sm:gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('meetings.index', $ministry) }}" class="w-full sm:w-auto px-5 py-2.5 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                    Скасувати
                </a>
                <button type="submit" :disabled="saving"
                        class="w-full sm:w-auto px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors disabled:opacity-50">
                    <span x-show="!saving">Створити зустріч</span>
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
function meetingCreateForm() {
    return {
        saving: false,
        errors: {},
        async submitForm() {
            this.saving = true;
            this.errors = {};
            const formData = new FormData(this.$refs.form);
            try {
                const response = await fetch('{{ route("meetings.store", $ministry) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData,
                });
                const data = await response.json();
                if (!response.ok) {
                    if (response.status === 422 && data.errors) { this.errors = data.errors; showToast('error', 'Перевірте правильність заповнення форми.'); }
                    else { showToast('error', data.message || 'Помилка збереження.'); }
                    this.saving = false; return;
                }
                showToast('success', data.message || 'Збережено!');
                setTimeout(() => window.location.href = data.redirect_url, 800);
            } catch (e) { showToast('error', "Помилка з'єднання з сервером."); this.saving = false; }
        }
    }
}
</script>
@endsection
