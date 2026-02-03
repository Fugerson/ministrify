{{-- Online Donations Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Онлайн пожертви</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Цього місяця</p>
            </div>
        </div>
    </div>

    @if(!isset($onlineDonations) || (($onlineDonations['total_this_month'] ?? 0) == 0 && ($onlineDonations['total_last_month'] ?? 0) == 0 && empty($onlineDonations['recent'])))
        <div class="text-center py-8">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Онлайн пожертви не налаштовані</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Підключіть платіжну систему для прийому онлайн пожертв</p>
        </div>
    @else
        {{-- Total this month with change indicator --}}
        <div class="mb-4">
            <div class="flex items-end gap-3">
                <p class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($onlineDonations['total_this_month'] ?? 0, 0, ',', ' ') }}
                    <span class="text-lg font-normal text-gray-400">{{ $onlineDonations['recent']->first()->currency ?? '₴' }}</span>
                </p>
                @if(isset($onlineDonations['change_percent']) && $onlineDonations['change_percent'] != 0)
                    <span class="mb-1 text-sm font-medium px-2 py-0.5 rounded-lg flex items-center gap-1
                        {{ $onlineDonations['change_percent'] > 0 ? 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/50' : 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/50' }}">
                        @if($onlineDonations['change_percent'] > 0)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            +{{ number_format($onlineDonations['change_percent'], 1) }}%
                        @else
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                            {{ number_format($onlineDonations['change_percent'], 1) }}%
                        @endif
                    </span>
                @endif
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                Минулого місяця: {{ number_format($onlineDonations['total_last_month'] ?? 0, 0, ',', ' ') }}
            </p>
        </div>

        {{-- Stat cards --}}
        <div class="grid grid-cols-3 gap-2 mb-4">
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-2.5 text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($onlineDonations['avg_donation'] ?? 0, 0, ',', ' ') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Середня</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-2.5 text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $onlineDonations['recurring_count'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Регулярних</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-2.5 text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $onlineDonations['count_this_month'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Транзакцій</p>
            </div>
        </div>

        {{-- Recurring amount info --}}
        @if(($onlineDonations['recurring_count'] ?? 0) > 0)
            <div class="flex items-center gap-2 px-3 py-2 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl mb-4">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <p class="text-xs text-emerald-700 dark:text-emerald-300">
                    Регулярних пожертв: <span class="font-semibold">{{ number_format($onlineDonations['recurring_amount'] ?? 0, 0, ',', ' ') }}/міс</span>
                </p>
            </div>
        @endif

        {{-- Recent donations --}}
        @if(!empty($onlineDonations['recent']) && $onlineDonations['recent']->isNotEmpty())
            <div class="border-t border-gray-100 dark:border-gray-700 pt-3">
                <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Останні пожертви</h4>
                <div class="space-y-2">
                    @foreach($onlineDonations['recent']->take(5) as $donation)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                    {{ $donation->is_anonymous ? 'bg-gray-100 dark:bg-gray-700' : 'bg-emerald-50 dark:bg-emerald-900/50' }}">
                                    @if($donation->is_anonymous)
                                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    @else
                                        <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                            {{ mb_substr($donation->donor_name, 0, 1) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $donation->is_anonymous ? 'Анонімно' : $donation->donor_name }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ \Carbon\Carbon::parse($donation->paid_at)->locale('uk')->diffForHumans() }}
                                        @if($donation->provider)
                                            <span class="text-gray-300 dark:text-gray-600 mx-0.5">&middot;</span>
                                            {{ $donation->provider }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white flex-shrink-0 ml-2">
                                {{ number_format($donation->amount, 0, ',', ' ') }} {{ $donation->currency ?? '₴' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>
