@extends('layouts.app')

@section('title', 'Редагувати: ' . $person->full_name)

@section('content')
<div class="max-w-3xl mx-auto">
    <form method="POST" action="{{ route('people.update', $person) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Photo Upload Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Фото профілю</h2>

            <div x-data="avatarUpload()" class="flex items-center gap-6">
                <!-- Preview -->
                <div class="relative">
                    <div class="w-24 h-24 rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <template x-if="preview">
                            <img :src="preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!preview && existingPhoto">
                            <img src="{{ $person->photo ? Storage::url($person->photo) : '' }}" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!preview && !existingPhoto">
                            <span class="text-3xl font-bold text-gray-400 dark:text-gray-500">{{ mb_substr($person->first_name, 0, 1) }}</span>
                        </template>
                    </div>
                    <button type="button" x-show="preview || existingPhoto" @click="removePhoto()"
                            class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Upload Area -->
                <div class="flex-1">
                    <label class="relative cursor-pointer block p-6 border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-xl hover:border-primary-400 dark:hover:border-primary-500 transition-colors"
                           @dragover.prevent="isDragging = true"
                           @dragleave.prevent="isDragging = false"
                           @drop.prevent="handleDrop($event)"
                           :class="isDragging ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : ''">
                        <input type="file" name="photo" accept="image/*" class="sr-only" @change="handleFileSelect($event)">
                        <div class="text-center">
                            <svg class="mx-auto h-10 w-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                <span class="text-primary-600 dark:text-primary-400 font-medium">Натисніть</span> або перетягніть фото
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG до 2MB</p>
                        </div>
                    </label>
                </div>
                <input type="hidden" name="remove_photo" x-ref="removePhotoInput" value="0">
            </div>
        </div>

        <!-- Basic Info -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Основна інформація</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ім'я *</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $person->first_name) }}" required
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Прізвище *</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $person->last_name) }}" required
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>
            </div>
        </div>

        <!-- Contacts -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Контакти</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Телефон</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $person->phone) }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $person->email) }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div>
                    <label for="telegram_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Telegram</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">@</span>
                        <input type="text" name="telegram_username" id="telegram_username" value="{{ old('telegram_username', $person->telegram_username) }}"
                               class="w-full pl-8 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Адреса</label>
                    <input type="text" name="address" id="address" value="{{ old('address', $person->address) }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div>
                    <label for="birth_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Дата народження</label>
                    <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date', $person->birth_date?->format('Y-m-d')) }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Стать</label>
                    <select name="gender" id="gender"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white">
                        <option value="" class="bg-white dark:bg-gray-800">-- Не вказано --</option>
                        @foreach(\App\Models\Person::GENDERS as $value => $label)
                        <option value="{{ $value }}" {{ old('gender', $person->gender) == $value ? 'selected' : '' }} class="bg-white dark:bg-gray-800">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="marital_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Сімейний стан</label>
                    <select name="marital_status" id="marital_status"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white">
                        <option value="" class="bg-white dark:bg-gray-800">-- Не вказано --</option>
                        @foreach(\App\Models\Person::MARITAL_STATUSES as $value => $label)
                        <option value="{{ $value }}" {{ old('marital_status', $person->marital_status) == $value ? 'selected' : '' }} class="bg-white dark:bg-gray-800">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="anniversary" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Річниця весілля</label>
                    <input type="date" name="anniversary" id="anniversary" value="{{ old('anniversary', $person->anniversary?->format('Y-m-d')) }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div>
                    <label for="joined_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Дата приходу в церкву</label>
                    <input type="date" name="joined_date" id="joined_date" value="{{ old('joined_date', $person->joined_date?->format('Y-m-d')) }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div>
                    <label for="church_role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Церковна роль</label>
                    <select name="church_role_id" id="church_role_id"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white">
                        <option value="" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">-- Не вказано --</option>
                        @foreach($churchRoles as $role)
                        <option value="{{ $role->id }}" {{ old('church_role_id', $person->church_role_id) == $role->id ? 'selected' : '' }} class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Tags -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Теги</h2>

            <div class="flex flex-wrap gap-2">
                @foreach($tags as $tag)
                    <label class="relative cursor-pointer">
                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                               {{ in_array($tag->id, old('tags', $person->tags->pluck('id')->toArray())) ? 'checked' : '' }}
                               class="peer sr-only">
                        <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium border-2 transition-colors peer-checked:border-current"
                              style="background-color: {{ $tag->color }}10; color: {{ $tag->color }}; border-color: transparent;"
                              :class="{ 'border-current': $el.previousElementSibling.checked }">
                            {{ $tag->name }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Ministries -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Служіння</h2>

            @php
                $personMinistries = $person->ministries->keyBy('id');
            @endphp

            <div class="space-y-4">
                @foreach($ministries as $ministry)
                    @php
                        $isInMinistry = $personMinistries->has($ministry->id);
                        $personPositionIds = [];
                        if ($isInMinistry) {
                            $pivot = $personMinistries->get($ministry->id)->pivot;
                            $personPositionIds = is_array($pivot->position_ids)
                                ? $pivot->position_ids
                                : json_decode($pivot->position_ids ?? '[]', true);
                        }
                    @endphp
                    <div x-data="{ open: {{ $isInMinistry ? 'true' : 'false' }} }"
                         class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 transition-colors"
                         :class="open ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800' : ''">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="ministries[{{ $ministry->id }}][selected]" value="1"
                                   @click="open = $event.target.checked"
                                   {{ $isInMinistry ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="ml-3 font-medium text-gray-900 dark:text-white">{{ $ministry->name }}</span>
                        </label>

                        <div x-show="open" x-cloak x-transition class="mt-4 ml-8 grid grid-cols-2 gap-2">
                            @foreach($ministry->positions as $position)
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="ministries[{{ $ministry->id }}][positions][]" value="{{ $position->id }}"
                                           {{ in_array($position->id, $personPositionIds ?? []) ? 'checked' : '' }}
                                           class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $position->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Notes -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Нотатки</h2>

            <textarea name="notes" rows="4" placeholder="Додаткова інформація про людину..."
                      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400">{{ old('notes', $person->notes) }}</textarea>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <a href="{{ route('people.show', $person) }}"
               class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Переглянути профіль
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('people.index') }}"
                   class="px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                    Назад
                </a>
                <button type="submit"
                        class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                    Зберегти
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function avatarUpload() {
    return {
        preview: null,
        isDragging: false,
        existingPhoto: {{ $person->photo ? 'true' : 'false' }},

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.showPreview(file);
            }
        },

        handleDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                const input = this.$el.querySelector('input[type="file"]');
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;
                this.showPreview(file);
            }
        },

        showPreview(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.preview = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        removePhoto() {
            this.preview = null;
            this.existingPhoto = false;
            this.$refs.removePhotoInput.value = '1';
            const input = this.$el.querySelector('input[type="file"]');
            input.value = '';
        }
    }
}
</script>
@endsection
