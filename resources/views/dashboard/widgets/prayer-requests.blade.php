{{-- Prayer Requests Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            Молитовні прохання
        </h2>
        <a href="{{ route('website-builder.prayer-inbox.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1">
            Всі
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <div class="divide-y divide-gray-50 dark:divide-gray-700">
        @forelse($prayerRequests->take(5) as $request)
        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $request->is_urgent ? 'bg-red-100 dark:bg-red-900/50' : 'bg-indigo-100 dark:bg-indigo-900/50' }}">
                    @if($request->is_urgent)
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h4 class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ $request->title }}</h4>
                        @if($request->is_urgent)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400 flex-shrink-0">
                                Термiново
                            </span>
                        @endif
                    </div>
                    @if($request->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-2">{{ $request->description }}</p>
                    @endif
                    <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                        @if(!$request->is_anonymous && $request->person)
                            <span>{{ $request->person->full_name }}</span>
                            <span>&bull;</span>
                        @endif
                        <span>{{ $request->created_at->diffForHumans() }}</span>
                        @if($request->prayer_count > 0)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 font-medium">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                                </svg>
                                {{ $request->prayer_count }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="p-8 text-center">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Немає активних молитовних прохань</p>
        </div>
        @endforelse
    </div>
</div>
