{{-- Giving Trends Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5">
    <div class="flex items-center gap-3 mb-5">
        <div class="w-10 h-10 rounded-xl bg-green-50 dark:bg-green-900/50 flex items-center justify-center">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white">Пожертви</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Тренд за 6 місяців</p>
        </div>
    </div>

    @if(count($givingTrends) > 0)
        @php
            $maxTotal = max(array_map(fn($m) => $m['total'], $givingTrends));
            $maxTotal = $maxTotal > 0 ? $maxTotal : 1;
        @endphp

        {{-- Chart area --}}
        <div class="flex items-end gap-2 h-44 mb-3">
            @foreach($givingTrends as $month)
                @php
                    $totalPercent = $maxTotal > 0 ? ($month['total'] / $maxTotal * 100) : 0;
                    $tithesPercent = $month['total'] > 0 ? ($month['tithes'] / $month['total'] * $totalPercent) : 0;
                    $offeringsPercent = $month['total'] > 0 ? ($month['offerings'] / $month['total'] * $totalPercent) : 0;
                    $donationsPercent = $month['total'] > 0 ? ($month['donations'] / $month['total'] * $totalPercent) : 0;
                @endphp
                <div class="flex-1 flex flex-col items-center gap-1">
                    {{-- Stacked bar --}}
                    <div class="w-full flex flex-col justify-end" style="height: 140px;">
                        <div class="w-full rounded-t-lg overflow-hidden flex flex-col-reverse" style="height: {{ max($totalPercent, $month['total'] > 0 ? 2 : 0) }}%;">
                            @if($month['tithes'] > 0)
                                <div class="w-full bg-emerald-500 dark:bg-emerald-400 transition-all duration-500" style="flex: {{ $month['tithes'] }} 0 0;"></div>
                            @endif
                            @if($month['offerings'] > 0)
                                <div class="w-full bg-blue-500 dark:bg-blue-400 transition-all duration-500" style="flex: {{ $month['offerings'] }} 0 0;"></div>
                            @endif
                            @if($month['donations'] > 0)
                                <div class="w-full bg-amber-500 dark:bg-amber-400 transition-all duration-500" style="flex: {{ $month['donations'] }} 0 0;"></div>
                            @endif
                        </div>
                    </div>
                    {{-- Month label --}}
                    <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400">{{ $month['month'] }}</span>
                </div>
            @endforeach
        </div>

        {{-- Totals row --}}
        <div class="flex items-center gap-2 mb-4">
            @foreach($givingTrends as $month)
                <div class="flex-1 text-center">
                    <span class="text-[10px] font-semibold text-gray-700 dark:text-gray-300">{{ number_format($month['total'], 0, ',', ' ') }}</span>
                </div>
            @endforeach
        </div>

        {{-- Legend --}}
        <div class="flex items-center justify-center gap-4 pt-3 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-emerald-500 dark:bg-emerald-400"></span>
                <span class="text-xs text-gray-600 dark:text-gray-400">Десятини</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-blue-500 dark:bg-blue-400"></span>
                <span class="text-xs text-gray-600 dark:text-gray-400">Пожертви</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-amber-500 dark:bg-amber-400"></span>
                <span class="text-xs text-gray-600 dark:text-gray-400">Дарування</span>
            </div>
        </div>
    @else
        <div class="text-center py-8">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Немає даних про пожертви</p>
        </div>
    @endif
</div>
