@extends('layouts.system-admin')

@section('title', 'Церкви')

@section('actions')
<a href="{{ route('system.churches.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg">
    + Додати церкву
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Search -->
    <div class="bg-gray-800 rounded-xl p-4 border border-gray-700">
        <form method="GET" class="flex flex-col sm:flex-row gap-3 sm:gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Пошук церкви..."
                   class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-red-500 focus:border-transparent">
            <button type="submit" class="px-6 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-lg">Шукати</button>
        </form>
    </div>

    <!-- Mobile Cards (visible on small screens) -->
    <div class="lg:hidden space-y-4">
        @forelse($churches as $church)
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
            <!-- Header with logo and name -->
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center">
                    @if($church->logo)
                    <img src="/storage/{{ $church->logo }}" alt="{{ $church->name }}" class="w-12 h-12 rounded-lg object-cover mr-3">
                    @else
                    <div class="w-12 h-12 rounded-lg bg-blue-600/20 flex items-center justify-center mr-3">
                        <span class="text-xl">⛪</span>
                    </div>
                    @endif
                    <div>
                        <p class="font-medium text-white">{{ $church->name }}</p>
                        <p class="text-sm text-gray-400">{{ $church->city }}</p>
                    </div>
                </div>
                @if($church->public_site_enabled)
                <span class="px-2 py-1 bg-green-600/20 text-green-400 text-xs rounded-full whitespace-nowrap">Публ. сайт</span>
                @endif
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-4 gap-2 mb-4">
                <div class="bg-gray-700/50 rounded-lg p-2 text-center">
                    <p class="text-lg font-semibold text-white">{{ $church->users_count }}</p>
                    <p class="text-xs text-gray-400">Юзери</p>
                </div>
                <div class="bg-gray-700/50 rounded-lg p-2 text-center">
                    <p class="text-lg font-semibold text-white">{{ $church->people_count }}</p>
                    <p class="text-xs text-gray-400">Люди</p>
                </div>
                <div class="bg-gray-700/50 rounded-lg p-2 text-center">
                    <p class="text-lg font-semibold text-white">{{ $church->ministries_count }}</p>
                    <p class="text-xs text-gray-400">Служ.</p>
                </div>
                <div class="bg-gray-700/50 rounded-lg p-2 text-center">
                    <p class="text-lg font-semibold text-white">{{ $church->events_count }}</p>
                    <p class="text-xs text-gray-400">Події</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 pt-3 border-t border-gray-700">
                <a href="{{ route('system.churches.show', $church) }}"
                   class="flex-1 py-2 px-3 bg-gray-700 hover:bg-gray-600 text-white text-sm text-center rounded-lg">
                    Переглянути
                </a>
                <form method="POST" action="{{ route('system.churches.switch', $church) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-2 px-3 bg-green-600/20 hover:bg-green-600/30 text-green-400 text-sm rounded-lg">
                        Увійти
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-8 text-center text-gray-400">
            Церкви не знайдено
        </div>
        @endforelse
    </div>

    <!-- Desktop Table (hidden on small screens) -->
    <div class="hidden lg:block bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Церква</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Місто</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Користувачі</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Люди</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Служіння</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Події</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Публ. сайт</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-400 uppercase">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($churches as $church)
                    <tr class="hover:bg-gray-700/30">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($church->logo)
                                <img src="/storage/{{ $church->logo }}" alt="{{ $church->name }}" class="w-10 h-10 rounded-lg object-cover mr-3">
                                @else
                                <div class="w-10 h-10 rounded-lg bg-blue-600/20 flex items-center justify-center mr-3">
                                    <span class="text-lg">⛪</span>
                                </div>
                                @endif
                                <div>
                                    <p class="font-medium text-white">{{ $church->name }}</p>
                                    <p class="text-xs text-gray-400">ID: {{ $church->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-300">{{ $church->city }}</td>
                        <td class="px-6 py-4 text-center text-gray-300">{{ $church->users_count }}</td>
                        <td class="px-6 py-4 text-center text-gray-300">{{ $church->people_count }}</td>
                        <td class="px-6 py-4 text-center text-gray-300">{{ $church->ministries_count }}</td>
                        <td class="px-6 py-4 text-center text-gray-300">{{ $church->events_count }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($church->public_site_enabled)
                            <span class="px-2 py-1 bg-green-600/20 text-green-400 text-xs rounded-full">Увімкнено</span>
                            @else
                            <span class="px-2 py-1 bg-gray-600/20 text-gray-400 text-xs rounded-full">Вимкнено</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('system.churches.show', $church) }}"
                                   class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('system.churches.switch', $church) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="p-2 text-gray-400 hover:text-green-400 hover:bg-gray-700 rounded-lg" title="Увійти в церкву">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-400">Церкви не знайдено</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($churches->hasPages())
        <div class="px-6 py-4 border-t border-gray-700">
            {{ $churches->links() }}
        </div>
        @endif
    </div>

    <!-- Mobile Pagination -->
    @if($churches->hasPages())
    <div class="lg:hidden">
        {{ $churches->links() }}
    </div>
    @endif
</div>
@endsection
