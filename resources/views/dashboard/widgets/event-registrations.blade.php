{{-- Event Registrations Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h2 class="font-semibold text-gray-900 dark:text-white">Реєстрація на події</h2>
            @if($eventRegistrations->count() > 0)
            <span class="text-xs font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/50 px-2 py-0.5 rounded-lg">{{ $eventRegistrations->count() }}</span>
            @endif
        </div>
        <a href="{{ route('schedule') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Всі</a>
    </div>
    <div class="divide-y divide-gray-50 dark:divide-gray-700">
        @forelse($eventRegistrations as $event)
        <a href="{{ route('events.show', $event) }}" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: {{ $event->ministry?->color ?? '#3b82f6' }}20;">
                    <svg class="w-6 h-6" style="color: {{ $event->ministry?->color ?? '#3b82f6' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ $event->title }}</p>
                        @if($event->ministry)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0" style="background-color: {{ $event->ministry->color }}20; color: {{ $event->ministry->color }};">
                            {{ $event->ministry->name }}
                        </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        {{ $event->date->format('d.m.Y') }} &bull; {{ $event->time->format('H:i') }}
                    </p>

                    {{-- Registration progress bar --}}
                    @if($event->registration_limit)
                    @php
                        $percentage = $event->registration_limit > 0 ? round(($event->confirmed_registrations_count / $event->registration_limit) * 100) : 0;
                        $barColor = $percentage >= 90 ? 'bg-red-500' : ($percentage >= 70 ? 'bg-amber-500' : 'bg-green-500');
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="flex-1 h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="{{ $barColor }} h-full rounded-full transition-all" style="width: {{ min($percentage, 100) }}%"></div>
                        </div>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 flex-shrink-0">
                            {{ $event->confirmed_registrations_count }}/{{ $event->registration_limit }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $percentage }}% заповнено</span>
                        <span class="text-xs font-medium {{ $event->remaining_spaces > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            @if($event->remaining_spaces > 0)
                                {{ $event->remaining_spaces }} {{ $event->remaining_spaces == 1 ? 'місце' : ($event->remaining_spaces < 5 ? 'місця' : 'місць') }}
                            @else
                                Місць немає
                            @endif
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </a>
        @empty
        <div class="p-8 text-center">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Немає подій з відкритою реєстрацією</p>
        </div>
        @endforelse
    </div>
</div>
