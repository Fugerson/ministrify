{{-- Membership Funnel Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900 dark:text-white">Воронка членства</h2>
        <span class="text-xs font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/50 px-2 py-0.5 rounded-lg">
            {{ $membershipStats['total'] }} всього
        </span>
    </div>
    <div class="p-4 lg:p-5">
        @php
            $total = max($membershipStats['total'], 1);
            $levels = [
                ['key' => 'guest', 'label' => 'Гості', 'color' => 'from-blue-400 to-blue-500', 'bg' => 'bg-blue-500', 'light' => 'bg-blue-100 dark:bg-blue-900/50', 'text' => 'text-blue-600 dark:text-blue-400'],
                ['key' => 'regular', 'label' => 'Постійні відвідувачі', 'color' => 'from-cyan-400 to-cyan-500', 'bg' => 'bg-cyan-500', 'light' => 'bg-cyan-100 dark:bg-cyan-900/50', 'text' => 'text-cyan-600 dark:text-cyan-400'],
                ['key' => 'member', 'label' => 'Члени церкви', 'color' => 'from-teal-400 to-teal-500', 'bg' => 'bg-teal-500', 'light' => 'bg-teal-100 dark:bg-teal-900/50', 'text' => 'text-teal-600 dark:text-teal-400'],
                ['key' => 'active_member', 'label' => 'Активні члени', 'color' => 'from-emerald-400 to-emerald-500', 'bg' => 'bg-emerald-500', 'light' => 'bg-emerald-100 dark:bg-emerald-900/50', 'text' => 'text-emerald-600 dark:text-emerald-400'],
                ['key' => 'leader', 'label' => 'Лідери', 'color' => 'from-green-500 to-green-600', 'bg' => 'bg-green-600', 'light' => 'bg-green-100 dark:bg-green-900/50', 'text' => 'text-green-600 dark:text-green-400'],
            ];
        @endphp

        {{-- Funnel visualization --}}
        <div class="flex flex-col items-center gap-1 mb-6">
            @foreach($levels as $index => $level)
            @php
                $count = $membershipStats[$level['key']] ?? 0;
                $percentage = round(($count / $total) * 100);
                // Funnel width: widest at top (100%), narrowing down
                $widthPercent = 100 - ($index * 16);
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
