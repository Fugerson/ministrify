{{-- Popular Songs Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Популярні пісні</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Топ-8 за використанням</p>
            </div>
        </div>
    </div>

    @if($popularSongs->isEmpty())
        <div class="text-center py-8">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Поки немає даних про використання пісень</p>
        </div>
    @else
        <div class="space-y-1">
            @foreach($popularSongs->take(8) as $index => $song)
                <a href="{{ route('songs.show', $song) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                    {{-- Rank number --}}
                    <div class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold
                        {{ $index < 3 ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                        {{ $index + 1 }}
                    </div>

                    {{-- Song info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                            {{ $song->title }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ $song->artist }}
                        </p>
                    </div>

                    {{-- Key badge --}}
                    @if($song->key)
                        <span class="flex-shrink-0 text-xs font-medium px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                            {{ $song->key }}
                        </span>
                    @endif

                    {{-- Times used --}}
                    <div class="flex-shrink-0 text-right">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $song->times_used }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            {{ $song->times_used == 1 ? 'раз' : ($song->times_used >= 2 && $song->times_used <= 4 ? 'рази' : 'разів') }}
                        </p>
                    </div>

                    {{-- Last used --}}
                    <div class="flex-shrink-0 hidden sm:block text-right min-w-[70px]">
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            @if($song->last_used_at)
                                {{ \Carbon\Carbon::parse($song->last_used_at)->locale('uk')->diffForHumans() }}
                            @else
                                ---
                            @endif
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
