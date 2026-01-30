@extends('layouts.app')

@section('title', 'Групи')

@section('actions')
@can('create', App\Models\Group::class)
<a href="{{ route('groups.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
    </svg>
    Створити групу
</a>
@endcan
@endsection

@section('content')
<div class="space-y-8">
    @if($groups->isEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
        <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-purple-100 to-purple-50 dark:from-purple-900 dark:to-purple-800 rounded-2xl flex items-center justify-center">
            <svg class="w-10 h-10 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Немає груп</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6">Створіть групу для організації людей</p>
        @can('create', App\Models\Group::class)
        <a href="{{ route('groups.create') }}" class="inline-flex items-center px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-all">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Створити групу
        </a>
        @endcan
    </div>
    @else
    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $groups->count() }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Всього груп</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $groups->sum('members_count') }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Всього учасників</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $groups->count() > 0 ? round($groups->sum('members_count') / $groups->count(), 1) : 0 }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Середній розмір</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Groups Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Група</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Лідер</th>
                        <th class="px-3 md:px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Статус</th>
                        <th class="px-3 md:px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Учасників</th>
                        <th class="px-3 md:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Дії</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($groups as $group)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-3 md:px-6 py-3 md:py-4">
                            <a href="{{ route('groups.show', $group) }}" class="flex items-center group">
                                <div class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 md:w-5 md:h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-3 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 truncate">{{ $group->name }}</p>
                                    @if($group->description)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[150px] sm:max-w-xs hidden sm:block">{{ Str::limit($group->description, 50) }}</p>
                                    @endif
                                    <!-- Mobile: show leader under name -->
                                    <p class="md:hidden text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $group->leader?->full_name ?? '' }}</p>
                                </div>
                            </a>
                        </td>
                        <td class="px-3 md:px-6 py-3 md:py-4 hidden md:table-cell">
                            @if($group->leader)
                            <a href="{{ route('people.show', $group->leader) }}" class="text-sm text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400">
                                {{ $group->leader->full_name }}
                            </a>
                            @else
                            <span class="text-sm text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-center hidden sm:table-cell">
                            <span class="inline-flex items-center gap-1.5 px-2 md:px-2.5 py-1 rounded-lg text-xs font-medium
                                @if($group->status === 'active') bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300
                                @elseif($group->status === 'paused') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300
                                @else bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                                @endif">
                                <span class="w-1.5 h-1.5 rounded-full
                                    @if($group->status === 'active') bg-green-500
                                    @elseif($group->status === 'paused') bg-yellow-500
                                    @else bg-blue-500
                                    @endif"></span>
                                <span class="hidden md:inline">{{ $group->status_label }}</span>
                            </span>
                        </td>
                        <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2 md:px-2.5 py-1 rounded-lg text-sm font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                                {{ $group->members_count }}
                            </span>
                        </td>
                        <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-right">
                            <a href="{{ route('groups.show', $group) }}" class="p-2 inline-flex text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
