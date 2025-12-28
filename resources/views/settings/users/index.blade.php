@extends('layouts.app')

@section('title', 'Користувачі')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Користувачі</h1>
        @admin
        <a href="{{ route('settings.users.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Запросити
        </a>
        @endadmin
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ім'я</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Email</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Роль</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden sm:table-cell">Статус</th>
                        <th class="px-3 md:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($users as $user)
                    <tr>
                        <td class="px-3 md:px-6 py-3 md:py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-9 w-9 md:h-10 md:w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                    <span class="text-gray-600 dark:text-gray-300 font-medium text-sm">{{ mb_substr($user->name, 0, 1) }}</span>
                                </div>
                                <div class="ml-3 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $user->name }}</div>
                                    @if($user->person)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate hidden sm:block">{{ $user->person->full_name }}</div>
                                    @endif
                                    <!-- Mobile: show email under name -->
                                    <div class="md:hidden text-xs text-gray-400 dark:text-gray-500 truncate">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell">{{ $user->email }}</td>
                        <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $user->role === 'admin' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300' : '' }}
                                {{ $user->role === 'leader' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : '' }}
                                {{ $user->role === 'volunteer' ? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300' : '' }}">
                                {{ $user->role === 'admin' ? 'Адмін' : ($user->role === 'leader' ? 'Лідер' : 'Служитель') }}
                            </span>
                        </td>
                        <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap hidden sm:table-cell">
                            <span class="inline-flex items-center text-sm text-green-600 dark:text-green-400">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                <span class="hidden md:inline">Активний</span>
                            </span>
                        </td>
                        <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-right text-sm font-medium">
                            @admin
                            @if($user->id !== auth()->id())
                            <a href="{{ route('settings.users.edit', $user) }}" class="p-1.5 inline-flex text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form action="{{ route('settings.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Ви впевнені?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 inline-flex text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @else
                            <span class="text-gray-400 dark:text-gray-500 text-xs">Це ви</span>
                            @endif
                            @endadmin
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <a href="{{ route('settings.index') }}" class="inline-block text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
        &larr; Назад до налаштувань
    </a>
</div>
@endsection
