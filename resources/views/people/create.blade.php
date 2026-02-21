@extends('layouts.app')

@section('title', 'Додати людину')

@section('content')
<div class="max-w-3xl mx-auto" x-data="personCreateForm()">
    <form @submit.prevent="submitForm" enctype="multipart/form-data" x-ref="form" class="space-y-6">

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 md:p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Основна інформація</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ім'я *</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                           class="w-full px-3 py-2.5 md:py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                           :class="errors.first_name ? 'border-red-500' : ''">
                    <template x-if="errors.first_name"><p class="mt-1 text-xs text-red-500" x-text="errors.first_name[0]"></p></template>
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Прізвище *</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                           class="w-full px-3 py-2.5 md:py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                           :class="errors.last_name ? 'border-red-500' : ''">
                    <template x-if="errors.last_name"><p class="mt-1 text-xs text-red-500" x-text="errors.last_name[0]"></p></template>
                </div>

                <div class="md:col-span-2">
                    <label for="photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Фото</label>
                    <div x-data="{ fileName: '' }" class="relative">
                        <input type="file" name="photo" id="photo" accept="image/*,.heic,.heif" class="sr-only" x-ref="photoInput" @change="fileName = $event.target.files[0]?.name || ''">
                        <label @click="$refs.photoInput.click()" class="flex items-center gap-3 px-4 py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:border-primary-400 dark:hover:border-primary-500 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-all group">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-900/30 transition-colors">
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p x-show="!fileName" class="text-sm font-medium text-gray-700 dark:text-gray-300">Обрати фото</p>
                                <p x-show="fileName" x-text="fileName" class="text-sm font-medium text-primary-600 dark:text-primary-400 truncate"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, WebP</p>
                            </div>
                        </label>
                    </div>
                    <template x-if="errors.photo"><p class="mt-1 text-xs text-red-500" x-text="errors.photo[0]"></p></template>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 md:p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Контакти</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Телефон</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                           class="w-full px-3 py-2.5 md:py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                           >
                    <template x-if="errors.phone"><p class="mt-1 text-xs text-red-500" x-text="errors.phone[0]"></p></template>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           class="w-full px-3 py-2.5 md:py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <template x-if="errors.email"><p class="mt-1 text-xs text-red-500" x-text="errors.email[0]"></p></template>
                </div>

                <div>
                    <label for="telegram_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telegram</label>
                    <input type="text" name="telegram_username" id="telegram_username" value="{{ old('telegram_username') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                           >
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Адреса</label>
                    <input type="text" name="address" id="address" value="{{ old('address') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div>
                    <label for="birth_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата народження</label>
                    <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Стать</label>
                    <select name="gender" id="gender"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">-- Не вказано --</option>
                        @foreach(\App\Models\Person::getGenders() as $value => $label)
                        <option value="{{ $value }}" {{ old('gender') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="marital_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Сімейний стан</label>
                    <select name="marital_status" id="marital_status"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">-- Не вказано --</option>
                        @foreach(\App\Models\Person::getMaritalStatuses() as $value => $label)
                        <option value="{{ $value }}" {{ old('marital_status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="anniversary" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Річниця весілля</label>
                    <input type="date" name="anniversary" id="anniversary" value="{{ old('anniversary') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div>
                    <label for="joined_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата приходу в церкву</label>
                    <input type="date" name="joined_date" id="joined_date" value="{{ old('joined_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div>
                    <label for="church_role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Церковна роль</label>
                    <select name="church_role_id" id="church_role_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">-- Не вказано --</option>
                        @foreach($churchRoles as $role)
                        <option value="{{ $role->id }}" {{ old('church_role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Теги</h2>

            <div class="flex flex-wrap gap-2">
                @foreach($tags as $tag)
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                               {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                        <span class="ml-2 text-sm" style="color: {{ $tag->color }}">{{ $tag->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Команди</h2>

            <div class="space-y-4">
                @foreach($ministries as $ministry)
                    <div x-data="{ open: false }" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="ministries[{{ $ministry->id }}][selected]" value="1"
                                   @click="open = $event.target.checked"
                                   class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ $ministry->name }}</span>
                        </label>

                        <div x-show="open" x-cloak class="mt-3 ml-6 space-y-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Позиції:</p>
                            @foreach($ministry->positions as $position)
                                <label class="flex items-center">
                                    <input type="checkbox" name="ministries[{{ $ministry->id }}][positions][]" value="{{ $position->id }}"
                                           class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $position->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Нотатки</h2>

            <textarea name="notes" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                      placeholder="Додаткова інформація...">{{ old('notes') }}</textarea>
        </div>

        <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 sm:space-x-4">
            <a href="{{ route('people.index') }}" class="w-full sm:w-auto text-center px-4 py-2.5 md:py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                Скасувати
            </a>
            <button type="submit" :disabled="saving"
                    class="w-full sm:w-auto px-6 py-2.5 md:py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                <span x-show="!saving">Зберегти</span>
                <span x-show="saving" class="inline-flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Збереження...
                </span>
            </button>
        </div>
    </form>

    <!-- Duplicate person warning modal -->
    <template x-if="duplicateWarning">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="duplicateWarning = null">
            <div class="fixed inset-0 bg-black/50" @click="duplicateWarning = null"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.duplicate_found') }}</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('app.duplicate_person_exists') }}</p>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 flex items-center gap-3">
                    <template x-if="duplicateWarning.photo_url">
                        <img :src="duplicateWarning.photo_url" class="w-10 h-10 rounded-full object-cover" alt="">
                    </template>
                    <template x-if="!duplicateWarning.photo_url">
                        <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-semibold text-sm" x-text="duplicateWarning.name?.charAt(0)"></div>
                    </template>
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white truncate" x-text="duplicateWarning.name"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="duplicateWarning.info"></p>
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row gap-2 sm:justify-end">
                    <button @click="duplicateWarning = null" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        {{ __('app.cancel') }}
                    </button>
                    <a :href="duplicateWarning.url" class="px-4 py-2 text-sm text-center bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">
                        {{ __('app.go_to_profile') }}
                    </a>
                    <button @click="submitForm(true)" class="px-4 py-2 text-sm text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 border border-amber-300 dark:border-amber-700 rounded-lg transition-colors">
                        {{ __('app.create_anyway') }}
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function personCreateForm() {
    return {
        saving: false,
        errors: {},
        duplicateWarning: null,
        async submitForm(forceDuplicate = false) {
            this.saving = true;
            this.errors = {};
            this.duplicateWarning = null;
            const formData = new FormData(this.$refs.form);
            if (forceDuplicate) {
                formData.append('force_duplicate', '1');
            }
            try {
                const response = await fetch('{{ route("people.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    if (response.status === 409 && data.duplicate) {
                        this.duplicateWarning = data.existing_person;
                        this.saving = false;
                        return;
                    }
                    if (response.status === 422 && data.errors) {
                        this.errors = data.errors;
                        showToast('error', '{{ __("app.check_form_errors") }}');
                    } else {
                        showToast('error', data.message || '{{ __("app.save_error") }}');
                    }
                    this.saving = false;
                    return;
                }
                showToast('success', data.message || '{{ __("app.saved") }}');
                setTimeout(() => window.location.href = data.redirect_url, 800);
            } catch (e) {
                showToast('error', "{{ __('app.connection_error') }}");
                this.saving = false;
            }
        }
    }
}
</script>
@endsection
