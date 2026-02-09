@extends('layouts.app')

@section('title', 'Редагувати подію')

@php
    $ministriesJson = $ministries->map(function($m) {
        return ['id' => $m->id, 'name' => $m->name, 'color' => $m->color];
    })->values()->toJson();
@endphp

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('events.show', $event) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Назад до події
        </a>
    </div>

    <form method="POST" action="{{ route('events.update', $event) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Редагувати подію</h2>

            <div class="space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва *</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $event->title) }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата *</label>
                        <input type="date" name="date" id="date" value="{{ old('date', $event->date->format('Y-m-d')) }}" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label for="time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Час *</label>
                        <input type="time" name="time" id="time" value="{{ old('time', $event->time?->format('H:i')) }}" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нотатки</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ old('notes', $event->notes) }}</textarea>
                </div>

                <!-- Ministry/Team Selection -->
                @if($ministries->count() > 0)
                <div x-data="{
                    selectedId: '{{ old('ministry_id', $event->ministry_id) }}',
                    ministries: {!! $ministriesJson !!},
                    get selected() {
                        const self = this;
                        return this.ministries.find(function(m) { return m.id == self.selectedId; });
                    }
                }">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Команда</label>
                    <x-searchable-select
                        name="ministry_id"
                        :items="$ministries"
                        :selected="old('ministry_id', $event->ministry_id)"
                        labelKey="name"
                        valueKey="id"
                        colorKey="color"
                        placeholder="Пошук команди..."
                        nullText="Без команди"
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
                               {{ old('is_service', $event->is_service) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500 dark:focus:ring-primary-600">
                        <label for="is_service" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Це подія з планом
                        </label>
                    </div>
                    <p class="mt-1 ml-6 text-xs text-gray-500 dark:text-gray-400">
                        Увімкніть, щоб створити план події з таймлайном
                    </p>

                    @if($event->is_service)
                        <div class="mt-3">
                            <a href="{{ route('events.show', $event) }}"
                               class="inline-flex items-center px-4 py-2 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-medium rounded-lg hover:bg-primary-200 dark:hover:bg-primary-900/50 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Переглянути план події
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Music Ministry Option -->
                <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex items-center">
                        <input type="checkbox" name="has_music" id="has_music" value="1"
                               {{ old('has_music', $event->has_music) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500 dark:focus:ring-primary-600">
                        <label for="has_music" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Подія з музичним супроводом
                        </label>
                    </div>
                    <p class="mt-1 ml-6 text-xs text-gray-500 dark:text-gray-400">
                        Увімкніть, щоб подія відображалась в порталі музичних команд
                    </p>
                </div>

                <!-- Attendance Tracking Option -->
                @if($currentChurch->attendance_enabled)
                <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex items-center">
                        <input type="checkbox" name="track_attendance" id="track_attendance" value="1"
                               {{ old('track_attendance', $event->track_attendance) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500 dark:focus:ring-primary-600">
                        <label for="track_attendance" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Відстежувати відвідуваність
                        </label>
                    </div>
                    <p class="mt-1 ml-6 text-xs text-gray-500 dark:text-gray-400">
                        Увімкніть, щоб відмічати хто був на цій події
                    </p>
                </div>
                @endif

                <!-- Reminder Settings -->
                @if($currentChurch->telegram_bot_token)
                <div class="pt-4 border-t border-gray-200 dark:border-gray-600" x-data="reminderSettings({{ json_encode($event->reminder_settings ?? []) }})">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Нагадування в Telegram
                    </label>

                    <div class="space-y-2">
                        <template x-for="(reminder, index) in reminders" :key="index">
                            <div class="flex items-center gap-2">
                                <select x-model="reminder.type" @change="updateReminder(index)"
                                        class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="days">За днів до</option>
                                    <option value="hours">За годин до</option>
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
                    </div>

                    <button type="button" @click="addReminder()"
                            class="mt-3 inline-flex items-center gap-1 text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Додати нагадування
                    </button>

                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Нагадування надсилатимуться призначеним служителям
                    </p>

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
                @if($event->google_event_id)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1"></span>
                        Синхронізовано
                    </span>
                @endif
            </div>
            <select name="google_calendar_id" x-model="calendarId"
                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm">
                <option value="">Не синхронізувати з Google</option>
                <option value="primary">Основний календар</option>
                <template x-for="cal in calendars" :key="cal.id">
                    <option :value="cal.id" :disabled="!cal.can_sync"
                            x-text="cal.summary + (cal.can_sync ? '' : ' (тільки читання)')"></option>
                </template>
            </select>
            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400" x-text="statusText"></p>
        </div>
        @endif

        <div class="flex items-center justify-between">
            <button type="button"
                    onclick="if(confirm('Видалити подію?')) { document.getElementById('delete-event-form').submit(); }"
                    class="text-red-600 hover:text-red-800 text-sm font-medium">
                Видалити подію
            </button>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-2 sm:gap-3">
                <a href="{{ route('events.show', $event) }}" class="w-full sm:w-auto px-4 py-2 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    Скасувати
                </a>
                <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Зберегти
                </button>
            </div>
        </div>
    </form>

    <form id="delete-event-form" method="POST" action="{{ route('events.destroy', $event) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection

@push('scripts')
<script>
function googleCalendarPicker() {
    return {
        calendarId: '{{ $event->google_calendar_id ?? ($gcCalendarId ?? "primary") }}',
        calendars: [],
        get statusText() {
            if (!this.calendarId) return 'Подія не буде синхронізуватись з Google Calendar';
            @if($event->google_event_id)
                const currentCal = this.calendars.find(c => c.id === this.calendarId);
                const calName = this.calendarId === 'primary' ? 'Основний календар' : (currentCal?.summary || this.calendarId);
                return 'Прив\u0027язано до: ' + calName;
            @else
                return 'Подія автоматично з\u0027явиться в обраному календарі';
            @endif
        },
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
</script>
@endpush
