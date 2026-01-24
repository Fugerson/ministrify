@extends('layouts.app')

@section('title', 'Користувачі')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Користувачі</h1>
        @admin
        <a href="{{ route('settings.users.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 text-sm font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Запросити
        </a>
        @endadmin
    </div>

    @php
        $pendingUsers = $users->whereNull('church_role_id');
        $activeUsers = $users->whereNotNull('church_role_id');
    @endphp

    @if($pendingUsers->count() > 0)
    <!-- Pending Access Requests -->
    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl shadow-sm border border-yellow-200 dark:border-yellow-800 overflow-hidden">
        <div class="px-4 py-3 border-b border-yellow-200 dark:border-yellow-800">
            <h2 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Очікують доступу ({{ $pendingUsers->count() }})
            </h2>
        </div>
        <div class="divide-y divide-yellow-200 dark:divide-yellow-800">
            @foreach($pendingUsers as $user)
            <div class="px-4 py-3 flex items-center justify-between gap-4" x-data="{ showRoleSelect: false, selectedRole: '' }">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-yellow-200 dark:bg-yellow-800 flex items-center justify-center">
                        <span class="text-yellow-700 dark:text-yellow-300 font-medium">{{ mb_substr($user->name, 0, 1) }}</span>
                    </div>
                    <div class="min-w-0">
                        <div class="font-medium text-gray-900 dark:text-white truncate">{{ $user->name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <!-- Role selector (hidden by default) -->
                    <div x-show="showRoleSelect" x-cloak class="flex items-center gap-2">
                        <select x-model="selectedRole" class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Оберіть роль</option>
                            @foreach(\App\Models\ChurchRole::where('church_id', auth()->user()->church_id)->orderBy('sort_order')->get() as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <form method="POST" action="{{ route('settings.users.update', $user) }}" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="name" value="{{ $user->name }}">
                            <input type="hidden" name="email" value="{{ $user->email }}">
                            <input type="hidden" name="church_role_id" :value="selectedRole">
                            @if($user->person)
                            <input type="hidden" name="person_id" value="{{ $user->person->id }}">
                            @endif
                            <button type="submit" :disabled="!selectedRole"
                                    class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                Зберегти
                            </button>
                        </form>
                        <button @click="showRoleSelect = false" class="p-1.5 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <!-- Buttons (visible by default) -->
                    <div x-show="!showRoleSelect" class="flex items-center gap-2">
                        <button @click="showRoleSelect = true"
                                class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="hidden sm:inline">Надати</span>
                        </button>
                        <form action="{{ route('settings.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Відхилити запит від {{ $user->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <span class="hidden sm:inline">Відхилити</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

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
                    @foreach($activeUsers as $user)
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
                            @if($user->churchRole)
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                  style="background-color: {{ $user->churchRole->color }}20; color: {{ $user->churchRole->color }}">
                                {{ $user->churchRole->name }}
                            </span>
                            @else
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                Без ролі
                            </span>
                            @endif
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
                            <a href="{{ route('settings.users.edit', $user) }}" class="p-2 inline-flex text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form action="{{ route('settings.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Ви впевнені?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 inline-flex text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
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
