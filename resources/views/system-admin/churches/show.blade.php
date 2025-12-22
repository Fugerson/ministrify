@extends('layouts.system-admin')

@section('title', $church->name)

@section('actions')
<div class="flex items-center gap-3">
    <form method="POST" action="{{ route('system.churches.switch', $church) }}">
        @csrf
        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">
            Увійти в церкву
        </button>
    </form>
    <a href="{{ route('system.churches.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white font-medium rounded-lg">
        ← Назад
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Church Info -->
    <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700">
        <div class="flex items-start gap-6">
            @if($church->logo)
            <img src="/storage/{{ $church->logo }}" class="w-24 h-24 rounded-xl object-cover">
            @else
            <div class="w-24 h-24 rounded-xl bg-blue-600/20 flex items-center justify-center">
                <span class="text-4xl">⛪</span>
            </div>
            @endif
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-white">{{ $church->name }}</h2>
                <p class="text-gray-400 mt-1">{{ $church->city }}</p>
                @if($church->address)
                <p class="text-gray-500 text-sm mt-1">{{ $church->address }}</p>
                @endif
                <div class="flex items-center gap-4 mt-4">
                    <span class="px-3 py-1 bg-gray-700 text-gray-300 rounded-full text-sm">ID: {{ $church->id }}</span>
                    <span class="px-3 py-1 bg-gray-700 text-gray-300 rounded-full text-sm">Slug: {{ $church->slug }}</span>
                    @if($church->public_site_enabled)
                    <span class="px-3 py-1 bg-green-600/20 text-green-400 rounded-full text-sm">Публічний сайт</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
            <p class="text-2xl font-bold text-white">{{ $church->users_count }}</p>
            <p class="text-gray-400 text-sm">Користувачів</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
            <p class="text-2xl font-bold text-white">{{ $church->people_count }}</p>
            <p class="text-gray-400 text-sm">Людей</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
            <p class="text-2xl font-bold text-white">{{ $church->ministries_count }}</p>
            <p class="text-gray-400 text-sm">Служінь</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
            <p class="text-2xl font-bold text-white">{{ $church->groups_count }}</p>
            <p class="text-gray-400 text-sm">Груп</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
            <p class="text-2xl font-bold text-white">{{ $church->events_count }}</p>
            <p class="text-gray-400 text-sm">Подій</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
            <p class="text-2xl font-bold text-white">{{ $church->boards_count }}</p>
            <p class="text-gray-400 text-sm">Дошок</p>
        </div>
    </div>

    <!-- Finances -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-2xl p-6">
            <p class="text-green-100 text-sm">Надходження</p>
            <p class="text-3xl font-bold text-white mt-2">{{ number_format($finances['income'], 0, ',', ' ') }} ₴</p>
        </div>
        <div class="bg-gradient-to-br from-red-600 to-red-700 rounded-2xl p-6">
            <p class="text-red-100 text-sm">Витрати</p>
            <p class="text-3xl font-bold text-white mt-2">{{ number_format($finances['expenses'], 0, ',', ' ') }} ₴</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Users -->
        <div class="bg-gray-800 rounded-2xl border border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-700">
                <h2 class="font-semibold text-white">Користувачі церкви</h2>
            </div>
            <div class="divide-y divide-gray-700">
                @forelse($users as $user)
                <div class="flex items-center px-6 py-3">
                    <div class="w-10 h-10 rounded-full bg-gray-600 flex items-center justify-center mr-3">
                        <span class="text-sm font-medium text-white">{{ mb_substr($user->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-white truncate">{{ $user->name }}</p>
                        <p class="text-sm text-gray-400 truncate">{{ $user->email }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 text-xs rounded-full
                            {{ $user->role === 'admin' ? 'bg-red-600/20 text-red-400' : '' }}
                            {{ $user->role === 'leader' ? 'bg-blue-600/20 text-blue-400' : '' }}
                            {{ $user->role === 'volunteer' ? 'bg-green-600/20 text-green-400' : '' }}
                        ">{{ $user->role }}</span>
                        <a href="{{ route('system.users.edit', $user) }}" class="p-1 text-gray-400 hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                @empty
                <p class="px-6 py-4 text-gray-400">Немає користувачів</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Events -->
        <div class="bg-gray-800 rounded-2xl border border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-700">
                <h2 class="font-semibold text-white">Останні події</h2>
            </div>
            <div class="divide-y divide-gray-700">
                @forelse($recentEvents as $event)
                <div class="px-6 py-3">
                    <p class="font-medium text-white">{{ $event->title }}</p>
                    <p class="text-sm text-gray-400">{{ $event->start_date->format('d.m.Y H:i') }}</p>
                </div>
                @empty
                <p class="px-6 py-4 text-gray-400">Немає подій</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Audit Logs -->
    <div class="bg-gray-800 rounded-2xl border border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700">
            <h2 class="font-semibold text-white">Останні дії</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Час</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Користувач</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Дія</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Модель</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($auditLogs as $log)
                    <tr class="hover:bg-gray-700/30">
                        <td class="px-6 py-3 text-sm text-gray-300">{{ $log->created_at->format('d.m H:i') }}</td>
                        <td class="px-6 py-3 text-sm text-white">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-xs rounded-full
                                {{ $log->action === 'created' ? 'bg-green-600/20 text-green-400' : '' }}
                                {{ $log->action === 'updated' ? 'bg-blue-600/20 text-blue-400' : '' }}
                                {{ $log->action === 'deleted' ? 'bg-red-600/20 text-red-400' : '' }}
                            ">{{ $log->action }}</span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-400">{{ class_basename($log->model_type) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-400">Немає записів</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
