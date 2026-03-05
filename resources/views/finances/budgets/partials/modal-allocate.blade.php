{{-- Allocate Budget Modal --}}
<div x-show="showAllocateModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     x-transition:enter="ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="showAllocateModal = false"></div>

        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             @click.stop>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Виділити бюджет: <span x-text="allocateMinistryName"></span>
            </h3>

            <form @submit.prevent="submitAllocation()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" title="Сума, яка буде переведена з церковного рахунку на рахунок команди">
                        Сума *
                    </label>
                    <div class="flex gap-2">
                        <input type="number" x-model="allocateForm.amount" required min="0.01" step="0.01"
                               placeholder="0.00"
                               class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                        <select x-model="allocateForm.currency"
                                class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                            @foreach($enabledCurrencies as $currency)
                                <option value="{{ $currency }}">{{ $currency }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Спосіб оплати
                    </label>
                    <select x-model="allocateForm.payment_method"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                        <option value="">Не вказано</option>
                        <option value="cash">Готівка</option>
                        <option value="card">Картка</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Дата *
                    </label>
                    <input type="date" x-model="allocateForm.date" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Нотатки
                    </label>
                    <textarea x-model="allocateForm.notes" rows="2" maxlength="500"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showAllocateModal = false"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        Скасувати
                    </button>
                    <button type="submit" :disabled="allocateSaving"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                        <span x-show="!allocateSaving">Виділити</span>
                        <span x-show="allocateSaving">Збереження...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
