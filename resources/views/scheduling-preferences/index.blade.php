@extends('layouts.app')

@section('title', 'Налаштування планування')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header with back link -->
    <div class="flex items-center gap-4">
        <a href="{{ route('my-profile') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Налаштування планування</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Вкажіть ваші бажані параметри для розкладу подій</p>
        </div>
    </div>

    <!-- General Preferences -->
    <form action="{{ route('scheduling-preferences.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Загальні налаштування
            </h2>

            <div class="space-y-4">
                <!-- Frequency limits -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="max_times_per_month" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Максимум разів на місяць
                        </label>
                        <input type="number" name="max_times_per_month" id="max_times_per_month"
                               value="{{ old('max_times_per_month', $preference->max_times_per_month) }}"
                               min="1" max="30" placeholder="Без обмежень"
                               class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Залиште порожнім, якщо без обмежень</p>
                    </div>

                    <div>
                        <label for="preferred_times_per_month" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Бажано разів на місяць
                        </label>
                        <input type="number" name="preferred_times_per_month" id="preferred_times_per_month"
                               value="{{ old('preferred_times_per_month', $preference->preferred_times_per_month) }}"
                               min="0" max="30" placeholder="Не вказано"
                               class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ідеальна кількість подій</p>
                    </div>
                </div>

                <!-- Prefer with person -->
                <div>
                    <label for="prefer_with_person_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Служити разом з
                    </label>
                    <select name="prefer_with_person_id" id="prefer_with_person_id"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Немає переваг</option>
                        @foreach($otherPeople as $otherPerson)
                            <option value="{{ $otherPerson->id }}"
                                    {{ old('prefer_with_person_id', $preference->prefer_with_person_id) == $otherPerson->id ? 'selected' : '' }}>
                                {{ $otherPerson->full_name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Виберіть особу, з якою бажаєте служити разом</p>
                </div>

                <!-- Household preference -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Сімейні налаштування
                    </label>
                    <div class="space-y-2">
                        @foreach(['none' => 'Не враховувати', 'together' => 'Служити разом з родиною', 'separate' => 'Служити окремо від родини'] as $value => $label)
                        <label class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                            <input type="radio" name="household_preference" value="{{ $value }}"
                                   {{ old('household_preference', $preference->household_preference) === $value ? 'checked' : '' }}
                                   class="text-primary-600 focus:ring-primary-500">
                            <span class="text-gray-700 dark:text-gray-300">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label for="scheduling_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Додаткові примітки
                    </label>
                    <textarea name="scheduling_notes" id="scheduling_notes" rows="3"
                              class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500">{{ old('scheduling_notes', $preference->scheduling_notes) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end">
            <button type="submit"
                    class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                Зберегти налаштування
            </button>
        </div>
    </form>

    <!-- Ministry-specific preferences -->
    @if($ministries->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Налаштування за командами
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Встановіть окремі ліміти для кожної команди (перезаписує загальні налаштування)
        </p>

        <div class="space-y-4">
            @foreach($ministries as $ministry)
                @php
                    $ministryPref = $preference->ministryPreferences->firstWhere('ministry_id', $ministry->id);
                @endphp
                <div x-data="{
                    open: false,
                    loading: false,
                    max: {{ $ministryPref?->max_times_per_month ?? 'null' }},
                    preferred: {{ $ministryPref?->preferred_times_per_month ?? 'null' }},
                    async save() {
                        this.loading = true;
                        try {
                            const response = await fetch('{{ route('scheduling-preferences.ministry.update', $ministry) }}', {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    max_times_per_month: this.max || null,
                                    preferred_times_per_month: this.preferred || null
                                })
                            });
                            const data = await response.json().catch(() => ({}));
                            if (data.success) {
                                this.open = false;
                            }
                        } catch (e) {
                            alert('Помилка збереження');
                        }
                        this.loading = false;
                    }
                }" class="border border-gray-200 dark:border-gray-600 rounded-xl overflow-hidden">
                    <button @click="open = !open" type="button"
                            class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                 style="background-color: {{ $ministry->color ?? '#3b82f6' }}30;">
                                <span class="text-lg" style="color: {{ $ministry->color ?? '#3b82f6' }};">
                                    {{ $ministry->icon ?? '⛪' }}
                                </span>
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $ministry->name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($ministryPref)
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Макс: {{ $ministryPref->max_times_per_month ?? '∞' }}
                                </span>
                            @endif
                            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-gray-200 dark:border-gray-600 p-4 bg-gray-50 dark:bg-gray-700/30">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Максимум на місяць</label>
                                <input type="number" x-model="max" min="1" max="30" placeholder="∞"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Бажано на місяць</label>
                                <input type="number" x-model="preferred" min="0" max="30" placeholder="-"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                            </div>
                        </div>
                        <button @click="save()" :disabled="loading" type="button"
                                class="w-full px-3 py-2 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white text-sm font-medium rounded-lg transition-colors">
                            <span x-show="!loading">Зберегти</span>
                            <span x-show="loading">Збереження...</span>
                        </button>

                        <!-- Positions within ministry -->
                        @if($ministry->positions->isNotEmpty())
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Позиції:</p>
                            <div class="space-y-2">
                                @foreach($ministry->positions as $position)
                                    @php
                                        $positionPref = $preference->positionPreferences->firstWhere('position_id', $position->id);
                                    @endphp
                                    <div x-data="{
                                        expanded: false,
                                        posLoading: false,
                                        posMax: {{ $positionPref?->max_times_per_month ?? 'null' }},
                                        posPreferred: {{ $positionPref?->preferred_times_per_month ?? 'null' }},
                                        async savePosition() {
                                            this.posLoading = true;
                                            try {
                                                await fetch('{{ route('scheduling-preferences.position.update', $position) }}', {
                                                    method: 'PUT',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                    },
                                                    body: JSON.stringify({
                                                        max_times_per_month: this.posMax || null,
                                                        preferred_times_per_month: this.posPreferred || null
                                                    })
                                                });
                                                this.expanded = false;
                                            } catch (e) {
                                                alert('Помилка');
                                            }
                                            this.posLoading = false;
                                        }
                                    }" class="bg-white dark:bg-gray-800 rounded-lg p-2">
                                        <button @click="expanded = !expanded" type="button" class="w-full flex items-center justify-between text-sm">
                                            <span class="text-gray-700 dark:text-gray-300">{{ $position->name }}</span>
                                            @if($positionPref)
                                                <span class="text-xs text-gray-400">Макс: {{ $positionPref->max_times_per_month ?? '∞' }}</span>
                                            @endif
                                        </button>
                                        <div x-show="expanded" x-collapse class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                            <div class="flex gap-2">
                                                <input type="number" x-model="posMax" min="1" max="30" placeholder="Макс"
                                                       class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs">
                                                <button @click="savePosition()" :disabled="posLoading" type="button"
                                                        class="px-3 py-1 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded-lg text-xs">
                                                    OK
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Quick Links -->
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4">
        <h3 class="font-medium text-blue-800 dark:text-blue-200 mb-2">Пов'язані налаштування</h3>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('blockouts.index') }}"
               class="inline-flex items-center gap-1 px-3 py-1.5 bg-white dark:bg-gray-800 rounded-lg text-sm text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                Періоди недоступності
            </a>
            <a href="{{ route('my-profile') }}"
               class="inline-flex items-center gap-1 px-3 py-1.5 bg-white dark:bg-gray-800 rounded-lg text-sm text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Мій профіль
            </a>
        </div>
    </div>
</div>
@endsection
