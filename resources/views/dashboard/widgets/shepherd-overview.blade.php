{{-- Shepherd Overview Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Пастирство</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Розподіл опіки</p>
            </div>
        </div>
    </div>

    @if($shepherdData['total_shepherds'] > 0)
        {{-- Stats summary --}}
        <div class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-gray-700 border-b border-gray-100 dark:border-gray-700">
            <div class="px-4 py-3 text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $shepherdData['total_shepherds'] }}</p>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Пастирів</p>
            </div>
            <div class="px-4 py-3 text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $shepherdData['total_sheep'] }}</p>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Під опікою</p>
            </div>
            <div class="px-4 py-3 text-center">
                <p class="text-lg font-bold {{ $shepherdData['unassigned_count'] > 10 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white' }}">{{ $shepherdData['unassigned_count'] }}</p>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Без опіки</p>
            </div>
        </div>

        {{-- Warning for many unassigned --}}
        @if($shepherdData['unassigned_count'] > 10)
            <div class="mx-4 lg:mx-5 mt-3 px-3 py-2 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-xl">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <p class="text-xs text-amber-700 dark:text-amber-300">
                        <span class="font-semibold">{{ $shepherdData['unassigned_count'] }}</span> людей без пастиря.
                        <a href="{{ route('people.index', ['filter' => 'unassigned']) }}" class="underline hover:no-underline font-medium">Переглянути</a>
                    </p>
                </div>
            </div>
        @endif

        {{-- Top shepherds list --}}
        @if($shepherdData['shepherds']->count() > 0)
            <div class="divide-y divide-gray-50 dark:divide-gray-700 mt-1">
                @foreach($shepherdData['shepherds'] as $shepherd)
                    <a href="{{ route('people.show', $shepherd['id']) }}" class="flex items-center gap-3 px-4 lg:px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-teal-900 dark:to-emerald-900 flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-medium text-teal-600 dark:text-teal-400">{{ mb_substr($shepherd['first_name'], 0, 1) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $shepherd['full_name'] }}</p>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $shepherd['sheep_count'] }}</span>
                            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    @else
        <div class="text-center py-8 px-4">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                </svg>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Пастирство не налаштовано</p>
            <p class="text-xs text-gray-400 dark:text-gray-500">Призначте пастирів для опіки над учасниками</p>
        </div>
    @endif
</div>
