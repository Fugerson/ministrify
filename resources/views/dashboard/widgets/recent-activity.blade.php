{{-- Recent Activity Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="font-semibold text-gray-900 dark:text-white">Остання активність</h2>
    </div>
    <div class="p-4">
        @if(count($recentActivity) > 0)
        <div class="relative">
            {{-- Vertical timeline line --}}
            <div class="absolute left-4 top-3 bottom-3 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

            <div class="space-y-4">
                @foreach($recentActivity as $activity)
                <div class="relative flex gap-4">
                    {{-- Timeline icon --}}
                    <div class="relative z-10 flex-shrink-0">
                        @if($activity['type'] === 'created')
                        <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        @elseif($activity['type'] === 'updated')
                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </div>
                        @elseif($activity['type'] === 'deleted')
                        <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        @else
                        <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        @endif
                    </div>

                    {{-- Activity content --}}
                    <div class="flex-1 min-w-0 pb-1">
                        <p class="text-sm text-gray-900 dark:text-white">{{ $activity['description'] }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            @if($activity['model_type'])
                            <span class="text-xs font-medium px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                {{ $activity['model_type'] }}
                            </span>
                            @endif
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $activity['user_name'] }}</span>
                            <span class="text-xs text-gray-300 dark:text-gray-600">&bull;</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="py-8 text-center">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Поки що немає активності</p>
        </div>
        @endif
    </div>
</div>
