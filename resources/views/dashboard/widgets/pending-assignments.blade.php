{{-- Pending Assignments Widget --}}
@if(count($pendingAssignments) > 0)
<div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/30 dark:to-orange-900/30 rounded-2xl border border-amber-100 dark:border-amber-800 p-4">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="font-semibold text-gray-900 dark:text-white">Очікує підтвердження</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">У вас {{ count($pendingAssignments) }} призначень</p>
            <div class="mt-3 space-y-2">
                @foreach($pendingAssignments->take(3) as $assignment)
                <div class="bg-white dark:bg-gray-800 rounded-xl p-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white text-sm truncate">{{ $assignment->event->title }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $assignment->event->date->format('d.m') }} &bull; {{ $assignment->position->name }}</p>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <form method="POST" action="{{ route('assignments.confirm', $assignment) }}">
                            @csrf
                            <button type="submit" class="w-11 h-11 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 rounded-xl flex items-center justify-center hover:bg-green-200 dark:hover:bg-green-800 active:bg-green-300 dark:active:bg-green-700 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('assignments.decline', $assignment) }}">
                            @csrf
                            <button type="submit" class="w-11 h-11 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400 rounded-xl flex items-center justify-center hover:bg-red-200 dark:hover:bg-red-800 active:bg-red-300 dark:active:bg-red-700 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
