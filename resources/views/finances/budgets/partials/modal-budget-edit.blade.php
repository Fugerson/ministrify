{{-- Budget Edit Modal --}}
<div x-show="showBudgetModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     x-transition:enter="ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="showBudgetModal = false"></div>

        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Бюджет: <span x-text="budgetMinistryName"></span>
            </h3>

            <form @submit.prevent="saveBudget()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" title="Максимальна сума, яку команда може витратити за місяць. Якщо є статті — бюджет рахується як їх сума">
                        Місячний бюджет (₴)
                    </label>
                    <input type="number" name="monthly_budget" x-model="budgetAmount" min="0" step="100"
                           :disabled="budgetHasItems"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <p x-show="budgetHasItems" class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                        Бюджет розраховується як сума статей витрат
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Нотатки
                    </label>
                    <textarea name="notes" x-model="budgetNotes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showBudgetModal = false"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        Скасувати
                    </button>
                    <button type="submit" :disabled="budgetSaving || budgetHasItems"
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                        <span x-show="!budgetSaving">Зберегти</span>
                        <span x-show="budgetSaving">Збереження...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
