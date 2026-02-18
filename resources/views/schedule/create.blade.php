@extends('layouts.app')

@section('title', __('Створити подію'))

@php
    $ministriesData = $ministries->map(function($m) {
        return ['id' => $m->id, 'name' => $m->name, 'color' => $m->color];
    })->values();
@endphp

@section('content')
<div class="max-w-2xl mx-auto" x-data="eventCreateForm()">
    <form class="space-y-6" x-ref="form" @submit.prevent="submitForm">

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Нова подія') }}</h2>

            <div class="space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Назва') }} *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                           :class="errors.title ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                           placeholder="{{ __('Недільне богослужіння') }}">
                    <template x-if="errors.title"><p class="mt-1 text-sm text-red-500" x-text="errors.title[0]"></p></template>
                </div>

                <div x-data="{ allDay: {{ old('all_day') ? 'true' : 'false' }}, multiDay: {{ old('end_date') ? 'true' : 'false' }} }">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Дата') }} *</label>
                            <input type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                                   class="w-full px-3 py-2.5 md:py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                   :class="errors.date ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                            <template x-if="errors.date"><p class="mt-1 text-sm text-red-500" x-text="errors.date[0]"></p></template>
                        </div>

                        <div x-show="!allDay" x-transition>
                            <label for="time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Час') }} *</label>
                            <input type="time" name="time" id="time" value="{{ old('time', '10:00') }}" :required="!allDay"
                                   class="w-full px-3 py-2.5 md:py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                   :class="errors.time ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                            <template x-if="errors.time"><p class="mt-1 text-sm text-red-500" x-text="errors.time[0]"></p></template>
                        </div>
                    </div>

                    <!-- Multi-day event toggle -->
                    <label class="flex items-center gap-2 mt-3 cursor-pointer">
                        <input type="checkbox" name="multi_day" value="1" x-model="multiDay"
                               class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Подія на кілька днів') }}</span>
                    </label>

                    <!-- End date for multi-day events -->
                    <div x-show="multiDay" x-collapse class="mt-3">
                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Дата закінчення') }} *</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                               class="w-full px-3 py-2.5 md:py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               :class="errors.end_date ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                        <template x-if="errors.end_date"><p class="mt-1 text-sm text-red-500" x-text="errors.end_date[0]"></p></template>
                    </div>

                    <label class="flex items-center gap-2 mt-2 cursor-pointer">
                        <input type="checkbox" name="all_day" value="1" x-model="allDay"
                               class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Подія на весь день') }}</span>
                    </label>
                </div>

                <div x-data="{ showRecurrence: false, ...recurrenceSettings() }" x-init="init()">
                    <button type="button" @click="showRecurrence = !showRecurrence"
                            class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                        <svg class="w-4 h-4 transition-transform" :class="showRecurrence ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ __('Повторення') }}
                        <span x-show="recurrenceType" x-cloak class="text-xs text-primary-600 dark:text-primary-400 font-medium"
                              x-text="recurrenceType ? '(' + ({'daily':'щодня','weekly':'щотижня','biweekly':'що 2 тижні','monthly':'щомісяця','yearly':'щороку','weekdays':'пн-пт','custom':'свій'}[recurrenceType] || '') + ')' : ''"></span>
                    </button>

                    <div x-show="showRecurrence" x-collapse class="mt-3 space-y-3">
                        <select x-model="recurrenceType" @change="updateRecurrence()"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">{{ __('Не повторювати') }}</option>
                            <option value="daily">{{ __('Щодня') }}</option>
                            <option value="weekly">{{ __('Щотижня') }}</option>
                            <option value="biweekly">{{ __('Що 2 тижні') }}</option>
                            <option value="monthly">{{ __('Щомісяця') }}</option>
                            <option value="yearly">{{ __('Щороку') }}</option>
                            <option value="weekdays">{{ __('Кожен робочий день (пн-пт)') }}</option>
                            <option value="custom">{{ __('Користувацьке...') }}</option>
                        </select>

                        <!-- Custom recurrence settings -->
                        <div x-show="recurrenceType === 'custom'" x-collapse class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Кожні') }}</span>
                                <input type="number" x-model="customInterval" @input="updatePreview()" min="1" max="99"
                                       class="w-16 px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center">
                                <select x-model="customFrequency" @change="updatePreview()"
                                        class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="day">{{ __('днів') }}</option>
                                    <option value="week">{{ __('тижнів') }}</option>
                                    <option value="month">{{ __('місяців') }}</option>
                                    <option value="year">{{ __('років') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Recurrence end settings -->
                        <div x-show="recurrenceType && recurrenceType !== ''" x-collapse class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg space-y-3">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Закінчується') }}</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" x-model="endType" value="count" @change="updatePreview()" class="text-primary-600 focus:ring-primary-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Після') }}</span>
                                    <input type="number" x-model="endCount" @input="updatePreview()" min="2" max="365" :disabled="endType !== 'count'"
                                           class="w-16 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center disabled:opacity-50">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('повторень') }}</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" x-model="endType" value="date" @change="updatePreview()" class="text-primary-600 focus:ring-primary-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('До дати') }}</span>
                                    <input type="date" x-model="endDate" @change="updatePreview()" :disabled="endType !== 'date'"
                                           class="px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white disabled:opacity-50">
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="previewText"></p>
                        </div>
                    </div>

                    <!-- Hidden inputs for form submission -->
                    <input type="hidden" name="recurrence_rule" :value="getRecurrenceRule()">
                    <input type="hidden" name="recurrence_end_type" :value="endType">
                    <input type="hidden" name="recurrence_end_count" :value="endCount">
                    <input type="hidden" name="recurrence_end_date" :value="endDate">
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Нотатки') }}</label>
                    <textarea name="notes" id="notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                              placeholder="{{ __('Додаткова інформація...') }}">{{ old('notes') }}</textarea>
                </div>

                <!-- Ministry/Team Selection -->
                @if($ministries->count() > 0)
                <div x-data="{
                    selectedId: '{{ $selectedMinistry ?? old('ministry_id', '') }}',
                    ministries: @json($ministriesData),
                    get selected() {
                        const self = this;
                        return this.ministries.find(function(m) { return m.id == self.selectedId; });
                    }
                }">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Команда') }}</label>
                    <x-searchable-select
                        name="ministry_id"
                        :items="$ministries"
                        :selected="$selectedMinistry ?? old('ministry_id')"
                        labelKey="name"
                        valueKey="id"
                        colorKey="color"
                        placeholder="{{ __('Пошук команди...') }}"
                        nullText="{{ __('Без команди') }}"
                        nullable
                        x-on:select-changed="selectedId = $event.detail.value || ''"
                    />
                    <!-- Colorful label preview -->
                    <div x-show="selected" x-cloak class="mt-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                              :style="selected ? 'background-color: ' + selected.color + '20; color: ' + selected.color + '; border: 1px solid ' + selected.color + '40' : ''">
                            <span class="w-2 h-2 rounded-full" :style="selected ? 'background-color: ' + selected.color : ''"></span>
                            <span x-text="selected?.name"></span>
                        </span>
                    </div>
                </div>
                @endif

                <!-- Service Plan Option -->
                <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_service" id="is_service" value="1"
                               {{ old('is_service') ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500 dark:focus:ring-primary-600">
                        <label for="is_service" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Це подія з планом') }}
                        </label>
                    </div>
                    <p class="mt-1 ml-6 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Увімкніть, щоб створити план події з таймлайном (прославлення, проповідь, оголошення тощо)') }}
                    </p>
                </div>

                <!-- Attendance Tracking Option -->
                @if($currentChurch->attendance_enabled)
                <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex items-center">
                        <input type="checkbox" name="track_attendance" id="track_attendance" value="1"
                               {{ old('track_attendance') ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500 dark:focus:ring-primary-600">
                        <label for="track_attendance" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Відстежувати відвідуваність') }}
                        </label>
                    </div>
                    <p class="mt-1 ml-6 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Увімкніть, щоб відмічати хто був на цій події') }}
                    </p>
                </div>
                @endif

                <!-- Reminder Settings -->
                @if($currentChurch->telegram_bot_token)
                <div x-data="{ showReminders: false, ...reminderSettings() }">
                    <button type="button" @click="showReminders = !showReminders"
                            class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                        <svg class="w-4 h-4 transition-transform" :class="showReminders ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        {{ __('Нагадування в Telegram') }}
                        <span x-show="reminders.length > 0" x-cloak class="inline-flex items-center justify-center w-5 h-5 text-xs font-medium bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-full" x-text="reminders.length"></span>
                    </button>

                    <div x-show="showReminders" x-collapse class="mt-3 space-y-2">
                        <template x-for="(reminder, index) in reminders" :key="index">
                            <div class="flex items-center gap-2">
                                <select x-model="reminder.type" @change="updateReminder(index)"
                                        class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="days">{{ __('За днів до') }}</option>
                                    <option value="hours">{{ __('За годин до') }}</option>
                                </select>
                                <input type="number" x-model="reminder.value" min="1" max="30"
                                       class="w-20 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center">
                                <template x-if="reminder.type === 'days'">
                                    <input type="time" x-model="reminder.time"
                                           class="w-28 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                </template>
                                <button type="button" @click="removeReminder(index)"
                                        class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <button type="button" @click="addReminder()"
                                class="inline-flex items-center gap-1 text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Додати нагадування') }}
                        </button>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Нагадування надсилатимуться призначеним служителям') }}
                        </p>
                    </div>

                    <!-- Hidden inputs for form submission -->
                    <template x-for="(reminder, index) in reminders" :key="'input-'+index">
                        <div>
                            <input type="hidden" :name="'reminders['+index+'][type]'" :value="reminder.type">
                            <input type="hidden" :name="'reminders['+index+'][value]'" :value="reminder.value">
                            <input type="hidden" :name="'reminders['+index+'][time]'" :value="reminder.time || ''">
                        </div>
                    </template>
                </div>
                @endif
            </div>
        </div>

        @php
            $gcSettings = auth()->user()->settings['google_calendar'] ?? null;
            $gcConnected = $gcSettings && !empty($gcSettings['access_token']);
            $gcCalendarId = $gcSettings['calendar_id'] ?? 'primary';
        @endphp
        @if($gcConnected)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6" x-data="googleCalendarPicker()">
            <div class="flex items-center gap-3 mb-3">
                <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none">
                    <path d="M19 4H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z" stroke="#4285F4" stroke-width="1.5"/>
                    <path d="M8 2v4M16 2v4M3 10h18" stroke="#4285F4" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <span class="text-sm font-medium text-gray-900 dark:text-white">Google Calendar</span>
            </div>
            <select name="google_calendar_id" x-model="calendarId"
                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm">
                <option value="">{{ __('Не додавати в Google') }}</option>
                <option value="primary">{{ __('Основний календар') }}</option>
                <template x-for="cal in calendars" :key="cal.id">
                    <option :value="cal.id" :disabled="!cal.can_sync" :selected="cal.id === defaultCalendarId"
                            x-text="cal.summary + (cal.can_sync ? '' : ' (тільки читання)')"></option>
                </template>
            </select>
            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ __('Подія автоматично з\'явиться в обраному календарі') }}</p>
        </div>
        @endif

        <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 sm:space-x-4">
            <a href="{{ route('schedule') }}" class="w-full sm:w-auto text-center px-4 py-2.5 md:py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                {{ __('Скасувати') }}
            </a>
            <button type="submit" :disabled="saving" class="w-full sm:w-auto px-6 py-2.5 md:py-2 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                <svg x-show="saving" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="saving ? '{{ __('Створення...') }}' : '{{ __('Створити') }}'"></span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function eventCreateForm() {
    return {
        saving: false,
        errors: {},
        async submitForm() {
            this.saving = true;
            this.errors = {};
            const formData = new FormData(this.$refs.form);
            try {
                const response = await fetch('{{ route("events.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const data = await response.json();
                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.errors = data.errors;
                        showToast('error', 'Перевірте правильність заповнення форми.');
                    } else {
                        showToast('error', data.message || 'Помилка збереження.');
                    }
                    this.saving = false;
                    return;
                }
                showToast('success', data.message || 'Збережено!');
                setTimeout(() => window.location.href = data.redirect_url, 800);
            } catch (e) {
                showToast('error', "Помилка з'єднання з сервером.");
                this.saving = false;
            }
        }
    }
}

