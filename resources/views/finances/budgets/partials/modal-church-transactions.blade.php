{{-- Church Budget Transactions Modal --}}
<div x-show="showChurchTransModal"
     x-cloak
     x-on:keydown.escape.window="showChurchTransModal = false"
     class="fixed inset-0 z-50 overflow-y-auto"
     x-transition:enter="ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" x-on:click="showChurchTransModal = false"></div>

        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[80vh] flex flex-col"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-on:click.stop>
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('app.matched_transactions') }}: <span x-text="churchTransItemName"></span>
                </h3>
                <button x-on:click="showChurchTransModal = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <div x-show="churchTransLoading" class="text-center py-8">
                    <svg class="animate-spin h-8 w-8 mx-auto text-primary-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>

                <div x-show="!churchTransLoading && churchTransList.length === 0" class="text-center py-8 text-gray-500">
                    {{ __('app.no_matched_transactions') }}
                </div>

                <div x-show="!churchTransLoading && churchTransList.length > 0" class="space-y-3">
                    <template x-for="tx in churchTransList" :key="tx.id">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white" x-text="tx.description || '{{ __('common.no_description') }}'"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <span x-text="tx.date"></span>
                                        <template x-if="tx.category">
                                            <span> &bull; <span x-text="tx.category"></span></span>
                                        </template>
                                        <template x-if="tx.payment_method">
                                            <span> &bull; <span x-text="tx.payment_method"></span></span>
                                        </template>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-red-600 dark:text-red-400" x-text="formatMoney(tx.amount) + ' ₴'"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
