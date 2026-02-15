<!-- Add Card Modal -->
<div x-show="addCardModal.open" x-cloak class="fixed inset-0 z-50 overflow-hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/40" @click="addCardModal.open = false" x-show="addCardModal.open"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    <!-- Panel -->
    <div class="absolute inset-y-0 right-0 flex max-w-full">
        <div x-show="addCardModal.open"
             x-transition:enter="transform transition ease-out duration-200"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in duration-150"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="w-screen max-w-xl">
            <div class="h-full bg-white dark:bg-gray-900 shadow-xl flex flex-col">
                <!-- Header -->
                <div class="flex-shrink-0 px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-900">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Нове завдання</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Створіть нову картку</p>
                            </div>
                        </div>
                        <button @click="addCardModal.open = false"
                                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Form Content -->
                <form @submit.prevent="submitNewCard()" class="flex-1 overflow-y-auto">
                    <div class="p-6 space-y-5">
                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Назва *</label>
                            <input type="text" x-model="addCardModal.title" x-ref="addCardTitle" required
                                   placeholder="Що потрібно зробити?"
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white text-lg">
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Опис</label>
                            <textarea x-model="addCardModal.description" rows="3"
                                      placeholder="Детальний опис завдання..."
                                      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white resize-none"></textarea>
                        </div>

                        <!-- Two columns -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Column -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Колонка</label>
                                <select x-model="addCardModal.columnId"
                                        class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white">
                                    @foreach($board->columns as $column)
                                        <option value="{{ $column->id }}">{{ $column->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Epic -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Проєкт</label>
                                <select x-model="addCardModal.epicId"
                                        class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white">
                                    <option value="">Без проєкту</option>
                                    <template x-for="epic in epics" :key="epic.id">
                                        <option :value="epic.id" x-text="epic.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Priority -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Пріоритет</label>
                            <div class="grid grid-cols-4 gap-2">
                                <label class="relative">
                                    <input type="radio" x-model="addCardModal.priority" value="low" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 cursor-pointer transition-all
                                                peer-checked:border-gray-500 peer-checked:bg-gray-50 dark:peer-checked:bg-gray-800
                                                border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Низький</span>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" x-model="addCardModal.priority" value="medium" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 cursor-pointer transition-all
                                                peer-checked:border-yellow-500 peer-checked:bg-yellow-50 dark:peer-checked:bg-yellow-900/20
                                                border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600">
                                        <span class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Середній</span>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" x-model="addCardModal.priority" value="high" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 cursor-pointer transition-all
                                                peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20
                                                border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600">
                                        <span class="text-sm font-medium text-orange-600 dark:text-orange-400">Високий</span>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" x-model="addCardModal.priority" value="urgent" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 cursor-pointer transition-all
                                                peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20
                                                border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600">
                                        <span class="text-sm font-medium text-red-600 dark:text-red-400">Терміново</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Two columns -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Ministry -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Команда</label>
                                <x-searchable-select
                                    name="ministry_id_temp"
                                    :items="$ministries"
                                    :selected="null"
                                    labelKey="name"
                                    valueKey="id"
                                    colorKey="color"
                                    placeholder="Пошук команди..."
                                    nullText="Без команди"
                                    nullable
                                    x-on:select-changed="addCardModal.ministryId = $event.detail.value || ''"
                                />
                            </div>

                            <!-- Due Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Дедлайн</label>
                                <input type="date" x-model="addCardModal.dueDate"
                                       class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white">
                            </div>
                        </div>

                        <!-- Assignee -->
                        <div x-data="{
                            open: false,
                            search: '',
                            dropdownStyle: {},
                            people: @js($people->map(fn($p) => ['id' => $p->id, 'name' => $p->full_name, 'photo' => $p->photo ? Storage::url($p->photo) : null])),
                            updatePosition() {
                                const btn = this.$el.querySelector('button');
                                if (!btn) return;
                                const rect = btn.getBoundingClientRect();
                                const spaceBelow = window.innerHeight - rect.bottom;
                                const openAbove = spaceBelow < 220 && rect.top > 220;
                                this.dropdownStyle = {
                                    left: rect.left + 'px',
                                    width: rect.width + 'px',
                                    ...(openAbove
                                        ? { bottom: (window.innerHeight - rect.top + 4) + 'px', top: 'auto' }
                                        : { top: (rect.bottom + 4) + 'px', bottom: 'auto' })
                                };
                            },
                            get filtered() {
                                if (!this.search) return this.people;
                                return this.people.filter(p => p.name.toLowerCase().includes(this.search.toLowerCase()));
                            },
                            get selectedPerson() {
                                return this.people.find(p => p.id == addCardModal.assignedTo);
                            },
                            select(id) {
                                addCardModal.assignedTo = id;
                                this.open = false;
                                this.search = '';
                            }
                        }">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Виконавець</label>
                            <div class="relative">
                                <button type="button" @click="updatePosition(); open = !open; $nextTick(() => open && $refs.searchInput.focus())"
                                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-left flex items-center justify-between dark:text-white">
                                    <span x-show="!selectedPerson" class="text-gray-500">Оберіть виконавця...</span>
                                    <template x-if="selectedPerson">
                                        <span class="flex items-center gap-2">
                                            <template x-if="selectedPerson.photo">
                                                <img :src="selectedPerson.photo" class="w-6 h-6 rounded-full object-cover">
                                            </template>
                                            <template x-if="!selectedPerson.photo">
                                                <span class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-xs text-primary-600 dark:text-primary-400" x-text="selectedPerson.name.charAt(0)"></span>
                                            </template>
                                            <span x-text="selectedPerson.name"></span>
                                        </span>
                                    </template>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="open" @click.away="open = false" x-transition
                                     :style="dropdownStyle"
                                     class="fixed z-[9999] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg overflow-hidden">
                                    <div class="p-2 border-b border-gray-200 dark:border-gray-700">
                                        <input type="text" x-model="search" x-ref="searchInput"
                                               placeholder="Пошук..."
                                               class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto">
                                        <button type="button" @click="select('')"
                                                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                                :class="!addCardModal.assignedTo ? 'bg-primary-50 dark:bg-primary-900/20' : ''">
                                            <span class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs">-</span>
                                            <span class="text-gray-500 dark:text-gray-400">Без виконавця</span>
                                        </button>
                                        <template x-for="person in filtered" :key="person.id">
                                            <button type="button" @click="select(person.id)"
                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-white flex items-center gap-2"
                                                    :class="addCardModal.assignedTo == person.id ? 'bg-primary-50 dark:bg-primary-900/20' : ''">
                                                <template x-if="person.photo">
                                                    <img :src="person.photo" class="w-6 h-6 rounded-full object-cover">
                                                </template>
                                                <template x-if="!person.photo">
                                                    <span class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-xs text-primary-600 dark:text-primary-400" x-text="person.name.charAt(0)"></span>
                                                </template>
                                                <span x-text="person.name"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex-shrink-0 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" @click="addCardModal.open = false"
                                    class="px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                Скасувати
                            </button>
                            <button type="submit" :disabled="addCardModal.loading || !addCardModal.title.trim()"
                                    class="px-6 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors disabled:opacity-50 flex items-center gap-2">
                                <template x-if="addCardModal.loading">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Створити
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
