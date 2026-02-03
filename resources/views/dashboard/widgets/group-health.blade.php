{{-- Group Health Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Здоров'я груп</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Активність та відвідуваність</p>
            </div>
        </div>
    </div>

    @if(count($groupHealth) > 0)
        <div class="divide-y divide-gray-50 dark:divide-gray-700">
            @foreach($groupHealth as $group)
                @php
                    // Determine health color based on last attendance and trend
                    $lastAttendance = $group['last_attendance_date'] ? \Carbon\Carbon::parse($group['last_attendance_date']) : null;
                    $daysSinceAttendance = $lastAttendance ? $lastAttendance->diffInDays(now()) : null;

                    if ($daysSinceAttendance === null || $daysSinceAttendance > 21) {
                        $healthColor = 'red';
                    } elseif ($daysSinceAttendance > 14 || $group['attendance_trend'] === 'down') {
                        $healthColor = 'yellow';
                    } else {
                        $healthColor = 'green';
                    }

                    $dotColors = [
                        'green' => 'bg-green-500',
                        'yellow' => 'bg-yellow-500',
                        'red' => 'bg-red-500',
                    ];

                    $trendIcons = [
                        'up' => ['icon' => 'M5 10l7-7m0 0l7 7m-7-7v18', 'color' => 'text-green-500 dark:text-green-400'],
                        'down' => ['icon' => 'M19 14l-7 7m0 0l-7-7m7 7V3', 'color' => 'text-red-500 dark:text-red-400'],
                        'stable' => ['icon' => 'M5 12h14', 'color' => 'text-gray-400 dark:text-gray-500'],
                    ];
                    $trend = $trendIcons[$group['attendance_trend']] ?? $trendIcons['stable'];
                @endphp
                <a href="{{ route('groups.show', $group['id']) }}" class="flex items-center gap-3 px-4 lg:px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    {{-- Health indicator dot --}}
                    <span class="w-2.5 h-2.5 rounded-full {{ $dotColors[$healthColor] }} flex-shrink-0"></span>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $group['name'] }}</p>
                            {{-- Trend arrow --}}
                            <svg class="w-3.5 h-3.5 {{ $trend['color'] }} flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $trend['icon'] }}"/>
                            </svg>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            <span>{{ $group['members_count'] }} учасників</span>
                            @if($group['leader_name'])
                                <span class="text-gray-300 dark:text-gray-600">&middot;</span>
                                <span class="truncate">{{ $group['leader_name'] }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="text-right flex-shrink-0">
                        @if($group['avg_attendance'])
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $group['avg_attendance'] }}%</p>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500">сер. відв.</p>
                        @endif
                        @if($lastAttendance)
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">{{ $lastAttendance->diffForHumans() }}</p>
                        @else
                            <p class="text-[10px] text-red-400 dark:text-red-500">Немає даних</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Legend --}}
        <div class="px-4 lg:px-5 py-3 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-4 text-[10px] text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span>Здорова</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                    <span>Потребує уваги</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    <span>Критично</span>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-8 px-4">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Немає активних груп</p>
        </div>
    @endif
</div>
