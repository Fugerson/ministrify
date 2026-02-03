{{-- Recent Sermons Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Останні проповіді
        </h2>
        <a href="{{ route('website-builder.sermons.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1">
            Всі
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <div class="divide-y divide-gray-50 dark:divide-gray-700">
        @forelse($recentSermons->take(4) as $sermon)
        <a href="{{ route('website-builder.sermons.show', $sermon) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/50 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ $sermon->title }}</h4>
                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mt-1">
                    @if($sermon->speaker)
                        <span>{{ $sermon->speaker->full_name }}</span>
                        <span>&bull;</span>
                    @endif
                    <span>{{ $sermon->sermon_date->format('d.m.Y') }}</span>
                </div>
                @if($sermon->scripture_reference)
                    <p class="text-xs text-teal-600 dark:text-teal-400 mt-1 italic">{{ $sermon->scripture_reference }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                @if($sermon->youtube_url)
                    <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/50 flex items-center justify-center" title="Відео">
                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                        </svg>
                    </div>
                @endif
                @if($sermon->audio_file)
                    <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center" title="Аудіо">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                        </svg>
                    </div>
                @endif
                @if($sermon->view_count > 0)
                    <span class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        {{ $sermon->view_count }}
                    </span>
                @endif
            </div>
        </a>
        @empty
        <div class="p-8 text-center">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Немає проповідей</p>
        </div>
        @endforelse
    </div>
</div>
