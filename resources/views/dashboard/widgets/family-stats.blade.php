{{-- Family Stats Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/50 flex items-center justify-center">
            <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
        </div>
        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('app.families') }}</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.family_connections_in_db') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        {{-- Married couples --}}
        <div class="bg-pink-50 dark:bg-pink-900/30 rounded-xl p-3 text-center">
            <p class="text-2xl font-bold text-pink-700 dark:text-pink-300">{{ $familyStats['married_couples'] ?? 0 }}</p>
            <p class="text-xs text-pink-600 dark:text-pink-400 mt-0.5">{{ __('app.married_couples') }}</p>
        </div>

        {{-- Children --}}
        <div class="bg-amber-50 dark:bg-amber-900/30 rounded-xl p-3 text-center">
            <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ $familyStats['children_count'] ?? 0 }}</p>
            <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">{{ __('app.children_count') }}</p>
        </div>
    </div>
</div>
