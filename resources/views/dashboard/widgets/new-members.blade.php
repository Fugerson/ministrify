{{-- New Members Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">Нові учасники</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Додані цього місяця</p>
                </div>
            </div>
            @if($newMembers->count() > 0)
                <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/50 px-2.5 py-1 rounded-lg">+{{ $newMembers->count() }}</span>
            @endif
        </div>
    </div>

    @if($newMembers->count() > 0)
        <div class="divide-y divide-gray-50 dark:divide-gray-700">
            @foreach($newMembers->take(8) as $member)
                <a href="{{ route('people.show', $member) }}" class="flex items-center gap-3 px-4 lg:px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    @if($member->photo)
                        <img src="{{ Storage::url($member->photo) }}" alt="{{ $member->full_name }}" class="w-10 h-10 rounded-xl object-cover flex-shrink-0">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">{{ mb_substr($member->first_name, 0, 1) }}</span>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $member->full_name }}</p>
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ $member->created_at->diffForHumans() }}</span>
                            @if($member->phone)
                                <span class="text-gray-300 dark:text-gray-600">&middot;</span>
                                <span>{{ $member->phone }}</span>
                            @endif
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 px-4">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Немає нових учасників цього місяця</p>
        </div>
    @endif
</div>
