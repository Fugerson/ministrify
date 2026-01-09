@extends('layouts.system-admin')

@section('title', 'System Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Церков</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['churches'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-600/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Користувачів</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['users'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-600/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Людей</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['people'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-600/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Подій</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['events'] }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 dark:bg-amber-600/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6">
            <p class="text-green-100 text-sm">Загальні надходження</p>
            <p class="text-3xl font-bold text-white mt-2">{{ number_format($finances['total_income'], 0, ',', ' ') }} ₴</p>
        </div>
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-6">
            <p class="text-red-100 text-sm">Загальні витрати</p>
            <p class="text-3xl font-bold text-white mt-2">{{ number_format($finances['total_expenses'], 0, ',', ' ') }} ₴</p>
        </div>
    </div>

    <!-- Growth Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Зростання за 6 місяців</h2>
        <canvas id="growthChart" height="100"></canvas>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Churches -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Останні церкви</h2>
                <a href="{{ route('system.churches.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-sm">Всі →</a>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($recentChurches as $church)
                <a href="{{ route('system.churches.show', $church) }}" class="flex items-center px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-600/20 flex items-center justify-center mr-4">
                        <span class="text-lg">⛪</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ $church->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $church->city }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $church->users_count }} користувачів</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $church->people_count }} людей</p>
                    </div>
                </a>
                @empty
                <p class="px-6 py-4 text-gray-500 dark:text-gray-400">Немає церков</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Users -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Останні користувачі</h2>
                <a href="{{ route('system.users.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-sm">Всі →</a>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($recentUsers->take(5) as $user)
                <div class="flex items-center px-6 py-3">
                    <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center mr-3">
                        <span class="text-sm font-medium text-gray-700 dark:text-white">{{ mb_substr($user->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($user->is_super_admin)
                        <span class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 text-xs rounded-full">Super</span>
                        @endif
                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs rounded-full">{{ $user->role }}</span>
                    </div>
                </div>
                @empty
                <p class="px-6 py-4 text-gray-500 dark:text-gray-400">Немає користувачів</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Audit Logs -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900 dark:text-white">Останні дії в системі</h2>
            <a href="{{ route('system.audit-logs') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-sm">Всі →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Час</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Користувач</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Церква</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дія</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Модель</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($recentLogs->take(10) as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $log->created_at->format('d.m H:i') }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900 dark:text-white">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $log->church?->name ?? '-' }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-xs rounded-full
                                {{ $log->action === 'created' ? 'bg-green-100 dark:bg-green-600/20 text-green-700 dark:text-green-400' : '' }}
                                {{ $log->action === 'updated' ? 'bg-blue-100 dark:bg-blue-600/20 text-blue-700 dark:text-blue-400' : '' }}
                                {{ $log->action === 'deleted' ? 'bg-red-100 dark:bg-red-600/20 text-red-700 dark:text-red-400' : '' }}
                            ">{{ $log->action }}</span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ class_basename($log->model_type) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Немає записів</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
const ctx = document.getElementById('growthChart').getContext('2d');
const isDark = document.documentElement.classList.contains('dark');
const gridColor = isDark ? 'rgba(75, 85, 99, 0.3)' : 'rgba(209, 213, 219, 0.5)';
const textColor = isDark ? '#9ca3af' : '#6b7280';

new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($monthlyGrowth, 'month')) !!},
        datasets: [
            {
                label: 'Церкви',
                data: {!! json_encode(array_column($monthlyGrowth, 'churches')) !!},
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.3,
                fill: true
            },
            {
                label: 'Користувачі',
                data: {!! json_encode(array_column($monthlyGrowth, 'users')) !!},
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.3,
                fill: true
            },
            {
                label: 'Люди',
                data: {!! json_encode(array_column($monthlyGrowth, 'people')) !!},
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.3,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: { color: textColor }
            }
        },
        scales: {
            x: {
                ticks: { color: textColor },
                grid: { color: gridColor }
            },
            y: {
                ticks: { color: textColor },
                grid: { color: gridColor }
            }
        }
    }
});
</script>
@endpush
@endsection
