@extends('layouts.app')

@section('title', 'Швидке редагування')

@section('content')
<div x-data="quickEdit()" class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('people.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm mb-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Назад до списку
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Швидке редагування</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Редагуйте дані прямо в таблиці, як в Excel</p>
        </div>
        <div class="flex items-center gap-3">
            <span x-show="hasChanges" class="text-sm text-amber-600 dark:text-amber-400 flex items-center gap-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <circle cx="10" cy="10" r="5"/>
                </svg>
                Є незбережені зміни
            </span>
            <button @click="saveAll()" :disabled="saving || !hasChanges"
                    class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-400 text-white rounded-xl font-medium transition-colors">
                <svg x-show="!saving" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg x-show="saving" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="saving ? 'Зберігаю...' : 'Зберегти все'"></span>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" x-model="searchQuery" placeholder="Пошук..."
                       class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20">
            </div>
            <select x-model="filterMinistry" class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20">
                <option value="">Всі команди</option>
                @foreach($ministries as $ministry)
                <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                @endforeach
            </select>
            <button @click="addNewRow()" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-medium transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Новий рядок
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50 sticky top-0 z-10">
                    <tr>
                        <th class="px-2 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-10">#</th>
                        <th class="px-2 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase min-w-[140px]">Ім'я</th>
                        <th class="px-2 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase min-w-[140px]">Прізвище</th>
                        <th class="px-2 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase min-w-[140px]">Телефон</th>
                        <th class="px-2 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase min-w-[180px]">Email</th>
                        <th class="px-2 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase min-w-[120px]">Дата народж.</th>
                        <th class="px-2 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase min-w-[100px]">Стать</th>
                        <th class="px-2 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase min-w-[150px]">Команда</th>
                        <th class="px-2 py-3 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <template x-for="(row, index) in filteredRows" :key="row.id || row.tempId">
                        <tr :class="{'bg-green-50 dark:bg-green-900/20': row.isNew, 'bg-amber-50 dark:bg-amber-900/20': row.isDirty && !row.isNew}"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <!-- Row Number -->
                            <td class="px-2 py-1 text-gray-400 text-xs" x-text="index + 1"></td>

                            <!-- First Name -->
                            <td class="px-1 py-1">
                                <input type="text" x-model="row.first_name" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors"
                                       placeholder="Ім'я">
                            </td>

                            <!-- Last Name -->
                            <td class="px-1 py-1">
                                <input type="text" x-model="row.last_name" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors"
                                       placeholder="Прізвище">
                            </td>

                            <!-- Phone -->
                            <td class="px-1 py-1">
                                <input type="tel" x-model="row.phone" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors"
                                       placeholder="+380...">
                            </td>

                            <!-- Email -->
                            <td class="px-1 py-1">
                                <input type="email" x-model="row.email" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors"
                                       placeholder="email@...">
                            </td>

                            <!-- Birth Date -->
                            <td class="px-1 py-1">
                                <input type="date" x-model="row.birth_date" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors">
                            </td>

                            <!-- Gender -->
                            <td class="px-1 py-1">
                                <select x-model="row.gender" @change="markDirty(row)"
                                        class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors">
                                    <option value="">—</option>
                                    <option value="male">Ч</option>
                                    <option value="female">Ж</option>
                                </select>
                            </td>

                            <!-- Ministry -->
                            <td class="px-1 py-1">
                                <select x-model="row.ministry_id" @change="markDirty(row)"
                                        class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors">
                                    <option value="">—</option>
                                    @foreach($ministries as $ministry)
                                    <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <!-- Actions -->
                            <td class="px-2 py-1">
                                <button @click="deleteRow(row)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div x-show="filteredRows.length === 0" class="p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 mb-4">Немає даних для відображення</p>
            <button @click="addNewRow()" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Додати першу людину
            </button>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Всього: <span x-text="rows.length"></span> |
                Нових: <span x-text="rows.filter(r => r.isNew).length"></span> |
                Змінено: <span x-text="rows.filter(r => r.isDirty && !r.isNew).length"></span>
            </div>
            <div class="flex items-center gap-2">
                <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">Tab</kbd>
                <span class="text-xs text-gray-500 dark:text-gray-400">для переходу між полями</span>
            </div>
        </div>
    </div>

    <!-- Success Toast -->
    <div x-show="showToast" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50">
        <div class="bg-green-600 text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span x-text="toastMessage"></span>
        </div>
    </div>
</div>

<script>
function quickEdit() {
    return {
        rows: @json($rows),
        searchQuery: '',
        filterMinistry: '',
        saving: false,
        showToast: false,
        toastMessage: '',
        tempIdCounter: 0,

        get filteredRows() {
            return this.rows.filter(row => {
                if (row.isDeleted) return false;

                // Search filter
                if (this.searchQuery) {
                    const query = this.searchQuery.toLowerCase();
                    const searchText = [row.first_name, row.last_name, row.phone, row.email].filter(Boolean).join(' ').toLowerCase();
                    if (!searchText.includes(query)) return false;
                }

                // Ministry filter
                if (this.filterMinistry && row.ministry_id != this.filterMinistry) {
                    return false;
                }

                return true;
            });
        },

        get hasChanges() {
            return this.rows.some(r => r.isDirty || r.isNew || r.isDeleted);
        },

        markDirty(row) {
            row.isDirty = true;
        },

        addNewRow() {
            this.tempIdCounter++;
            this.rows.unshift({
                tempId: 'new_' + this.tempIdCounter,
                first_name: '',
                last_name: '',
                phone: '',
                email: '',
                birth_date: '',
                gender: '',
                ministry_id: '',
                isDirty: false,
                isNew: true,
                isDeleted: false,
            });

            // Focus on first input of new row
            this.$nextTick(() => {
                const firstInput = this.$el.querySelector('tbody tr:first-child input');
                if (firstInput) firstInput.focus();
            });
        },

        deleteRow(row) {
            if (row.isNew) {
                // Remove new row completely
                const index = this.rows.indexOf(row);
                if (index > -1) this.rows.splice(index, 1);
            } else {
                // Mark existing row as deleted
                row.isDeleted = true;
                row.isDirty = true;
            }
        },

        async saveAll() {
            this.saving = true;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            // Prepare data
            const toCreate = this.rows.filter(r => r.isNew && !r.isDeleted && (r.first_name || r.last_name));
            const toUpdate = this.rows.filter(r => r.isDirty && !r.isNew && !r.isDeleted && r.id);
            const toDelete = this.rows.filter(r => r.isDeleted && r.id);

            try {
                const response = await fetch('{{ route("people.quick-save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        create: toCreate.map(r => ({
                            first_name: r.first_name,
                            last_name: r.last_name,
                            phone: r.phone,
                            email: r.email,
                            birth_date: r.birth_date || null,
                            gender: r.gender || null,
                            ministry_id: r.ministry_id || null,
                        })),
                        update: toUpdate.map(r => ({
                            id: r.id,
                            first_name: r.first_name,
                            last_name: r.last_name,
                            phone: r.phone,
                            email: r.email,
                            birth_date: r.birth_date || null,
                            gender: r.gender || null,
                            ministry_id: r.ministry_id || null,
                        })),
                        delete: toDelete.map(r => r.id),
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Update created rows with real IDs
                    if (data.created) {
                        data.created.forEach((created, index) => {
                            const row = toCreate[index];
                            if (row) {
                                row.id = created.id;
                                row.isNew = false;
                                row.isDirty = false;
                                delete row.tempId;
                            }
                        });
                    }

                    // Clear dirty flags for updated rows
                    toUpdate.forEach(r => r.isDirty = false);

                    // Remove deleted rows
                    this.rows = this.rows.filter(r => !r.isDeleted);

                    this.toast(`Збережено! Створено: ${data.stats.created}, Оновлено: ${data.stats.updated}, Видалено: ${data.stats.deleted}`);
                } else {
                    alert(data.message || 'Помилка збереження');
                }
            } catch (error) {
                console.error('Save error:', error);
                alert('Помилка збереження');
            } finally {
                this.saving = false;
            }
        },

        toast(message) {
            this.toastMessage = message;
            this.showToast = true;
            setTimeout(() => this.showToast = false, 4000);
        }
    };
}
</script>
@endsection
