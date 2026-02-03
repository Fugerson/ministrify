{{-- Need Attention Widget (Admin Only) --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            <h2 class="font-semibold text-gray-900 dark:text-white">Потребують уваги</h2>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Не відвідували 3+ тижні</p>
    </div>
    @if(count($needAttention) > 0)
    <div class="divide-y divide-gray-50 dark:divide-gray-700">
        @foreach($needAttention as $person)
        <div class="flex items-center justify-between p-4">
            <a href="{{ route('people.show', $person) }}" class="flex items-center gap-3 hover:opacity-80">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-600 dark:to-gray-700 flex items-center justify-center">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ mb_substr($person->first_name, 0, 1) }}</span>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $person->full_name }}</p>
                    @if($person->phone)
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $person->phone }}</p>
                    @endif
                </div>
            </a>
            @if($person->phone)
            <a href="tel:{{ $person->phone }}" class="w-9 h-9 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 rounded-lg flex items-center justify-center hover:bg-green-200 dark:hover:bg-green-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </a>
            @endif
        </div>
        @endforeach
    </div>
    @else
    <div class="p-8 text-center">
        <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/50 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Всі відвідують регулярно</p>
    </div>
    @endif
</div>
