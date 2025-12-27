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
            @forelse($roles as $role)
            <div class="flex items-center gap-4 p-4 group hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                 data-role-id="{{ $role->id }}"
                 x-data="{ editing: false, name: '{{ $role->name }}', color: '{{ $role->color }}', saving: false }">

                <!-- Drag Handle -->
                <div class="cursor-move text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 drag-handle">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                    </svg>
                </div>

                <!-- Color Picker -->
                <div class="relative">
                    <input type="color" x-model="color" @change="saveRole({{ $role->id }}, name, color)"
                           class="w-8 h-8 rounded-lg cursor-pointer border-0 p-0">
                </div>

                <!-- Name -->
                <div class="flex-1">
                    <template x-if="!editing">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-900 dark:text-white" @dblclick="editing = true">{{ $role->name }}</span>
                            @if($role->is_default)
                            <span class="px-2 py-0.5 text-xs bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 rounded-full">
                                за замовчуванням
                            </span>
                            @endif
                        </div>
                    </template>
                    <template x-if="editing">
                        <input type="text" x-model="name"
                               @keydown.enter="saveRole({{ $role->id }}, name, color); editing = false"
                               @keydown.escape="editing = false; name = '{{ $role->name }}'"
                               @blur="saveRole({{ $role->id }}, name, color); editing = false"
                               x-ref="nameInput"
                               x-init="$nextTick(() => { if(editing) $refs.nameInput.focus() })"
                               class="px-2 py-1 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </template>
                </div>

                <!-- People Count -->
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $role->people_count ?? $role->people()->count() }} людей
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button @click="editing = true; $nextTick(() => $refs.nameInput?.focus())"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </button>
                    @if(!$role->is_default)
                    <button @click="setDefault({{ $role->id }})"
                            class="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600"
                            title="Зробити за замовчуванням">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </button>
                    @endif
                    <button @click="deleteRole({{ $role->id }}, '{{ $role->name }}')"
                            class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                Немає ролей. Додайте першу роль нижче.
            </div>
            @endforelse
        </div>

        <!-- Add New Role -->
        <div class="border-t border-gray-100 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-700/50">
            <div class="flex items-center gap-4" x-data="{ newName: '', newColor: '#6b7280', adding: false }">
                <input type="color" x-model="newColor" class="w-8 h-8 rounded-lg cursor-pointer border-0 p-0">
                <input type="text" x-model="newName"
                       placeholder="Назва нової ролі..."
                       @keydown.enter="if(newName.trim()) { addRole(newName, newColor); newName = ''; newColor = '#6b7280'; }"
                       class="flex-1 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400">
                <button @click="if(newName.trim()) { addRole(newName, newColor); newName = ''; newColor = '#6b7280'; }"
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
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function churchRolesManager() {
    return {
        init() {
            // Initialize drag and drop
            const list = this.$refs.rolesList;
            if (list && typeof Sortable !== 'undefined') {
                new Sortable(list, {
                    animation: 150,
                    handle: '.drag-handle',
                    onEnd: (evt) => {
                        this.saveOrder();
                    }
                });
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
                    window.location.reload();
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
                    window.location.reload();
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
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error setting default:', error);
            }
        },

        async saveOrder() {
            const items = this.$refs.rolesList.querySelectorAll('[data-role-id]');
            const order = Array.from(items).map(item => parseInt(item.dataset.roleId));

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
