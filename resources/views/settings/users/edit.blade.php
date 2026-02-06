@extends('layouts.app')

@section('title', 'Редагувати користувача')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Редагувати користувача</h1>
    </div>

    @php
        $linkedPerson = $user->person;
        $initialPersonData = $linkedPerson ? [
            'id' => $linkedPerson->id,
            'full_name' => $linkedPerson->full_name,
            'email' => $linkedPerson->email,
        ] : null;
        $userHadNoRole = $user->church_role_id === null;
    @endphp
    <form action="{{ route('settings.users.update', $user) }}" method="POST" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6"
          x-data="{
              personSelected: {{ json_encode($initialPersonData) }},
              hadNoRole: {{ $userHadNoRole ? 'true' : 'false' }},
              showConfirm: false,
              selectedRoleName: '',
              submitForm() {
                  const roleSelect = document.getElementById('church_role_id');
                  const selectedRole = roleSelect.value;
                  if (this.hadNoRole && selectedRole) {
                      this.selectedRoleName = roleSelect.options[roleSelect.selectedIndex].text;
                      this.showConfirm = true;
                  } else {
                      this.$el.submit();
                  }
              }
          }"
          @person-selected.window="personSelected = $event.detail.person"
          @submit.prevent="submitForm()">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Прив'язати до людини</label>
            <div class="mt-1">
                <x-person-select
                    name="person_id"
                    :people="$people"
                    :selected="old('person_id', $user->person?->id)"
                    placeholder="Почніть вводити ім'я..."
                    null-text="Відв'язати"
                />
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Якщо обрати людину, дані візьмуться з її профілю</p>
        </div>

        <!-- Show selected person info -->
        <div x-show="personSelected && personSelected?.email" x-cloak class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4">
            <p class="text-sm text-green-700 dark:text-green-300">
                <span class="font-medium" x-text="personSelected?.full_name"></span>
                <span class="text-green-600 dark:text-green-400">
                    (<span x-text="personSelected?.email"></span>)
                </span>
            </p>
        </div>

        <!-- Warning when person has no email -->
        <div x-show="personSelected && !personSelected?.email" x-cloak class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4">
            <p class="text-sm text-amber-700 dark:text-amber-300">
                <span class="font-medium" x-text="personSelected?.full_name"></span>
                — немає email. Додайте email в профілі людини.
            </p>
        </div>

        <!-- Show name/email fields only when no person selected -->
        <div x-show="!personSelected" x-cloak>
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ім'я</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" x-bind:required="!personSelected"
                        class="mt-1 block w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-transparent rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white @error('name') border-red-500 bg-red-50 dark:bg-red-900/20 @enderror">
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" x-bind:required="!personSelected"
                        class="mt-1 block w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-transparent rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white @error('email') border-red-500 bg-red-50 dark:bg-red-900/20 @enderror">
                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div>
            <label for="church_role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Роль</label>
            <select name="church_role_id" id="church_role_id"
                class="mt-1 block w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-transparent rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white @error('church_role_id') border-red-500 bg-red-50 dark:bg-red-900/20 @enderror">
                <option value="" {{ old('church_role_id', $user->church_role_id) === null ? 'selected' : '' }}>
                    Очікує підтвердження (без доступу)
                </option>
                @foreach($churchRoles as $role)
                <option value="{{ $role->id }}" {{ old('church_role_id', $user->church_role_id) == $role->id ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
                @endforeach
            </select>
            @error('church_role_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Новий пароль (залиште порожнім щоб не змінювати)</label>
            <input type="password" name="password" id="password"
                class="mt-1 block w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
            @error('password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 sm:gap-3">
            <a href="{{ route('settings.users.index') }}" class="w-full sm:w-auto px-4 py-2 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Скасувати</a>
            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700">
                Зберегти
            </button>
        </div>

        <!-- Confirmation Modal -->
        <div x-show="showConfirm" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity" @click="showConfirm = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-4"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 dark:bg-green-900/30 rounded-full mb-4">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white text-center mb-2">Надати доступ?</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-1">
                            Ви збираєтесь надати користувачу <strong>{{ $user->name }}</strong> роль:
                        </p>
                        <p class="text-center font-semibold text-primary-600 dark:text-primary-400 mb-4" x-text="selectedRoleName"></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">
                            Користувач отримає email-сповіщення про надання доступу.
                        </p>
                        <div class="flex gap-3">
                            <button type="button" @click="showConfirm = false"
                                    class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 font-medium transition-colors">
                                Скасувати
                            </button>
                            <button type="button" @click="showConfirm = false; $el.closest('form').submit();"
                                    class="flex-1 px-4 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium transition-colors">
                                Так, надати доступ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Permission Overrides Section (hidden for admin roles) --}}
    @if($user->church_role_id && !$user->churchRole?->is_admin_role)
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
         x-data="permissionOverrides()"

        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Додаткові права</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Персональні права понад роль користувача</p>
            </div>
            <button type="button" @click="showModal = true"
                    class="px-4 py-2 text-sm font-medium text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/30 rounded-xl hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors">
                Налаштувати
            </button>
        </div>

        {{-- Override badges --}}
        <div class="flex flex-wrap gap-2" x-show="hasOverrides()" x-cloak>
            <template x-for="badge in getOverrideBadges()" :key="badge.key">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300">
                    <span x-text="badge.label"></span>
                </span>
            </template>
        </div>
        <p x-show="!hasOverrides()" class="text-sm text-gray-400 dark:text-gray-500">Додаткових прав не призначено</p>

        {{-- Permissions Modal --}}
        <div x-show="showModal" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="flex items-start justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity" @click="showModal = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-xl transform transition-all w-full max-w-3xl mx-4"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     @keydown.escape.window="showModal = false">

                    <div class="p-6">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Налаштування прав</h3>
                            <button type="button" @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Роль: <span class="font-medium text-gray-700 dark:text-gray-300" x-text="roleName"></span>
                        </p>

                        {{-- Legend --}}
                        <div class="flex flex-wrap items-center gap-4 mb-4 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1">
                                <span class="w-4 h-4 rounded bg-green-100 dark:bg-green-900/40 border border-green-300 dark:border-green-700 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                Від ролі
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="w-4 h-4 rounded bg-purple-100 dark:bg-purple-900/40 border-2 border-purple-400 dark:border-purple-500"></span>
                                Додатково
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="w-4 h-4 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-300 dark:text-gray-600">&mdash;</span>
                                Недоступно
                            </span>
                        </div>

                        {{-- Permissions Table --}}
                        <div class="overflow-x-auto -mx-6 px-6">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th class="text-left py-2 pr-4 font-medium text-gray-600 dark:text-gray-400">Модуль</th>
                                        <th class="text-center py-2 px-2 font-medium text-gray-600 dark:text-gray-400 w-20">Перегляд</th>
                                        <th class="text-center py-2 px-2 font-medium text-gray-600 dark:text-gray-400 w-20">Створ.</th>
                                        <th class="text-center py-2 px-2 font-medium text-gray-600 dark:text-gray-400 w-20">Редаг.</th>
                                        <th class="text-center py-2 px-2 font-medium text-gray-600 dark:text-gray-400 w-20">Видал.</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                    <template x-for="mod in modules" :key="mod.key">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                            <td class="py-2.5 pr-4">
                                                <span class="font-medium text-gray-900 dark:text-white" x-text="mod.label"></span>
                                            </td>
                                            <template x-for="action in allActions" :key="action">
                                                <td class="text-center py-2.5 px-2">
                                                    {{-- Not allowed for this module --}}
                                                    <template x-if="!mod.actions.includes(action)">
                                                        <span class="text-gray-300 dark:text-gray-600">&mdash;</span>
                                                    </template>
                                                    {{-- Already granted by role --}}
                                                    <template x-if="mod.actions.includes(action) && isRolePermission(mod.key, action)">
                                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-green-100 dark:bg-green-900/40">
                                                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                        </span>
                                                    </template>
                                                    {{-- Available as override --}}
                                                    <template x-if="mod.actions.includes(action) && !isRolePermission(mod.key, action)">
                                                        <label class="inline-flex items-center justify-center cursor-pointer">
                                                            <input type="checkbox"
                                                                   :checked="isOverride(mod.key, action)"
                                                                   @change="toggleOverride(mod.key, action)"
                                                                   class="w-5 h-5 rounded border-2 border-purple-300 dark:border-purple-600 text-purple-600 dark:text-purple-500 focus:ring-purple-500 dark:focus:ring-purple-600 bg-white dark:bg-gray-700 cursor-pointer">
                                                        </label>
                                                    </template>
                                                </td>
                                            </template>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-end gap-3">
                        <button type="button" @click="showModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Скасувати
                        </button>
                        <button type="button" @click="saveOverrides()"
                                :disabled="saving"
                                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-xl hover:bg-purple-700 disabled:opacity-50 transition-colors">
                            <span x-show="!saving">Зберегти</span>
                            <span x-show="saving" x-cloak>Збереження...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $modulesJson = collect(\App\Models\ChurchRolePermission::MODULES)->map(function($m, $k) {
            return ['key' => $k, 'label' => $m['label'], 'actions' => $m['actions']];
        })->values();
        $actionsJson = \App\Models\ChurchRolePermission::ACTIONS;
        $rolePermsJson = $user->churchRole ? $user->churchRole->getAllPermissions() : [];
        $overridesJson = $user->permission_overrides ?? (object)[];
    @endphp
    <script>
    function permissionOverrides() {
        return {
            showModal: false,
            saving: false,
            roleName: @json($user->churchRole?->name ?? 'Без ролі'),
            rolePermissions: @json($rolePermsJson),
            overrides: JSON.parse(@json(json_encode($overridesJson))),
            modules: @json($modulesJson),
            allActions: ['view', 'create', 'edit', 'delete'],
            actionLabels: @json($actionsJson),

            isRolePermission(module, action) {
                return (this.rolePermissions[module] || []).includes(action);
            },

            isOverride(module, action) {
                return (this.overrides[module] || []).includes(action);
            },

            toggleOverride(module, action) {
                if (!this.overrides[module]) {
                    this.overrides = { ...this.overrides, [module]: [action] };
                } else {
                    const idx = this.overrides[module].indexOf(action);
                    if (idx === -1) {
                        this.overrides = { ...this.overrides, [module]: [...this.overrides[module], action] };
                    } else {
                        const newActions = this.overrides[module].filter((_, i) => i !== idx);
                        if (newActions.length === 0) {
                            const { [module]: _, ...rest } = this.overrides;
                            this.overrides = rest;
                        } else {
                            this.overrides = { ...this.overrides, [module]: newActions };
                        }
                    }
                }
            },

            hasOverrides() {
                return Object.keys(this.overrides).length > 0;
            },

            getOverrideBadges() {
                const badges = [];
                for (const [module, actions] of Object.entries(this.overrides)) {
                    const mod = this.modules.find(m => m.key === module);
                    if (!mod) continue;
                    for (const action of actions) {
                        badges.push({
                            key: module + '_' + action,
                            label: mod.label + ': ' + (this.actionLabels[action] || action),
                        });
                    }
                }
                return badges;
            },

            async saveOverrides() {
                this.saving = true;
                try {
                    const res = await fetch('{{ route("settings.users.permissions.update", $user) }}', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ overrides: this.overrides }),
                    });
                    const data = await res.json();
                    if (res.ok) {
                        this.overrides = data.overrides || {};
                        this.showModal = false;
                    }
                } catch (e) {
                    console.error('Failed to save permissions', e);
                } finally {
                    this.saving = false;
                }
            },
        };
    }
    </script>
    @endif
</div>
@endsection
