@extends('layouts.system-admin')

@section('title', 'Навігація користувачів')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Пошук (URL, ім'я, маршрут)..."
                   class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">

            <select name="church_id"
                    class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">Всі церкви</option>
                @foreach($churches as $church)
                <option value="{{ $church->id }}" {{ request('church_id') == $church->id ? 'selected' : '' }}>
                    {{ $church->name }}
                </option>
                @endforeach
            </select>

            <input type="date" name="from" value="{{ request('from') }}"
                   class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">

            <input type="date" name="to" value="{{ request('to') }}"
                   class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">

            <div class="flex gap-2 md:col-span-3 lg:col-span-4">
                <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">Фільтрувати</button>
                <a href="{{ route('system.page-visits') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-900 dark:text-white rounded-lg transition-colors">Скинути</a>
            </div>
        </form>
    </div>

    @if(request('user_id'))
        @php $filteredUser = \App\Models\User::find(request('user_id')); @endphp
        @if($filteredUser)
        <div class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center">
                    <span class="text-white text-sm font-bold">{{ mb_substr($filteredUser->name, 0, 1) }}</span>
                </div>
                <div>
                    <span class="text-sm font-medium text-indigo-900 dark:text-indigo-200">Навігація користувача:</span>
                    <span class="text-sm font-bold text-indigo-900 dark:text-white">{{ $filteredUser->name }}</span>
                </div>
            </div>
            <a href="{{ route('system.page-visits') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Показати всіх</a>
        </div>
        @endif
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $visits->total() }}</p>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Всього переглядів</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $visits->pluck('user_id')->unique()->count() }}</p>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Користувачів на сторінці</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $visits->pluck('route_name')->unique()->filter()->count() }}</p>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Унікальних сторінок</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $visits->pluck('church_id')->unique()->filter()->count() }}</p>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Церков на сторінці</p>
        </div>
    </div>

    <!-- Visits List -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Час</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Користувач</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Церква</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Сторінка</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden lg:table-cell">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($visits as $visit)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <!-- Time -->
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                                {{ $visit->created_at->format('d.m.Y H:i:s') }}
                            </span>
                        </td>

                        <!-- User -->
                        <td class="px-4 py-3">
                            <a href="{{ route('system.page-visits', ['user_id' => $visit->user_id]) }}"
                               class="flex items-center gap-2 hover:opacity-80 transition-opacity group">
                                <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">
                                        {{ mb_substr($visit->user_name, 0, 1) }}
                                    </span>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 truncate max-w-[150px]">
                                    {{ $visit->user_name }}
                                </span>
                            </a>
                        </td>

                        <!-- Church -->
                        <td class="px-4 py-3 hidden md:table-cell">
                            @if($visit->church)
                                <a href="{{ route('system.churches.show', $visit->church) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded truncate max-w-[150px] inline-block">
                                    {{ $visit->church->name }}
                                </a>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>

                        <!-- Page -->
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $visit->routeLabel() }}
                                </span>
                                <span class="text-xs text-gray-400 dark:text-gray-500 truncate max-w-[300px]" title="{{ $visit->url }}">
                                    {{ Str::limit(parse_url($visit->url, PHP_URL_PATH) ?: $visit->url, 60) }}
                                </span>
                            </div>
                        </td>

                        <!-- IP -->
                        <td class="px-4 py-3 hidden lg:table-cell">
                            <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                                {{ $visit->ip_address }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            Записів не знайдено
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($visits->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $visits->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
