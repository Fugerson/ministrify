{{-- Upcoming Events Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900 dark:text-white">Найближчі події</h2>
        <a href="{{ route('schedule') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Всі</a>
    </div>
    <div class="divide-y divide-gray-50 dark:divide-gray-700">
        @forelse($upcomingEvents as $event)
        <a href="{{ route('events.show', $event) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: {{ $event->ministry?->color ?? '#3b82f6' }}30;">
                <svg class="w-6 h-6" style="color: {{ $event->ministry?->color ?? '#3b82f6' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <p class="font-medium text-gray-900 dark:text-white truncate">{{ $event->title }}</p>
                    @if($event->isFullyStaffed())
                    <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></span>
                    @else
                    <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $event->date->format('d.m') }} &bull; {{ $event->time->format('H:i') }}</p>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $event->filled_positions_count }}/{{ $event->total_positions_count }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">позицій</p>
            </div>
        </a>
        @empty
        <div class="p-8 text-center">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Немає запланованих подій</p>
        </div>
        @endforelse
    </div>
</div>
