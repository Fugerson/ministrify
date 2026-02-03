{{-- Volunteer Schedule Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h2 class="font-semibold text-gray-900 dark:text-white">Розклад служителів</h2>
            @if($volunteerSchedule->count() > 0)
            <span class="text-xs font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/50 px-2 py-0.5 rounded-lg">{{ $volunteerSchedule->count() }}</span>
            @endif
        </div>
        <span class="text-xs text-gray-500 dark:text-gray-400">Наступні 7 днів</span>
    </div>
    <div class="divide-y divide-gray-50 dark:divide-gray-700">
        @forelse($volunteerSchedule->groupBy(fn($a) => $a->event->date->format('Y-m-d')) as $date => $assignments)
        <div class="p-4">
            {{-- Date header --}}
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/50 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-primary-600 dark:text-primary-400">{{ \Carbon\Carbon::parse($date)->format('d') }}</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ \Carbon\Carbon::parse($date)->locale('uk')->translatedFormat('l') }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ \Carbon\Carbon::parse($date)->locale('uk')->translatedFormat('d F') }}
                    </p>
                </div>
            </div>

            {{-- Assignments for this date --}}
            <div class="space-y-2 ml-10">
                @foreach($assignments as $assignment)
                <a href="{{ route('events.show', $assignment->event) }}" class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    {{-- Status indicator --}}
                    <div class="w-2 h-2 rounded-full flex-shrink-0 {{ $assignment->status === 'confirmed' ? 'bg-green-500' : 'bg-amber-500' }}"></div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $assignment->event->title }}</p>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $assignment->event->time->format('H:i') }}</span>
                            <span class="text-xs text-gray-300 dark:text-gray-600">&bull;</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $assignment->position->name }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        <div class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-900/50 flex items-center justify-center">
                            <span class="text-xs font-medium text-primary-600 dark:text-primary-400">{{ mb_substr($assignment->person->first_name, 0, 1) }}</span>
                        </div>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 hidden sm:inline">{{ $assignment->person->full_name }}</span>
                    </div>

                    {{-- Status badge --}}
                    <span class="text-xs px-2 py-0.5 rounded-full flex-shrink-0 {{ $assignment->status === 'confirmed' ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-400' : 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-400' }}">
                        {{ $assignment->status === 'confirmed' ? 'Підтв.' : 'Очікує' }}
                    </span>
                </a>
                @endforeach
            </div>
        </div>
        @empty
        <div class="p-8 text-center">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Немає призначень на найближчі 7 днів</p>
        </div>
        @endforelse
    </div>
</div>
