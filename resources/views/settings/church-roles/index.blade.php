@extends('layouts.app')

@section('title', 'Церковні ролі')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="churchRolesManager()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Церковні ролі</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Налаштуйте ролі для членів вашої церкви</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="resetToDefaults()"
                    class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                Скинути до стандартних
            </button>
        </div>
    </div>

    <!-- Roles List -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="divide-y divide-gray-100 dark:divide-gray-700" x-ref="rolesList">
            <template x-if="roles.length === 0">
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    Немає ролей. Додайте першу роль нижче.
                </div>
            </template>
            <template x-for="role in roles" :key="role.id">
                <div class="flex items-center gap-4 p-4 group hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                     :data-role-id="role.id"
                     x-data="{ editing: false }">

                    <!-- Drag Handle -->
                    <div class="cursor-move text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 drag-handle">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                        </svg>
                    </div>

                    <!-- Color Picker -->
                    <div class="relative">
                        <input type="color" x-model="role.color" @change="saveRole(role.id, role.name, role.color)"
                               class="w-8 h-8 rounded-lg cursor-pointer border-0 p-0">
                    </div>

                    <!-- Name -->
                    <div class="flex-1">
                        <template x-if="!editing">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-900 dark:text-white" @dblclick="editing = true" x-text="role.name"></span>
                                <template x-if="role.is_admin_role">
                                    <span class="px-2 py-0.5 text-xs bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 rounded-full flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        Повний доступ
                                    </span>
                                </template>
                                <template x-if="role.is_default">
                                    <span class="px-2 py-0.5 text-xs bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 rounded-full">
                                        за замовчуванням
                                    </span>
                                </template>
                            </div>
                        </template>
                        <template x-if="editing">
                            <input type="text" x-model="role.name"
                                   @keydown.enter="saveRole(role.id, role.name, role.color); editing = false"
                                   @keydown.escape="editing = false"
                                   @blur="saveRole(role.id, role.name, role.color); editing = false"
                                   x-ref="nameInput"
                                   x-init="$nextTick(() => { if(editing) $el.focus() })"
                                   class="px-2 py-1 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                        </template>
                    </div>

                    <!-- People Count -->
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <span x-text="role.people_count"></span> людей
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <!-- Toggle Admin -->
                        <button @click="toggleAdmin(role.id)"
                                class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600"
                                :class="role.is_admin_role ? 'text-red-600 dark:text-red-400' : 'text-gray-400 hover:text-red-600 dark:hover:text-red-400'"
                                :title="role.is_admin_role ? 'Прибрати повний доступ' : 'Надати повний доступ'">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        </button>
                        <!-- Permissions -->
                        <template x-if="!role.is_admin_role">
                            <button @click="openPermissions(role.id, role.name)"
                                    class="p-2 text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600"
                                    title="Налаштувати права доступу">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </button>
                        </template>
                        <button @click="editing = true; $nextTick(() => $el.previousElementSibling?.previousElementSibling?.querySelector('input')?.focus())"
                                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </button>
                        <template x-if="!role.is_default">
                            <button @click="setDefault(role.id)"
                                    class="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600"
                                    title="Зробити за замовчуванням">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            </button>
                        </template>
                        <button @click="deleteRole(role.id, role.name)"
                                class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Add New Role -->
        <div class="border-t border-gray-100 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-700/50">
            <div class="flex items-center gap-4">
                <input type="color" x-model="newColor" class="w-8 h-8 rounded-lg cursor-pointer border-0 p-0">
                <input type="text" x-model="newName"
                       placeholder="Назва нової ролі..."
                       @keydown.enter="if(newName.trim()) { addRole(newName, newColor); }"
                       class="flex-1 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400">
                <button @click="if(newName.trim()) { addRole(newName, newColor); }"
                        :disabled="!newName.trim()"
                        class="px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Додати
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
                <p class="font-medium">Підказки:</p>
                <ul class="mt-1 list-disc list-inside space-y-1 text-blue-700 dark:text-blue-300">
                    <li>Перетягуйте ролі для зміни порядку</li>
                    <li>Двічі клікніть на назву для редагування</li>
                    <li>Роль "за замовчуванням" буде обрана автоматично для нових людей</li>
                </ul>
            </div>
        </div>
    </div>

    <a href="{{ route('settings.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад до налаштувань
    </a>

    <!-- Permissions Modal -->
    <template x-teleport="body">
        <div x-show="showPermissionsModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/50" @click="showPermissionsModal = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Права доступу: <span x-text="permissionsRoleName"></span>
                        </h3>
                        <button @click="showPermissionsModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="overflow-x-auto max-h-96">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                                    <th class="pb-2 font-medium text-gray-900 dark:text-white">Модуль</th>
                                    <th class="pb-2 font-medium text-gray-900 dark:text-white text-center w-20">Перегляд</th>
                                    <th class="pb-2 font-medium text-gray-900 dark:text-white text-center w-20">Створ.</th>
                                    <th class="pb-2 font-medium text-gray-900 dark:text-white text-center w-20">Редаг.</th>
                                    <th class="pb-2 font-medium text-gray-900 dark:text-white text-center w-20">Видал.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="(module, key) in modules" :key="key">
                                    <tr>
                                        <td class="py-2 text-gray-700 dark:text-gray-300" x-text="module.label"></td>
                                        <template x-for="action in ['view', 'create', 'edit', 'delete']" :key="action">
                                            <td class="py-2 text-center">
                                                <input type="checkbox"
                                                       :checked="permissions[key]?.includes(action)"
                                                       @change="togglePermission(key, action)"
                                                       class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="showPermissionsModal = false"
                                class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            Скасувати
                        </button>
                        <button @click="savePermissions()"
                                :disabled="savingPermissions"
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 flex items-center gap-2">
                            <svg x-show="savingPermissions" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="savingPermissions ? 'Збереження...' : 'Зберегти'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function churchRolesManager() {
    return {
        roles: @json($roles->map(fn($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'color' => $r->color,
            'is_admin_role' => $r->is_admin_role,
            'is_default' => $r->is_default,
            'people_count' => $r->people_count ?? $r->people()->count(),
        ])),
        newName: '',
        newColor: '#6b7280',
        showPermissionsModal: false,
        permissionsRoleId: null,
        permissionsRoleName: '',
        permissions: {},
        savingPermissions: false,
        modules: {
            dashboard: { label: 'Головна' },
            people: { label: 'Люди' },
            groups: { label: 'Домашні групи' },
            ministries: { label: 'Служіння' },
            events: { label: 'Розклад' },
            finances: { label: 'Фінанси' },
            reports: { label: 'Звіти' },
            resources: { label: 'Ресурси' },
            boards: { label: 'Дошки завдань' },
            announcements: { label: 'Комунікації' },
            website: { label: 'Веб-сайт' },
            settings: { label: 'Налаштування' }
        },

        init() {
            this.$nextTick(() => this.initSortable());
        },

        initSortable() {
            const list = this.$refs.rolesList;
            if (list && typeof Sortable !== 'undefined') {
                new Sortable(list, {
                    animation: 150,
                    handle: '.drag-handle',
                    draggable: '[data-role-id]',
                    onEnd: (evt) => {
                        // Reorder roles array based on DOM order
                        const items = list.querySelectorAll('[data-role-id]');
                        const newOrder = Array.from(items).map(item => parseInt(item.dataset.roleId));
                        this.roles.sort((a, b) => newOrder.indexOf(a.id) - newOrder.indexOf(b.id));
                        this.saveOrder();
                    }
                });
            }
        },

        async toggleAdmin(id) {
            try {
                const response = await fetch(`/settings/church-roles/${id}/toggle-admin`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    const role = this.roles.find(r => r.id === id);
                    if (role) {
                        role.is_admin_role = data.is_admin_role;
                    }
                    if (window.showGlobalToast) showGlobalToast('Статус оновлено', 'success');
                } else {
                    const data = await response.json();
                    alert(data.message || 'Помилка');
                }
            } catch (error) {
                alert('Помилка з\'єднання');
            }
        },

        async openPermissions(id, name) {
            this.permissionsRoleId = id;
            this.permissionsRoleName = name;

            try {
                const response = await fetch(`/settings/church-roles/${id}/permissions`, {
                    headers: { 'Accept': 'application/json' }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.permissions = data.permissions;
                    this.showPermissionsModal = true;
                }
            } catch (error) {
                alert('Помилка завантаження прав');
            }
        },

        togglePermission(module, action) {
            if (!this.permissions[module]) {
                this.permissions[module] = [];
            }

            const idx = this.permissions[module].indexOf(action);
            if (idx > -1) {
                this.permissions[module].splice(idx, 1);
            } else {
                this.permissions[module].push(action);
            }
        },

        async savePermissions() {
            this.savingPermissions = true;

            try {
                const response = await fetch(`/settings/church-roles/${this.permissionsRoleId}/permissions`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ permissions: this.permissions })
                });

                if (response.ok) {
                    this.showPermissionsModal = false;
                    if (window.showGlobalToast) showGlobalToast('Права збережено', 'success');
                } else {
                    const data = await response.json();
                    alert(data.message || 'Помилка збереження');
                }
            } catch (error) {
                alert('Помилка з\'єднання');
            } finally {
                this.savingPermissions = false;
            }
        },

        async addRole(name, color) {
            try {
                const response = await fetch('{{ route("settings.church-roles.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name, color })
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.role) {
                        this.roles.push(data.role);
                    }
                    this.newName = '';
                    this.newColor = '#6b7280';
                    if (window.showGlobalToast) showGlobalToast('Роль додано', 'success');
                } else {
                    const data = await response.json();
                    alert(data.message || 'Помилка при додаванні');
                }
            } catch (error) {
                alert('Помилка з\'єднання');
            }
        },

        async saveRole(id, name, color) {
            try {
                await fetch(`/settings/church-roles/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name, color })
                });
            } catch (error) {
                console.error('Error saving role:', error);
            }
        },

        async deleteRole(id, name) {
            if (!confirm(`Видалити роль "${name}"?`)) return;

            try {
                const response = await fetch(`/settings/church-roles/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.roles = this.roles.filter(r => r.id !== id);
                    if (window.showGlobalToast) showGlobalToast('Роль видалено', 'success');
                } else {
                    const data = await response.json();
                    alert(data.message || 'Помилка при видаленні');
                }
            } catch (error) {
                alert('Помилка з\'єднання');
            }
        },

        async setDefault(id) {
            try {
                const response = await fetch(`/settings/church-roles/${id}/set-default`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    // Update all roles - remove default, set new default
                    this.roles.forEach(r => r.is_default = (r.id === id));
                    if (window.showGlobalToast) showGlobalToast('За замовчуванням оновлено', 'success');
                }
            } catch (error) {
                console.error('Error setting default:', error);
            }
        },

        async saveOrder() {
            const order = this.roles.map(r => r.id);

            try {
                await fetch('{{ route("settings.church-roles.reorder") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ order })
                });
            } catch (error) {
                console.error('Error saving order:', error);
            }
        },

        async resetToDefaults() {
            if (!confirm('Скинути всі ролі до стандартних? Це можливо лише якщо жодна роль не використовується.')) return;

            try {
                const response = await fetch('{{ route("settings.church-roles.reset") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    // Reload page as this is a complete reset
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert(data.message || 'Помилка при скиданні');
                }
            } catch (error) {
                alert('Помилка з\'єднання');
            }
        }
    }
}
</script>
@endpush
@endsection
