{{-- Ministry Budgets Widget (Admin Only) --}}
@if(count($ministryBudgets) > 0)
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900 dark:text-white">Бюджети команд</h2>
        <a href="{{ route('finances.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Звіт</a>
    </div>
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
</div>
@endif
