{{-- Expenses Breakdown Widget (Admin Only) --}}
@if(isset($stats['expenses_this_month']))
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-900 dark:text-white">Витрати за місяць</h2>
        <a href="{{ route('finances.expenses.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Всі</a>
    </div>

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
</div>
@endif
