<!-- Epic Modal -->
<div x-show="showEpicModal" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     @keydown.escape.window="showEpicModal = false; editingEpic = null">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showEpicModal = false; editingEpic = null"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md z-10 p-6"
             x-show="showEpicModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span x-text="editingEpic ? 'Редагувати проєкт' : 'Новий проєкт'"></span>
                </h3>
                <button @click="showEpicModal = false; editingEpic = null" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="editingEpic ? updateEpic() : createEpic()">
                <div class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва *</label>
                        <input type="text" x-model="newEpic.name" required
                               placeholder="Наприклад: Q1 2026 Goals"
                               class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>

                    <!-- Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Колір</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="newEpic.color"
                                   class="w-10 h-10 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer">
                            <div class="flex gap-1">
                                @foreach(['#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f97316', '#eab308', '#22c55e', '#14b8a6', '#3b82f6', '#64748b'] as $color)
                                    <button type="button" @click="newEpic.color = '{{ $color }}'"
                                            class="w-6 h-6 rounded-full transition-transform hover:scale-110"
                                            :class="newEpic.color === '{{ $color }}' ? 'ring-2 ring-offset-2 ring-primary-500' : ''"
                                            style="background-color: {{ $color }}"></button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис</label>
                        <textarea x-model="newEpic.description" rows="2"
                                  placeholder="Короткий опис цілей проєкту..."
                                  class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white resize-none"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="showEpicModal = false; editingEpic = null"
                            class="px-4 py-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 font-medium">
                        Скасувати
                    </button>
                    <button type="submit" :disabled="!newEpic.name.trim() || epicModalLoading"
                            class="px-5 py-2 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 disabled:opacity-50 flex items-center gap-2">
                        <template x-if="epicModalLoading">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <template x-if="!epicModalLoading">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x-bind:d="editingEpic ? 'M5 13l4 4L19 7' : 'M12 4v16m8-8H4'"/>
                            </svg>
                        </template>
                        <span x-text="editingEpic ? 'Зберегти' : 'Створити'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
