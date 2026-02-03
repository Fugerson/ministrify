{{-- Financial Summary Widget (Admin Only) --}}
@if(isset($stats['income_this_month']) || isset($stats['expenses_this_month']))
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
    @if(isset($stats['income_this_month']))
    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 rounded-2xl border border-green-100 dark:border-green-800 p-4 lg:p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['income_this_month'], 0, ',', ' ') }} ₴</p>
        <p class="text-xs lg:text-sm text-green-600 dark:text-green-400 mt-0.5">Доходи за місяць</p>
    </div>
    @endif

    @if(isset($stats['expenses_this_month']))
    <div class="bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/30 dark:to-rose-900/30 rounded-2xl border border-red-100 dark:border-red-800 p-4 lg:p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['expenses_this_month'], 0, ',', ' ') }} ₴</p>
        <p class="text-xs lg:text-sm text-red-600 dark:text-red-400 mt-0.5">Витрати за місяць</p>
    </div>
    @endif

    @if(isset($stats['income_this_month']) && isset($stats['expenses_this_month']))
    @php $balance = $stats['income_this_month'] - $stats['expenses_this_month']; @endphp
    <div class="bg-gradient-to-br {{ $balance >= 0 ? 'from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 border-blue-100 dark:border-blue-800' : 'from-amber-50 to-orange-50 dark:from-amber-900/30 dark:to-orange-900/30 border-amber-100 dark:border-amber-800' }} rounded-2xl border p-4 lg:p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl {{ $balance >= 0 ? 'bg-blue-100 dark:bg-blue-900' : 'bg-amber-100 dark:bg-amber-900' }} flex items-center justify-center">
                <svg class="w-5 h-5 {{ $balance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ $balance >= 0 ? '+' : '' }}{{ number_format($balance, 0, ',', ' ') }} ₴</p>
        <p class="text-xs lg:text-sm {{ $balance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400' }} mt-0.5">Баланс за місяць</p>
    </div>
    @endif

    @if(count($growthData) > 0)
    @php $lastMonth = end($growthData); @endphp
    <div class="bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/30 dark:to-violet-900/30 rounded-2xl border border-purple-100 dark:border-purple-800 p-4 lg:p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">+{{ $lastMonth['count'] }}</p>
        <p class="text-xs lg:text-sm text-purple-600 dark:text-purple-400 mt-0.5">Нових за місяць</p>
    </div>
    @endif
</div>
@else
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
    <div class="text-center">
        <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Немає фінансових даних за місяць</p>
    </div>
</div>
@endif
