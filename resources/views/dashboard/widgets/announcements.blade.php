{{-- Announcements Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
            Оголошення
        </h2>
        <a href="{{ route('announcements.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1">
            Всі
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <div class="divide-y divide-gray-50 dark:divide-gray-700">
        @forelse($announcements->take(5) as $announcement)
        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="flex items-start gap-3">
                @if($announcement->is_pinned)
                    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.828 3.414a2 2 0 012.828 0l1.93 1.93a2 2 0 010 2.828l-4.243 4.243a1 1 0 01-.707.293H7.414a1 1 0 01-.707-.293L5.05 10.757a2 2 0 010-2.828L9.828 3.414zM10 18a1 1 0 01-1-1v-3h2v3a1 1 0 01-1 1z"/>
                        </svg>
                    </div>
                @else
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h4 class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ $announcement->title }}</h4>
                        @if($announcement->is_pinned)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex-shrink-0">
                                Закріплено
                            </span>
                        @endif
                    </div>
                    @if($announcement->content)
                        <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-2">{{ Str::limit(strip_tags($announcement->content), 120) }}</p>
                    @endif
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        @if($announcement->author)
                            <span>{{ $announcement->author->name }}</span>
                            <span>&bull;</span>
                        @endif
                        <span>{{ ($announcement->published_at ?? $announcement->created_at)->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="p-8 text-center">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Немає оголошень</p>
        </div>
        @endforelse
    </div>
</div>
