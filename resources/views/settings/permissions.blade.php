@extends('layouts.app')

@section('title', 'Права доступу')

@section('content')
<div class="space-y-6" x-data="permissionsManager()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Права доступу</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Налаштуйте що може бачити та робити кожна роль</p>
        </div>
        <a href="{{ route('settings.index') }}" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">
            &larr; Назад до налаштувань
        </a>
    </div>

    @if($churchRoles->isEmpty())
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="font-medium text-yellow-800 dark:text-yellow-200">Немає церковних ролей</p>
                <p class="text-sm text-yellow-600 dark:text-yellow-400">
                    <a href="{{ route('settings.church-roles.index') }}" class="underline">Створіть церковні ролі</a> спочатку, щоб налаштувати права доступу.
                </p>
            </div>
        </div>
    </div>
    @else
    <!-- Role tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
            <nav class="flex -mb-px min-w-max">
                @foreach($churchRoles as $role)
                <button @click="currentRoleId = {{ $role->id }}"
                        :class="currentRoleId === {{ $role->id }} ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300'"
                        class="py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors whitespace-nowrap flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $role->color }}"></span>
                    {{ $role->name }}
                    @if($role->is_admin_role)
                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    @endif
                </button>
                @endforeach
            </nav>
        </div>

        <div class="p-6">
            <!-- Admin notice -->
            <template x-if="isCurrentRoleAdmin()">
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-blue-800 dark:text-blue-200">Ця роль має повний доступ</p>
                            <p class="text-sm text-blue-600 dark:text-blue-400">Права ролі з повним доступом не можна обмежити. Налаштуйте права для інших ролей.</p>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Bulk actions (hidden for admin roles) -->
            <template x-if="!isCurrentRoleAdmin()">
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Швидкий вибір:</span>
                    <button type="button" @click="selectAll()" class="px-3 py-1.5 text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors">
                        Все
                    </button>
                    <button type="button" @click="selectNone()" class="px-3 py-1.5 text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">
                        Нічого
                    </button>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <button type="button" @click="selectColumn('view')" class="px-3 py-1.5 text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                        + Перегляд
                    </button>
                    <button type="button" @click="selectColumn('create')" class="px-3 py-1.5 text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                        + Створення
                    </button>
                    <button type="button" @click="selectColumn('edit')" class="px-3 py-1.5 text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                        + Редагування
                    </button>
                    <button type="button" @click="selectColumn('delete')" class="px-3 py-1.5 text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                        + Видалення
                    </button>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <button type="button" @click="selectViewOnly()" class="px-3 py-1.5 text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-lg hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-colors">
                        Тільки перегляд
                    </button>
                </div>
            </template>

            <!-- Permissions table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-3 text-sm font-semibold text-gray-900 dark:text-white">Модуль</th>
                            @foreach($actions as $actionKey => $actionLabel)
                            <th class="pb-3 text-sm font-semibold text-gray-900 dark:text-white text-center w-24">
                                <span>{{ $actionLabel }}</span>
                                <template x-if="!isCurrentRoleAdmin()">
                                    <button type="button" @click="toggleColumn('{{ $actionKey }}')" class="block mx-auto mt-1 text-xs text-primary-600 dark:text-primary-400 hover:underline">
                                        вкл/викл
                                    </button>
                                </template>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($modules as $moduleKey => $module)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="!isCurrentRoleAdmin() && toggleRow('{{ $moduleKey }}')"
                                            :class="isCurrentRoleAdmin() ? 'cursor-default' : 'cursor-pointer hover:bg-primary-100 dark:hover:bg-primary-900/30 hover:text-primary-600 dark:hover:text-primary-400'"
                                            class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 transition-colors"
                                            :title="isCurrentRoleAdmin() ? '' : 'Вибрати все / нічого для цього модуля'">
                                        @switch($module['icon'])
                                            @case('home')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                                @break
                                            @case('users')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                                @break
                                            @case('user-group')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                @break
                                            @case('heart')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                                @break
                                            @case('calendar')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                @break
                                            @case('currency-dollar')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @break
                                            @case('chart-bar')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                                @break
                                            @case('folder')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                                @break
                                            @case('view-boards')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                                                @break
                                            @case('speakerphone')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                                @break
                                            @case('globe')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @break
                                            @case('cog')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                @break
                                            @default
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                        @endswitch
                                    </button>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $module['label'] }}</span>
                                </div>
                            </td>
                            @foreach($actions as $actionKey => $actionLabel)
                            <td class="py-4 text-center">
                                @if(in_array($actionKey, $module['actions'] ?? []))
                                <label class="inline-flex items-center justify-center">
                                    <template x-if="isCurrentRoleAdmin()">
                                        <input type="checkbox" checked disabled
                                               class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-primary-600 bg-gray-100 dark:bg-gray-600 cursor-not-allowed">
                                    </template>
                                    <template x-if="!isCurrentRoleAdmin()">
                                        <input type="checkbox"
                                               x-model="rolePermissions[currentRoleId]['{{ $moduleKey }}']"
                                               value="{{ $actionKey }}"
                                               @change="markDirty()"
                                               class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:bg-gray-700">
                                    </template>
                                </label>
                                @else
                                <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Actions (hidden for admin roles) -->
            <template x-if="!isCurrentRoleAdmin()">
                <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button @click="resetToDefaults()"
                            type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        Скинути до стандартних
                    </button>
                    <button @click="savePermissions()"
                            :disabled="!isDirty || saving"
                            class="px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span x-show="!saving">Зберегти зміни</span>
                        <span x-show="saving" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Збереження...
                        </span>
                    </button>
                </div>
            </template>
        </div>
    </div>
    @endif

    <!-- Individual User Permissions Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Персональні права</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Розширте або обмежте права для окремих користувачів</p>
                </div>
                <button @click="showUserModal = true"
                        class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                    Додати користувача
                </button>
            </div>
        </div>

        <div class="p-6">
            @if($usersWithOverrides->isEmpty())
            <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <p>Немає користувачів з персональними правами</p>
                <p class="text-xs mt-1">Натисніть "Додати користувача" щоб налаштувати персональні права</p>
            </div>
            @else
            <div class="space-y-3">
                @foreach($usersWithOverrides as $userOverride)
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center text-primary-600 dark:text-primary-400 font-medium">
                            {{ substr($userOverride->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $userOverride->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $userOverride->churchRole?->name ?? 'Без ролі' }}
                                <span class="text-purple-600 dark:text-purple-400">• персональні права</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="editUserOverrides({{ $userOverride->id }})"
                                class="p-2 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </button>
                        <button @click="clearUserOverrides({{ $userOverride->id }}, '{{ $userOverride->name }}')"
                                class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <!-- User Selection Modal -->
    <template x-teleport="body">
        <div x-show="showUserModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/50" @click="showUserModal = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Вибрати користувача</h3>

                    <div class="mb-4">
                        <input type="text" x-model="userSearch" placeholder="Пошук за ім'ям..."
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <div class="max-h-64 overflow-y-auto space-y-2">
                        @foreach($allUsers as $user)
                        <button @click="selectUser({{ $user->id }})"
                                x-show="'{{ strtolower($user->name) }}'.includes(userSearch.toLowerCase())"
                                class="w-full flex items-center gap-3 p-3 text-left hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 text-sm font-medium">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->churchRole?->name ?? 'Без ролі' }}</p>
                            </div>
                        </button>
                        @endforeach
                    </div>

                    <div class="flex justify-end mt-4">
                        <button @click="showUserModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300">
                            Скасувати
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- User Permissions Modal -->
    <template x-teleport="body">
        <div x-show="showUserPermissionsModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/50" @click="showUserPermissionsModal = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-3xl w-full p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Персональні права: <span x-text="selectedUserName"></span>
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Роль: <span x-text="selectedUserRoleName || 'Без ролі'"></span>
                            </p>
                        </div>
                        <button @click="showUserPermissionsModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="mb-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                        <p class="text-sm text-amber-800 dark:text-amber-200">
                            <strong>+</strong> = дозволити додатково до ролі &nbsp;|&nbsp;
                            <strong>−</strong> = заборонити (навіть якщо роль дозволяє)
                        </p>
                    </div>

                    <div class="overflow-x-auto max-h-96">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                                    <th class="pb-2 font-medium text-gray-900 dark:text-white">Модуль</th>
                                    <th class="pb-2 font-medium text-gray-900 dark:text-white text-center" colspan="4">Дозволити (+)</th>
                                    <th class="pb-2 font-medium text-gray-900 dark:text-white text-center" colspan="4">Заборонити (−)</th>
                                </tr>
                                <tr class="text-xs text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                                    <th></th>
                                    <th class="pb-2 text-center w-16">Перегл.</th>
                                    <th class="pb-2 text-center w-16">Створ.</th>
                                    <th class="pb-2 text-center w-16">Ред.</th>
                                    <th class="pb-2 text-center w-16">Видал.</th>
                                    <th class="pb-2 text-center w-16">Перегл.</th>
                                    <th class="pb-2 text-center w-16">Створ.</th>
                                    <th class="pb-2 text-center w-16">Ред.</th>
                                    <th class="pb-2 text-center w-16">Видал.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($modules as $moduleKey => $module)
                                <tr>
                                    <td class="py-2 text-gray-700 dark:text-gray-300">{{ $module['label'] }}</td>
                                    @foreach(['view', 'create', 'edit', 'delete'] as $action)
                                    <td class="py-2 text-center">
                                        <input type="checkbox"
                                               x-model="userOverrides['{{ $moduleKey }}'].allow"
                                               value="{{ $action }}"
                                               class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                    </td>
                                    @endforeach
                                    @foreach(['view', 'create', 'edit', 'delete'] as $action)
                                    <td class="py-2 text-center">
                                        <input type="checkbox"
                                               x-model="userOverrides['{{ $moduleKey }}'].deny"
                                               value="{{ $action }}"
                                               class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="showUserPermissionsModal = false"
                                class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            Скасувати
                        </button>
                        <button @click="saveUserOverrides()"
                                :disabled="savingUserOverrides"
                                class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 flex items-center gap-2">
                            <svg x-show="savingUserOverrides" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="savingUserOverrides ? 'Збереження...' : 'Зберегти'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Info -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <p class="font-medium mb-1">Як працюють права доступу</p>
                <ul class="list-disc list-inside space-y-1 text-blue-700 dark:text-blue-300">
                    <li>Ролі з <strong>повним доступом</strong> (позначені щитом) мають доступ до всіх функцій</li>
                    <li>Для інших ролей налаштуйте окремі права для кожного модуля</li>
                    <li><strong>Персональні права</strong> мають пріоритет над правами ролі</li>
                    <li><a href="{{ route('settings.church-roles.index') }}" class="underline">Керувати ролями</a> можна на сторінці "Церковні ролі"</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function permissionsManager() {
    const moduleKeys = @json(array_keys($modules));
    const modulesConfig = @json($modules);

    return {
        currentRoleId: {{ $churchRoles->first()?->id ?? 0 }},
        isDirty: false,
        saving: false,
        rolePermissions: @json($permissions),
        roles: @json($churchRoles->keyBy('id')),

        // User overrides
        showUserModal: false,
        showUserPermissionsModal: false,
        userSearch: '',
        selectedUserId: null,
        selectedUserName: '',
        selectedUserRoleName: '',
        userOverrides: {},
        savingUserOverrides: false,
        allUsers: @json($allUsers->keyBy('id')),

        init() {
            this.initEmptyUserOverrides();
        },

        initEmptyUserOverrides() {
            this.userOverrides = {};
            moduleKeys.forEach(module => {
                this.userOverrides[module] = { allow: [], deny: [] };
            });
        },

        isCurrentRoleAdmin() {
            return this.roles[this.currentRoleId]?.is_admin_role ?? false;
        },

        markDirty() {
            this.isDirty = true;
        },

        // Bulk selection methods
        selectAll() {
            moduleKeys.forEach(module => {
                const allowedActions = modulesConfig[module]?.actions || [];
                this.rolePermissions[this.currentRoleId][module] = [...allowedActions];
            });
            this.markDirty();
        },

        selectNone() {
            moduleKeys.forEach(module => {
                this.rolePermissions[this.currentRoleId][module] = [];
            });
            this.markDirty();
        },

        selectColumn(action) {
            moduleKeys.forEach(module => {
                const allowedActions = modulesConfig[module]?.actions || [];
                if (!allowedActions.includes(action)) return; // Skip if action not allowed for this module

                const perms = this.rolePermissions[this.currentRoleId][module] || [];
                if (!perms.includes(action)) {
                    this.rolePermissions[this.currentRoleId][module] = [...perms, action];
                }
            });
            this.markDirty();
        },

        toggleColumn(action) {
            // Only consider modules that have this action
            const relevantModules = moduleKeys.filter(m => (modulesConfig[m]?.actions || []).includes(action));

            const allHave = relevantModules.every(module => {
                const perms = this.rolePermissions[this.currentRoleId][module] || [];
                return perms.includes(action);
            });

            relevantModules.forEach(module => {
                let perms = this.rolePermissions[this.currentRoleId][module] || [];
                if (allHave) {
                    this.rolePermissions[this.currentRoleId][module] = perms.filter(p => p !== action);
                } else {
                    if (!perms.includes(action)) {
                        this.rolePermissions[this.currentRoleId][module] = [...perms, action];
                    }
                }
            });
            this.markDirty();
        },

        selectViewOnly() {
            moduleKeys.forEach(module => {
                const allowedActions = modulesConfig[module]?.actions || [];
                this.rolePermissions[this.currentRoleId][module] = allowedActions.includes('view') ? ['view'] : [];
            });
            this.markDirty();
        },

        toggleRow(module) {
            const allowedActions = modulesConfig[module]?.actions || [];
            const perms = this.rolePermissions[this.currentRoleId][module] || [];

            if (perms.length === allowedActions.length) {
                this.rolePermissions[this.currentRoleId][module] = [];
            } else {
                this.rolePermissions[this.currentRoleId][module] = [...allowedActions];
            }
            this.markDirty();
        },

        async savePermissions() {
            this.saving = true;

            try {
                const response = await fetch('{{ route('settings.permissions.update') }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        role_id: this.currentRoleId,
                        permissions: this.rolePermissions[this.currentRoleId],
                    }),
                });

                if (response.ok) {
                    this.isDirty = false;
                    if (window.showGlobalToast) {
                        showGlobalToast('Права доступу збережено', 'success');
                    }
                } else {
                    throw new Error('Failed to save');
                }
            } catch (error) {
                if (window.showGlobalToast) {
                    showGlobalToast('Помилка збереження', 'error');
                }
            }

            this.saving = false;
        },

        async resetToDefaults() {
            const roleName = this.roles[this.currentRoleId]?.name || 'цієї ролі';
            if (!confirm(`Скинути права для "${roleName}" до стандартних?`)) {
                return;
            }

            try {
                const response = await fetch('{{ route('settings.permissions.reset') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        role_id: this.currentRoleId,
                    }),
                });

                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                if (window.showGlobalToast) {
                    showGlobalToast('Помилка скидання', 'error');
                }
            }
        },

        // User overrides methods
        selectUser(userId) {
            this.showUserModal = false;
            this.editUserOverrides(userId);
        },

        async editUserOverrides(userId) {
            this.selectedUserId = userId;
            const user = this.allUsers[userId];
            this.selectedUserName = user?.name || '';
            this.selectedUserRoleName = user?.church_role?.name || '';

            try {
                const response = await fetch(`/settings/permissions/user/${userId}`, {
                    headers: { 'Accept': 'application/json' }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.initEmptyUserOverrides();

                    // Populate overrides from response
                    if (data.overrides) {
                        Object.keys(data.overrides).forEach(module => {
                            if (this.userOverrides[module]) {
                                this.userOverrides[module].allow = data.overrides[module].allow || [];
                                this.userOverrides[module].deny = data.overrides[module].deny || [];
                            }
                        });
                    }

                    this.showUserPermissionsModal = true;
                }
            } catch (error) {
                if (window.showGlobalToast) {
                    showGlobalToast('Помилка завантаження', 'error');
                }
            }
        },

        async saveUserOverrides() {
            this.savingUserOverrides = true;

            try {
                const response = await fetch(`/settings/permissions/user/${this.selectedUserId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        overrides: this.userOverrides,
                    }),
                });

                if (response.ok) {
                    this.showUserPermissionsModal = false;
                    if (window.showGlobalToast) {
                        showGlobalToast('Персональні права збережено', 'success');
                    }
                    window.location.reload();
                } else {
                    throw new Error('Failed to save');
                }
            } catch (error) {
                if (window.showGlobalToast) {
                    showGlobalToast('Помилка збереження', 'error');
                }
            }

            this.savingUserOverrides = false;
        },

        async clearUserOverrides(userId, userName) {
            if (!confirm(`Видалити персональні права для "${userName}"?`)) {
                return;
            }

            try {
                const response = await fetch(`/settings/permissions/user/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });

                if (response.ok) {
                    if (window.showGlobalToast) {
                        showGlobalToast('Персональні права видалено', 'success');
                    }
                    window.location.reload();
                }
            } catch (error) {
                if (window.showGlobalToast) {
                    showGlobalToast('Помилка видалення', 'error');
                }
            }
        }
    }
}
</script>
@endsection
