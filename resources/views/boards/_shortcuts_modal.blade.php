<!-- Keyboard Shortcuts Modal -->
<div x-show="showShortcuts" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     @keydown.escape.window="showShortcuts = false">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showShortcuts = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg z-10 p-6"
             x-show="showShortcuts"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Клавіатурні скорочення
                </h3>
                <button @click="showShortcuts = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <!-- Global Shortcuts -->
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Глобальні</h4>
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Пошук</span>
                            <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">/</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Нове завдання</span>
                            <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">N</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Ця довідка</span>
                            <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">?</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Закрити модальне вікно</span>
                            <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">Esc</kbd>
                        </div>
                    </div>
                </div>

                <!-- Card Panel Shortcuts -->
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">При відкритій картці</h4>
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Завершити / Відновити</span>
                            <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">C</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Перемістити в колонку</span>
                            <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">M</kbd>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        <span class="font-medium">Tip:</span> Використовуйте drag & drop для переміщення карток між колонками
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
