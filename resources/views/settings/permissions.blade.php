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
            ← Назад до налаштувань
        </a>
    </div>

    <!-- Role tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px">
                @foreach($roles as $roleKey => $roleLabel)
                <button @click="currentRole = '{{ $roleKey }}'"
                        :class="currentRole === '{{ $roleKey }}' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300'"
                        class="flex-1 py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors">
                    {{ $roleLabel }}
                </button>
                @endforeach
            </nav>
        </div>

        <div class="p-6">
            <!-- Admin notice -->
            <div x-show="currentRole === 'admin'" class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <div>
                        <p class="font-medium text-blue-800 dark:text-blue-200">Адміністратор має повний доступ</p>
                        <p class="text-sm text-blue-600 dark:text-blue-400">Права адміністратора не можна обмежити. Налаштуйте права для лідерів та волонтерів.</p>
                    </div>
                </div>
            </div>

            <!-- Permissions table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-3 text-sm font-semibold text-gray-900 dark:text-white">Модуль</th>
                            @foreach($actions as $actionKey => $actionLabel)
                            <th class="pb-3 text-sm font-semibold text-gray-900 dark:text-white text-center w-24">{{ $actionLabel }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($modules as $moduleKey => $module)
                        <tr>
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500">
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
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $module['label'] }}</span>
                                </div>
                            </td>
                            @foreach($actions as $actionKey => $actionLabel)
                            <td class="py-4 text-center">
                                <label class="inline-flex items-center justify-center">
                                    <template x-if="currentRole === 'admin'">
                                        <input type="checkbox" checked disabled
                                               class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-primary-600 bg-gray-100 dark:bg-gray-600 cursor-not-allowed">
                                    </template>
                                    <template x-if="currentRole !== 'admin'">
                                        <input type="checkbox"
                                               x-model="rolePermissions[currentRole]['{{ $moduleKey }}']"
                                               value="{{ $actionKey }}"
                                               @change="markDirty()"
                                               class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:bg-gray-700">
                                    </template>
                                </label>
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Actions (hidden for admin) -->
            <div x-show="currentRole !== 'admin'" class="flex items-center justify-between mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
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
        </div>
    </div>

    <!-- Info -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <p class="font-medium mb-1">Як працюють права доступу</p>
                <ul class="list-disc list-inside space-y-1 text-blue-700 dark:text-blue-300">
                    <li><strong>Адміністратор</strong> - повний доступ до всіх функцій (рекомендовано не обмежувати)</li>
                    <li><strong>Лідер</strong> - може керувати людьми, групами та подіями свого служіння</li>
                    <li><strong>Волонтер</strong> - базовий доступ для перегляду та виконання завдань</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function permissionsManager() {
    return {
        currentRole: 'leader',
        isDirty: false,
        saving: false,
        rolePermissions: @json($permissions),

        markDirty() {
            this.isDirty = true;
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
                        role: this.currentRole,
                        permissions: this.rolePermissions[this.currentRole],
                    }),
                });

                if (response.ok) {
                    this.isDirty = false;
                    // Show success toast
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { message: 'Права доступу збережено', type: 'success' }
                    }));
                } else {
                    throw new Error('Failed to save');
                }
            } catch (error) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: 'Помилка збереження', type: 'error' }
                }));
            }

            this.saving = false;
        },

        async resetToDefaults() {
            if (!confirm('Скинути права для ролі "' + this.getRoleName() + '" до стандартних?')) {
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
                        role: this.currentRole,
                    }),
                });

                if (response.ok) {
                    // Reload page to get fresh data
                    window.location.reload();
                }
            } catch (error) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: 'Помилка скидання', type: 'error' }
                }));
            }
        },

        getRoleName() {
            const roles = @json($roles);
            return roles[this.currentRole] || this.currentRole;
        }
    }
}
</script>
@endsection
