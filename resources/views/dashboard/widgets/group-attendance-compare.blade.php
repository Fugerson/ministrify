{{-- Group Attendance Comparison Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/50 flex items-center justify-center">
            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white">Відвідуваність груп</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Порівняння за останні 4 тижні</p>
        </div>
    </div>

    @if(!isset($groupAttendanceCompare) || $groupAttendanceCompare->isEmpty())
        <div class="text-center py-8">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Немає даних про відвідуваність груп</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Почніть відмічати присутність у групах</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($groupAttendanceCompare->sortByDesc('attendance_rate') as $group)
                @php
                    $rate = $group['attendance_rate'] ?? 0;
                    if ($rate >= 80) {
                        $rateColorClass = 'text-green-600 dark:text-green-400';
                        $barColorClass = 'bg-green-500 dark:bg-green-400';
                        $barBgClass = 'bg-green-100 dark:bg-green-900/30';
                    } elseif ($rate >= 50) {
                        $rateColorClass = 'text-yellow-600 dark:text-yellow-400';
                        $barColorClass = 'bg-yellow-500 dark:bg-yellow-400';
                        $barBgClass = 'bg-yellow-100 dark:bg-yellow-900/30';
                    } else {
                        $rateColorClass = 'text-red-600 dark:text-red-400';
                        $barColorClass = 'bg-red-500 dark:bg-red-400';
                        $barBgClass = 'bg-red-100 dark:bg-red-900/30';
                    }
                    $maxWeekly = max($group['last_4_weeks'] ?? [0]);
                @endphp
                <div>
                    {{-- Group name and rate --}}
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2 min-w-0">
                            @if(!empty($group['color']))
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $group['color'] }}"></span>
                            @endif
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $group['name'] }}</span>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            {{-- Sparkline: mini bars for last 4 weeks --}}
                            <div class="flex items-end gap-0.5 h-5">
                                @foreach(($group['last_4_weeks'] ?? [0, 0, 0, 0]) as $weekCount)
                                    @php
                                        $barHeight = $maxWeekly > 0 ? max(4, round(($weekCount / $maxWeekly) * 20)) : 4;
                                    @endphp
                                    <div class="w-1.5 rounded-sm {{ $barColorClass }}" style="height: {{ $barHeight }}px;" title="{{ $weekCount }}"></div>
                                @endforeach
                            </div>
                            <span class="text-sm font-bold {{ $rateColorClass }}">{{ number_format($rate, 0) }}%</span>
                        </div>
                    </div>

                    {{-- Attendance bar --}}
                    <div class="w-full h-2 rounded-full {{ $barBgClass }}">
                        <div class="h-2 rounded-full {{ $barColorClass }} transition-all duration-500" style="width: {{ min($rate, 100) }}%"></div>
                    </div>

                    {{-- Details --}}
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            Середня: {{ $group['avg_attendance'] ?? 0 }} з {{ $group['members_count'] ?? 0 }} учасників
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
