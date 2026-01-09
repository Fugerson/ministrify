@extends('layouts.system-admin')

@section('title', 'Журнал дій')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <form method="GET" class="flex flex-wrap gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Пошук..."
                   class="flex-1 min-w-64 px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">

            <select name="church_id"
                    class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">Всі церкви</option>
                @foreach($churches as $church)
                <option value="{{ $church->id }}" {{ request('church_id') == $church->id ? 'selected' : '' }}>
                    {{ $church->name }}
                </option>
                @endforeach
            </select>

            <button type="submit" class="px-6 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-900 dark:text-white rounded-lg">Фільтрувати</button>
            <a href="{{ route('system.audit-logs') }}" class="px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-lg">Скинути</a>
        </form>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $logs->where('action', 'created')->count() }}</p>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Створено</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $logs->where('action', 'updated')->count() }}</p>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Оновлено</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $logs->where('action', 'deleted')->count() }}</p>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Видалено</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $logs->total() }}</p>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Всього</p>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Час</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Користувач</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дія</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Церква</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                            <div>{{ $log->created_at->format('d.m.Y') }}</div>
                            <div class="text-xs text-gray-400 dark:text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($log->user)
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center mr-2 flex-shrink-0">
                                    <span class="text-xs font-medium text-gray-900 dark:text-white">{{ mb_substr($log->user->name, 0, 1) }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-900 dark:text-white truncate">{{ $log->user->name }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $log->user->email }}</p>
                                </div>
                            </div>
                            @else
                            <span class="text-gray-400 dark:text-gray-500">System</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 text-xs rounded-full whitespace-nowrap
                                    {{ $log->action === 'created' ? 'bg-green-100 dark:bg-green-600/20 text-green-700 dark:text-green-400' : '' }}
                                    {{ $log->action === 'updated' ? 'bg-blue-100 dark:bg-blue-600/20 text-blue-700 dark:text-blue-400' : '' }}
                                    {{ $log->action === 'deleted' ? 'bg-red-100 dark:bg-red-600/20 text-red-700 dark:text-red-400' : '' }}
                                    {{ in_array($log->action, ['login', 'logout', 'impersonate', 'stop_impersonate']) ? 'bg-purple-100 dark:bg-purple-600/20 text-purple-700 dark:text-purple-400' : '' }}
                                    {{ !in_array($log->action, ['created', 'updated', 'deleted', 'login', 'logout', 'impersonate', 'stop_impersonate']) ? 'bg-gray-100 dark:bg-gray-600/20 text-gray-700 dark:text-gray-400' : '' }}
                                ">{{ $log->action_label }}</span>
                            </div>
                            <p class="text-sm text-gray-900 dark:text-white mt-1">
                                {{ $log->description }}
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            @if($log->church)
                            <a href="{{ route('system.churches.show', $log->church) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-sm">
                                {{ $log->church->name }}
                            </a>
                            @else
                            <span class="text-gray-400 dark:text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-400 dark:text-gray-500 whitespace-nowrap">
                            {{ $log->ip_address ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Записів не знайдено</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
