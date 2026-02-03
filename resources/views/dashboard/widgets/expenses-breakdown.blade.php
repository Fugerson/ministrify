{{-- Expenses Breakdown Widget (Admin Only) --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-900 dark:text-white">Витрати за місяць</h2>
        <a href="{{ route('finances.expenses.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Всі</a>
    </div>

    @if(isset($stats['expenses_this_month']))
    <!-- Total -->
    <div class="text-center pb-4 mb-4 border-b border-gray-200 dark:border-gray-700">
        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['expenses_this_month'], 0, ',', ' ') }} ₴</p>
    </div>

    <!-- Breakdown by category -->
    @if($expensesByCategory->isNotEmpty())
    <div class="space-y-3">
        @foreach($expensesByCategory as $category)
        @php
            $percentage = $stats['expenses_this_month'] > 0 ? ($category['amount'] / $stats['expenses_this_month']) * 100 : 0;
        @endphp
        <div>
            <div class="flex items-center justify-between text-sm mb-1">
                <span class="text-gray-700 dark:text-gray-300">{{ $category['name'] }}</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ number_format($category['amount'], 0, ',', ' ') }} ₴</span>
            </div>
            <div class="w-full h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-red-500 rounded-full" style="width: {{ $percentage }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">Немає витрат за цей місяць</p>
    @endif
    @else
    <div class="py-4 text-center">
        <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
        </div>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Немає даних про витрати</p>
    </div>
    @endif
</div>
