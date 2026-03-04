{{-- Membership Funnel Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900 dark:text-white">{{ __('app.membership_funnel') }}</h2>
        <span class="text-xs font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/50 px-2 py-0.5 rounded-lg">
            {{ $membershipStats['total'] }} {{ __('app.total_lowercase') }}
        </span>
    </div>
    <div class="p-4 lg:p-5">
        @php
            $total = max($membershipStats['total'], 1);
            $levels = [
                ['key' => 'guest', 'label' => __('app.guest'), 'color' => 'from-gray-300 to-gray-400', 'bg' => 'bg-gray-400', 'light' => 'bg-gray-100 dark:bg-gray-900/50', 'text' => 'text-gray-600 dark:text-gray-400'],
                ['key' => 'newcomer', 'label' => __('app.newcomer'), 'color' => 'from-amber-400 to-amber-500', 'bg' => 'bg-amber-500', 'light' => 'bg-amber-100 dark:bg-amber-900/50', 'text' => 'text-amber-600 dark:text-amber-400'],
                ['key' => 'member', 'label' => __('app.church_member'), 'color' => 'from-blue-400 to-blue-500', 'bg' => 'bg-blue-500', 'light' => 'bg-blue-100 dark:bg-blue-900/50', 'text' => 'text-blue-600 dark:text-blue-400'],
                ['key' => 'servant', 'label' => __('app.servant'), 'color' => 'from-emerald-400 to-emerald-500', 'bg' => 'bg-emerald-500', 'light' => 'bg-emerald-100 dark:bg-emerald-900/50', 'text' => 'text-emerald-600 dark:text-emerald-400'],
                ['key' => 'leader', 'label' => __('app.leader'), 'color' => 'from-violet-400 to-violet-500', 'bg' => 'bg-violet-500', 'light' => 'bg-violet-100 dark:bg-violet-900/50', 'text' => 'text-violet-600 dark:text-violet-400'],
                ['key' => 'leadership', 'label' => __('app.leadership'), 'color' => 'from-red-400 to-red-500', 'bg' => 'bg-red-500', 'light' => 'bg-red-100 dark:bg-red-900/50', 'text' => 'text-red-600 dark:text-red-400'],
            ];
        @endphp

        {{-- Funnel visualization --}}
        <div class="flex flex-col items-center gap-1 mb-6">
            @foreach($levels as $index => $level)
            @php
                $count = $membershipStats[$level['key']] ?? 0;
                $percentage = round(($count / $total) * 100);
                // Funnel width: widest at top (100%), narrowing down
                $widthPercent = 100 - ($index * 13);
            @endphp
            <div class="relative group" style="width: {{ $widthPercent }}%">
                <div class="h-10 bg-gradient-to-r {{ $level['color'] }} rounded-lg flex items-center justify-between px-3 transition-all hover:opacity-90 cursor-default">
                    <span class="text-xs font-medium text-white truncate">{{ $level['label'] }}</span>
                    <span class="text-xs font-bold text-white">{{ $count }}</span>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Horizontal bar breakdown --}}
        <div class="space-y-3">
            @foreach($levels as $level)
            @php
                $count = $membershipStats[$level['key']] ?? 0;
                $percentage = round(($count / $total) * 100);
            @endphp
            <div>
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full {{ $level['bg'] }}"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $level['label'] }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $count }}</span>
                        <span class="text-xs text-gray-400 dark:text-gray-500 w-10 text-right">{{ $percentage }}%</span>
                    </div>
                </div>
                <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r {{ $level['color'] }} rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
