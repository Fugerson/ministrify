{{-- Budget Item Create/Edit Modal --}}
<div x-show="showItemModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     x-transition:enter="ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="showItemModal = false"></div>

        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             @click.stop>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4" x-text="itemModalTitle"></h3>

            <form @submit.prevent="saveItem()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва *</label>
                    <input type="text" x-model="itemForm.name" required maxlength="255"
                           placeholder="Наприклад: Оренда, Перекуси, Матеріали..."
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Запланована сума (₴) *</label>
                    <input type="number" x-model="itemForm.planned_amount" required min="0" step="0.01"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія витрат</label>
                    <select x-model="itemForm.category_id"
                            :class="{ 'hidden': itemForm.category_id === '__custom__' }"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                        <option value="">Без категорії (ручна прив'язка)</option>
                        @foreach($expenseCategories ?? [] as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->icon_emoji ?? '💸' }} {{ $cat->name }}</option>
                        @endforeach
                        <option value="__custom__">Інше (ввести вручну)...</option>
                    </select>
                    <div x-show="itemForm.category_id === '__custom__'" class="flex gap-2">
                        <input type="text" x-model="itemForm.category_name" placeholder="Назва категорії..."
                               class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                        <button type="button" @click="itemForm.category_id = ''; itemForm.category_name = ''"
                                class="px-3 py-2 text-gray-500 hover:text-red-500 border border-gray-300 dark:border-gray-600 rounded-lg">✕</button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Вибір категорії дозволяє автоматично збирати витрати</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Відповідальні</label>
                    <div class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 max-h-40 overflow-y-auto p-2 space-y-1">
                        <template x-for="person in ministryMembers" :key="person.id">
                            <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">
                                <input type="checkbox" :value="person.id"
                                       x-model="itemForm.person_ids"
                                       class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300" x-text="person.name"></span>
                            </label>
                        </template>
                        <p x-show="ministryMembers.length === 0" class="text-xs text-gray-400 py-2 text-center">Завантаження учасників...</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нотатки</label>
                    <textarea x-model="itemForm.notes" rows="2" maxlength="500"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showItemModal = false"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        Скасувати
                    </button>
                    <button type="submit" :disabled="itemSaving"
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                        <span x-show="!itemSaving" x-text="itemModalMode === 'create' ? 'Додати' : 'Зберегти'"></span>
                        <span x-show="itemSaving">Збереження...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
