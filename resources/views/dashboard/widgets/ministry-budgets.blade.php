{{-- Ministry Budgets Widget (Admin Only) --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900 dark:text-white">Бюджети команд</h2>
        <a href="{{ route('finances.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Звіт</a>
    </div>
    @if(count($ministryBudgets) > 0)
    <div class="p-4 lg:p-5 space-y-4">
        @foreach($ministryBudgets as $budget)
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $budget['icon'] }} {{ $budget['name'] }}</span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ number_format($budget['spent'], 0, ',', ' ') }} / {{ number_format($budget['budget'], 0, ',', ' ') }} ₴
                </span>
            </div>
            <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500
                    {{ $budget['percentage'] > 90 ? 'bg-red-500' : ($budget['percentage'] > 70 ? 'bg-amber-500' : 'bg-green-500') }}"
                     style="width: {{ min(100, $budget['percentage']) }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="p-8 text-center">
        <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Немає бюджетів у служінь</p>
    </div>
    @endif
</div>
