{{-- Stats Grid Widget - 4 KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
    <!-- People Stats -->
    <a href="{{ route('people.index') }}" id="stat-people" class="stagger-item card-hover bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5 hover:border-blue-200 dark:hover:border-blue-800 transition-all group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-blue-50 dark:bg-blue-900/50 flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5 lg:w-6 lg:h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                </svg>
            </div>
            <div class="flex items-center gap-2">
                @if($stats['people_trend'] > 0)
                <span class="text-xs font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/50 px-2 py-1 rounded-lg flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    +{{ $stats['people_trend'] }}
                </span>
                @elseif($stats['people_trend'] < 0)
                <span class="text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/50 px-2 py-1 rounded-lg flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    {{ $stats['people_trend'] }}
                </span>
                @endif
                <span class="text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/50 px-2 py-1 rounded-lg">{{ __('Люди') }}</span>
            </div>
        </div>
        <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total_people'] }}</p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">{{ __('за 3 місяці') }}</p>
        <div class="mt-2 space-y-1.5">
            @if($stats['age_stats']['children'] > 0)
            <div class="flex items-center justify-between text-xs">
                <span class="text-amber-600 dark:text-amber-400">{{ __('Діти (0-12)') }}</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $stats['age_stats']['children'] }}</span>
            </div>
            @endif
            @if($stats['age_stats']['teens'] > 0)
            <div class="flex items-center justify-between text-xs">
                <span class="text-purple-600 dark:text-purple-400">{{ __('Підлітки (13-17)') }}</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $stats['age_stats']['teens'] }}</span>
            </div>
            @endif
            @if($stats['age_stats']['youth'] > 0)
            <div class="flex items-center justify-between text-xs">
                <span class="text-blue-600 dark:text-blue-400">{{ __('Молодь (18-35)') }}</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $stats['age_stats']['youth'] }}</span>
            </div>
            @endif
            @if($stats['age_stats']['adults'] > 0)
            <div class="flex items-center justify-between text-xs">
                <span class="text-green-600 dark:text-green-400">{{ __('Дорослі (36-59)') }}</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $stats['age_stats']['adults'] }}</span>
            </div>
            @endif
            @if($stats['age_stats']['seniors'] > 0)
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600 dark:text-gray-400">{{ __('Старші (60+)') }}</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $stats['age_stats']['seniors'] }}</span>
            </div>
            @endif
        </div>
    </a>

    <!-- Ministries Stats -->
    <a href="{{ route('ministries.index') }}" id="stat-ministries" class="stagger-item card-hover bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5 hover:border-green-200 dark:hover:border-green-800 transition-all group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-green-50 dark:bg-green-900/50 flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5 lg:w-6 lg:h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="flex items-center gap-2">
                @if($stats['volunteers_trend'] > 0)
                <span class="text-xs font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/50 px-2 py-1 rounded-lg flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    +{{ $stats['volunteers_trend'] }}
                </span>
                @elseif($stats['volunteers_trend'] < 0)
                <span class="text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/50 px-2 py-1 rounded-lg flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    {{ $stats['volunteers_trend'] }}
                </span>
                @endif
                <span class="text-xs font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/50 px-2 py-1 rounded-lg">{{ __('Служіння') }}</span>
            </div>
        </div>
        <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['volunteers_count'] }}</p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">{{ __('служителів') }}</p>
        <div class="mt-2 space-y-1.5 max-h-32 overflow-y-auto">
            @foreach($stats['ministries_list'] as $ministry)
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600 dark:text-gray-400 truncate mr-2">{{ $ministry->name }}</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300 flex-shrink-0">{{ $ministry->members_count }}</span>
            </div>
            @endforeach
        </div>
    </a>

    <!-- Groups Stats -->
    <a href="{{ route('groups.index') }}" id="stat-groups" class="stagger-item card-hover bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5 hover:border-purple-200 dark:hover:border-purple-800 transition-all group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-purple-50 dark:bg-purple-900/50 flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5 lg:w-6 lg:h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <span class="text-xs font-medium text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/50 px-2 py-1 rounded-lg">Групи</span>
        </div>
        <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total_groups'] ?? 0 }}</p>
        <div class="mt-3 space-y-1.5">
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span class="text-gray-500 dark:text-gray-400">Активних</span>
                </div>
                <span class="font-semibold text-green-600 dark:text-green-400">{{ $stats['active_groups'] }}</span>
            </div>
            @if($stats['paused_groups'] > 0)
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                    <span class="text-gray-500 dark:text-gray-400">На паузі</span>
                </div>
                <span class="font-semibold text-yellow-600 dark:text-yellow-400">{{ $stats['paused_groups'] }}</span>
            </div>
            @endif
            @if($stats['vacation_groups'] > 0)
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span class="text-gray-500 dark:text-gray-400">У відпустці</span>
                </div>
                <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $stats['vacation_groups'] }}</span>
            </div>
            @endif
            <div class="flex items-center justify-between text-xs pt-1 border-t border-gray-200 dark:border-gray-700">
                <span class="text-gray-500 dark:text-gray-400">Учасників</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $stats['total_group_members'] }}</span>
            </div>
        </div>
    </a>

    <!-- Events Stats -->
    <a href="{{ route('schedule') }}" id="stat-events" class="stagger-item card-hover bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5 hover:border-amber-200 dark:hover:border-amber-800 transition-all group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-amber-50 dark:bg-amber-900/50 flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5 lg:w-6 lg:h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <span class="text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/50 px-2 py-1 rounded-lg">{{ now()->locale('uk')->translatedFormat('F') }}</span>
        </div>
        <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['events_this_month'] }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1 mb-2">подій цього місяця</p>
        <div class="space-y-1.5">
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-500 dark:text-gray-400">Проведено</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $stats['past_events'] }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-500 dark:text-gray-400">Заплановано</span>
                <span class="font-semibold text-amber-600 dark:text-amber-400">{{ $stats['upcoming_events'] }}</span>
            </div>
        </div>
    </a>
</div>
