@extends('layouts.app')

@section('title', 'Створити подію')

@section('content')
<div class="max-w-2xl mx-auto">
    <form method="POST" action="{{ route('events.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Нова подія</h2>

            <div class="space-y-4">
                @if($selectedMinistry && $ministries->contains('id', $selectedMinistry))
                    @php $preselectedMinistry = $ministries->firstWhere('id', $selectedMinistry); @endphp
                    <input type="hidden" name="ministry_id" value="{{ $selectedMinistry }}">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Служіння</label>
                        <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg">
                            @if($preselectedMinistry->color)
                                <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $preselectedMinistry->color }}"></span>
                            @endif
                            <span class="text-gray-900 dark:text-white font-medium">{{ $preselectedMinistry->name }}</span>
                        </div>
                    </div>
                @else
                    <div>
                        <label for="ministry_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Служіння *</label>
                        <select name="ministry_id" id="ministry_id" required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Виберіть служіння</option>
                            @foreach($ministries as $ministry)
                                <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                           placeholder="Недільне служіння">
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

                <div>
                    <label for="recurrence_rule" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Повторення</label>
                    <select name="recurrence_rule" id="recurrence_rule"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">Не повторювати</option>
                        <option value="weekly">Щотижня (наступні 4 тижні)</option>
                    </select>
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
