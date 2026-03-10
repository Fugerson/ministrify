{{-- Church Budget Item Create/Edit Modal --}}
<div x-show="showChurchItemModal"
     x-cloak
     x-on:keydown.escape.window="showChurchItemModal = false"
     class="fixed inset-0 z-50 overflow-y-auto"
     x-transition:enter="ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" x-on:click="showChurchItemModal = false"></div>

        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-on:click.stop>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                x-text="churchItemMode === 'create' ? @js( __('app.add_budget_item') ) : @js( __('app.edit_budget_item') )"></h3>

            <form x-on:submit.prevent="saveChurchItem()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" title="{{ __('app.budget_item_name_tooltip') }}">{{ __('app.budget_item_name') }} *</label>
                    <input type="text" x-model="churchItemForm.name" required maxlength="255"
                           placeholder="{{ __('app.budget_item_name') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" title="{{ __('app.budget_category_tooltip') }}">{{ __('app.category') }}</label>
                    <select x-model="churchItemForm.category_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                        <option value="">{{ __('app.no_category') }}</option>
                        @foreach($expenseCategories ?? [] as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->icon_emoji ?? '💸' }} {{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('app.category') }} — {{ __('app.actual') }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" title="{{ __('app.budget_type_tooltip') }}">{{ __('app.budget_item_type') }} *</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer" title="{{ __('app.budget_recurring_tooltip') }}">
                            <input type="radio" x-model="churchItemForm.is_recurring" value="1"
                                   class="text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">🔄 {{ __('app.monthly') }}</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer" title="{{ __('app.budget_onetime_tooltip') }}">
                            <input type="radio" x-model="churchItemForm.is_recurring" value="0"
                                   class="text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">☀️ {{ __('app.one_time') }}</span>
                        </label>
                    </div>
                </div>

                {{-- Recurring: amount per month --}}
                <div x-show="churchItemForm.is_recurring == '1'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" title="{{ __('app.budget_amount_monthly_tooltip') }}">{{ __('app.amount_per_month') }} (₴) *</label>
                    <input type="number" x-model="churchItemForm.amount" min="0" step="1"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                </div>

                {{-- One-time: month + amount --}}
                <div x-show="churchItemForm.is_recurring == '0'" class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" title="{{ __('app.budget_onetime_month_tooltip') }}">{{ __('app.select_month') }} *</label>
                        <select x-model="churchItemForm.one_time_month"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            @foreach([1 => __('app.january'), 2 => __('app.february'), 3 => __('app.march'), 4 => __('app.april'), 5 => __('app.may'), 6 => __('app.june'), 7 => __('app.july'), 8 => __('app.august'), 9 => __('app.september'), 10 => __('app.october'), 11 => __('app.november'), 12 => __('app.december')] as $m => $name)
                                <option value="{{ $m }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.amount') }} (₴) *</label>
                        <input type="number" x-model="churchItemForm.one_time_amount" min="0" step="1"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.notes') }}</label>
                    <textarea x-model="churchItemForm.notes" rows="2" maxlength="500"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" x-on:click="showChurchItemModal = false"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        {{ __('ui.cancel') }}
                    </button>
                    <button type="submit" :disabled="churchItemSaving"
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                        <span x-show="!churchItemSaving" x-text="churchItemMode === 'create' ? @js( __('ui.add') ) : @js( __('ui.save') )"></span>
                        <span x-show="churchItemSaving">{{ __('app.saving') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
