@extends('layouts.app')

@section('title', 'Створити подію')

@section('content')
<div class="max-w-2xl mx-auto">
    <form method="POST" action="{{ route('events.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Нова подія</h2>

            <div class="space-y-4">
                <div x-data="{ useCustomLabel: {{ ($selectedMinistry && $ministries->contains('id', $selectedMinistry)) ? 'false' : 'false' }} }">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Команда</label>

                    @if($selectedMinistry && $ministries->contains('id', $selectedMinistry))
                        @php $preselectedMinistry = $ministries->firstWhere('id', $selectedMinistry); @endphp
                        <input type="hidden" name="ministry_id" value="{{ $selectedMinistry }}">
                        <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg">
                            @if($preselectedMinistry->color)
                                <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $preselectedMinistry->color }}"></span>
                            @endif
                            <span class="text-gray-900 dark:text-white font-medium">{{ $preselectedMinistry->name }}</span>
                        </div>
                    @else
                        <div class="flex items-center gap-2 mb-2">
                            <button type="button" @click="useCustomLabel = false"
                                    :class="!useCustomLabel ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 border-primary-300 dark:border-primary-700' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-400 border-gray-300 dark:border-gray-600'"
                                    class="px-3 py-1.5 text-sm rounded-lg border transition-colors">
                                Вибрати команду
                            </button>
                            <button type="button" @click="useCustomLabel = true"
                                    :class="useCustomLabel ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 border-primary-300 dark:border-primary-700' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-400 border-gray-300 dark:border-gray-600'"
                                    class="px-3 py-1.5 text-sm rounded-lg border transition-colors">
                                Свій лейбл
                            </button>
                        </div>

                        <div x-show="!useCustomLabel" x-cloak>
                            <select name="ministry_id" id="ministry_id"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">Без команди</option>
                                @foreach($ministries as $ministry)
                                    <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="useCustomLabel" x-cloak>
                            <input type="text" name="ministry_label" id="ministry_label" value="{{ old('ministry_label') }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                   placeholder="Напр.: Недільна школа, Молодіжка...">
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Необов'язково. Можна вибрати команду або ввести свій текст.</p>
                    @endif
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                           placeholder="Недільне богослужіння">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата *</label>
                        <input type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                               class="w-full px-3 py-2.5 md:py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label for="time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Час *</label>
                        <input type="time" name="time" id="time" value="{{ old('time', '10:00') }}" required
                               class="w-full px-3 py-2.5 md:py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>

                <div x-data="recurrenceSettings()" x-init="init()">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Повторення</label>
                    <select x-model="recurrenceType" @change="updateRecurrence()"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">Не повторювати</option>
                        <option value="daily">Щодня</option>
                        <option value="weekly">Щотижня</option>
                        <option value="biweekly">Що 2 тижні</option>
                        <option value="monthly">Щомісяця</option>
                        <option value="yearly">Щороку</option>
                        <option value="weekdays">Кожен робочий день (пн-пт)</option>
                        <option value="custom">Користувацьке...</option>
                    </select>

                    <!-- Custom recurrence settings -->
                    <div x-show="recurrenceType === 'custom'" x-collapse class="mt-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Кожні</span>
                            <input type="number" x-model="customInterval" @input="updatePreview()" min="1" max="99"
                                   class="w-16 px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center">
                            <select x-model="customFrequency" @change="updatePreview()"
                                    class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="day">днів</option>
                                <option value="week">тижнів</option>
                                <option value="month">місяців</option>
                                <option value="year">років</option>
                            </select>
                        </div>
                    </div>

                    <!-- Recurrence end settings -->
                    <div x-show="recurrenceType && recurrenceType !== ''" x-collapse class="mt-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg space-y-3">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Закінчується</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" x-model="endType" value="count" @change="updatePreview()" class="text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Після</span>
                                <input type="number" x-model="endCount" @input="updatePreview()" min="2" max="365" :disabled="endType !== 'count'"
                                       class="w-16 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center disabled:opacity-50">
                                <span class="text-sm text-gray-700 dark:text-gray-300">повторень</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" x-model="endType" value="date" @change="updatePreview()" class="text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">До дати</span>
                                <input type="date" x-model="endDate" @change="updatePreview()" :disabled="endType !== 'date'"
                                       class="px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white disabled:opacity-50">
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="previewText"></p>
                    </div>

                    <!-- Hidden inputs for form submission -->
                    <input type="hidden" name="recurrence_rule" :value="getRecurrenceRule()">
                    <input type="hidden" name="recurrence_end_type" :value="endType">
                    <input type="hidden" name="recurrence_end_count" :value="endCount">
                    <input type="hidden" name="recurrence_end_date" :value="endDate">
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нотатки</label>
                    <textarea name="notes" id="notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                              placeholder="Додаткова інформація...">{{ old('notes') }}</textarea>
                </div>

                <!-- Service Plan Option -->
                <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_service" id="is_service" value="1"
                               {{ old('is_service') ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500 dark:focus:ring-primary-600">
                        <label for="is_service" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Це подія з планом
                        </label>
                    </div>
                    <p class="mt-1 ml-6 text-xs text-gray-500 dark:text-gray-400">
                        Увімкніть, щоб створити план події з таймлайном (прославлення, проповідь, оголошення тощо)
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
                <div class="pt-4 border-t border-gray-200 dark:border-gray-600" x-data="reminderSettings()">
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

        <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 sm:space-x-4">
            <a href="{{ route('schedule') }}" class="w-full sm:w-auto text-center px-4 py-2.5 md:py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                Скасувати
            </a>
            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 md:py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Створити
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
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
</script>
@endpush
