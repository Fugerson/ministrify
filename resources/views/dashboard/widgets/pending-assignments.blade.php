{{-- Pending Assignments Widget --}}
<div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/30 dark:to-orange-900/30 rounded-2xl border border-amber-100 dark:border-amber-800 p-4">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="font-semibold text-gray-900 dark:text-white">Очікує підтвердження</h3>

            {{-- Pending Users (admin only) --}}
            @if(!empty($pendingUsers) && count($pendingUsers) > 0)
            <p class="text-sm text-amber-700 dark:text-amber-400 font-medium mt-2">
                <svg class="w-4 h-4 inline -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                {{ count($pendingUsers) }} {{ count($pendingUsers) === 1 ? 'користувач очікує' : 'користувачів очікують' }} на роль
            </p>
            <div class="mt-2 space-y-2">
                @foreach($pendingUsers as $pendingUser)
                <a href="{{ route('people.show', $pendingUser->person_id) }}" class="block bg-white dark:bg-gray-800 rounded-xl p-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $pendingUser->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $pendingUser->email }} &bull; {{ \Carbon\Carbon::parse($pendingUser->joined_at)->diffForHumans() }}</p>
                </a>
                @endforeach
            </div>
            @endif

            {{-- Pending Assignments --}}
            @if(count($pendingAssignments) > 0)
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">У вас {{ count($pendingAssignments) }} призначень</p>
            <div class="mt-3 space-y-2">
                @foreach($pendingAssignments->take(3) as $assignment)
                <div class="bg-white dark:bg-gray-800 rounded-xl p-3 flex items-center justify-between gap-3" x-data="{ responding: false }">
                    <a href="{{ route('events.show', $assignment->event) }}" class="min-w-0 hover:opacity-80 transition-opacity">
                        <p class="font-medium text-gray-900 dark:text-white text-sm truncate">{{ $assignment->event?->title }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $assignment->event?->date?->format('d.m') }} &bull; {{ $assignment->position?->name }}</p>
                    </a>
                    <div class="flex gap-2 flex-shrink-0" x-show="!responding">
                        <button @click="responding = true; fetch('/api/pwa/responsibilities/{{ $assignment->id }}/confirm', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(() => $el.closest('[x-data]').remove())"
                                class="w-11 h-11 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 rounded-xl flex items-center justify-center hover:bg-green-200 dark:hover:bg-green-800 active:bg-green-300 dark:active:bg-green-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                        <button @click="responding = true; fetch('/api/pwa/responsibilities/{{ $assignment->id }}/decline', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(() => $el.closest('[x-data]').remove())"
                                class="w-11 h-11 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400 rounded-xl flex items-center justify-center hover:bg-red-200 dark:hover:bg-red-800 active:bg-red-300 dark:active:bg-red-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div x-show="responding" class="text-xs text-gray-400">
                        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </div>
                </div>
                @endforeach
            </div>
            @if($pendingAssignments->count() > 3)
                <a href="{{ route('my-schedule') }}" class="block mt-2 text-center text-xs text-primary-600 dark:text-primary-400 hover:underline">
                    Всі призначення ({{ $pendingAssignments->count() }})
                </a>
            @endif
            @elseif(empty($pendingUsers) || count($pendingUsers) === 0)
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Немає призначень для підтвердження</p>
            @endif
        </div>
    </div>
</div>
