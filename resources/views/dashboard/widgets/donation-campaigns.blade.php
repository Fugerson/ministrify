{{-- Donation Campaigns Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Збори коштів
        </h2>
        <a href="{{ route('donations.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1">
            Всі
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    @if($donationCampaigns->where('is_active', true)->count() > 0)
    <div class="p-4 lg:p-5 space-y-5">
        @foreach($donationCampaigns->where('is_active', true) as $campaign)
        <div>
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $campaign->name }}</h4>
                <span class="text-sm font-semibold text-green-600 dark:text-green-400 flex-shrink-0 ml-2">
                    {{ $campaign->progress_percent }}%
                </span>
            </div>
            <div class="h-2.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden mb-2">
                <div class="h-full rounded-full transition-all duration-500
                    {{ $campaign->progress_percent >= 100 ? 'bg-green-500' : ($campaign->progress_percent >= 75 ? 'bg-emerald-500' : ($campaign->progress_percent >= 50 ? 'bg-blue-500' : 'bg-indigo-500')) }}"
                     style="width: {{ min(100, $campaign->progress_percent) }}%"></div>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>
                    {{ number_format($campaign->collected_amount, 0, ',', ' ') }} / {{ number_format($campaign->goal_amount, 0, ',', ' ') }} &#8372;
                </span>
                @if($campaign->end_date)
                    @php
                        $daysRemaining = now()->diffInDays($campaign->end_date, false);
                    @endphp
                    @if($daysRemaining > 0)
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $daysRemaining }} {{ trans_choice('{1} день|[2,4] дні|[5,*] днів', $daysRemaining) }} залишилось
                        </span>
                    @elseif($daysRemaining == 0)
                        <span class="text-amber-500 font-medium">Останній день</span>
                    @else
                        <span class="text-red-500 font-medium">Завершено</span>
                    @endif
                @endif
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
        <p class="text-gray-500 dark:text-gray-400 text-sm">Немає активних зборів</p>
    </div>
    @endif
</div>
