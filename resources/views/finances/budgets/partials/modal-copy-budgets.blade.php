{{-- Copy All Budgets Modal --}}
<div x-show="showCopyModal"
     x-cloak
     x-on:keydown.escape.window="showCopyModal = false"
     class="fixed inset-0 z-50 overflow-y-auto"
     x-transition:enter="ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" x-on:click="showCopyModal = false"></div>

        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-on:click.stop>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.copy_team_budgets') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                {{ __('app.copy_budgets_desc') }}
            </p>

            <form x-on:submit.prevent="submitCopyBudgets()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.from_label') }}</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm text-gray-700 dark:text-gray-300">
                        <span x-text="monthNames[month - 1]"></span> <span x-text="year"></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.to_month_label') }}</label>
                        <select x-model="copyToMonth"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @foreach([1 => __('app.january'), 2 => __('app.february'), 3 => __('app.march'), 4 => __('app.april'), 5 => __('app.may'), 6 => __('app.june'), 7 => __('app.july'), 8 => __('app.august'), 9 => __('app.september'), 10 => __('app.october'), 11 => __('app.november'), 12 => __('app.december')] as $m => $name)
                                <option value="{{ $m }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.year_label') }}</label>
                        <select x-model="copyToYear"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @for($y = now()->year + 1; $y >= 2020; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" x-on:click="showCopyModal = false"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        {{ __('app.cancel') }}
                    </button>
                    <button type="submit" :disabled="copySaving"
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                        <span x-show="!copySaving">{{ __('app.copy') }}</span>
                        <span x-show="copySaving">{{ __('app.copying') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