function recurrenceSettings() {
    return {
        recurrenceType: '',
        customInterval: 1,
        customFrequency: 'week',
        endType: 'count',
        endCount: 12,
        endDate: '',
        previewText: '',

        init() {
            // Set default end date to 3 months from now
            const defaultEnd = new Date();
            defaultEnd.setMonth(defaultEnd.getMonth() + 3);
            this.endDate = defaultEnd.toISOString().split('T')[0];
            this.updatePreview();
        },

        updateRecurrence() {
            // Set sensible defaults based on type
            switch(this.recurrenceType) {
                case 'daily':
                    this.endCount = 30;
                    break;
                case 'weekly':
                case 'biweekly':
                    this.endCount = 12;
                    break;
                case 'monthly':
                    this.endCount = 12;
                    break;
                case 'yearly':
                    this.endCount = 5;
                    break;
                case 'weekdays':
                    this.endCount = 20;
                    break;
            }
            this.updatePreview();
        },

        updatePreview() {
            if (!this.recurrenceType) {
                this.previewText = '';
                return;
            }

            const typeLabels = {
                'daily': 'щодня',
                'weekly': 'щотижня',
                'biweekly': 'що 2 тижні',
                'monthly': 'щомісяця',
                'yearly': 'щороку',
                'weekdays': 'кожен робочий день',
                'custom': `кожні ${this.customInterval} ${this.getFrequencyLabel()}`
            };

            const label = typeLabels[this.recurrenceType] || '';

            if (this.endType === 'count') {
                this.previewText = `Буде створено ${this.endCount} подій (${label})`;
            } else {
                this.previewText = `Повторюватиметься ${label} до ${this.formatDate(this.endDate)}`;
            }
        },

        getFrequencyLabel() {
            const labels = {
                'day': this.customInterval == 1 ? 'день' : 'днів',
                'week': this.customInterval == 1 ? 'тиждень' : 'тижнів',
                'month': this.customInterval == 1 ? 'місяць' : 'місяців',
                'year': this.customInterval == 1 ? 'рік' : 'років'
            };
            return labels[this.customFrequency] || '';
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleDateString('uk-UA', { day: 'numeric', month: 'long', year: 'numeric' });
        },

        getRecurrenceRule() {
            if (!this.recurrenceType) return '';

            if (this.recurrenceType === 'custom') {
                return `custom:${this.customInterval}:${this.customFrequency}`;
            }
            return this.recurrenceType;
        }
    }
}

function reminderSettings(initial = []) {
    return {
        reminders: initial.length ? initial : [],
        addReminder() {
            this.reminders.push({
                type: 'days',
                value: 1,
                time: '18:00'
            });
        },
        removeReminder(index) {
            this.reminders.splice(index, 1);
        },
        updateReminder(index) {
            if (this.reminders[index].type === 'hours') {
                this.reminders[index].time = null;
            } else {
                this.reminders[index].time = '18:00';
            }
        }
    }
}

function googleCalendarPicker() {
    return {
        calendarId: '{{ $gcCalendarId ?? "primary" }}',
        defaultCalendarId: '{{ $gcCalendarId ?? "primary" }}',
        calendars: [],
        async init() {
            try {
                const res = await fetch('{{ route("settings.google-calendar.calendars") }}');
                if (res.ok) {
                    const data = await res.json();
                    this.calendars = data.calendars || [];
                }
            } catch (e) {}
        }
    }
}
</script>
@endpush
