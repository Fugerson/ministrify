{{-- Shared Income Modal (create + edit) --}}
{{-- Required variables: $incomeCategories, $enabledCurrencies, $exchangeRates --}}

<script>
window.incomeModal = function() {
    return {
        modalOpen: false,
        loading: false,
        isEdit: false,
        editId: null,
        errors: {},
        exchangeRates: @json($exchangeRates ?? []),
        formData: {
            amount: '',
            currency: 'UAH',
            category_id: '',
            category_name: '',
            date: new Date().toISOString().split('T')[0],
            payment_method: 'cash',
            notes: '',
            is_anonymous: true
        },
        init() {
            window.openIncomeModal = () => this.openCreate();
            window.openIncomeEditModal = (t) => this.openEdit(t);
        },
        openCreate() {
            this.isEdit = false;
            this.editId = null;
            this.formData = {
                amount: '',
                currency: 'UAH',
                category_id: '',
                category_name: '',
                date: new Date().toISOString().split('T')[0],
                payment_method: 'cash',
                notes: '',
                is_anonymous: true
            };
            this.errors = {};
            this.modalOpen = true;
        },
        openEdit(transaction) {
            this.isEdit = true;
            this.editId = transaction.id;
            this.formData = {
                amount: transaction.amount,
                currency: transaction.currency || 'UAH',
                category_id: transaction.category_id || '',
                category_name: '',
                date: transaction.date.substring(0, 10),
                payment_method: transaction.payment_method || 'cash',
                notes: transaction.notes || '',
                is_anonymous: transaction.is_anonymous ?? true
            };
            this.errors = {};
            this.modalOpen = true;
        },
        async deleteIncome() {
            if (!await confirmDialog(@js( __('messages.confirm_delete_income') ))) return;
            const deleteId = this.editId;

            // Optimistic: close modal, remove from journal, show toast
            this.modalOpen = false;
            this.loading = false;
            showToast('success', @js( __('app.deleted') ));
            if (window.journalRemoveTransaction) {
                window.journalRemoveTransaction(deleteId);
            }

            try {
                const response = await fetch(`/finances/incomes/${deleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json().catch(() => ({}));
                if (response.ok && data.success) {
                    if (!window.journalRemoveTransaction) {
                        this._silentReload();
                    }
                } else {
                    showToast('error', data.message || @js( __('app.delete_error') ));
                    // Reload to restore consistent state after failed optimistic delete
                    this._silentReload();
                }
            } catch (e) {
                showToast('error', @js( __('app.connection_error') ));
                this._silentReload();
            }
        },
        async submit() {
            this.loading = true;
            this.errors = {};
            const url = this.isEdit ? `/finances/incomes/${this.editId}` : '/finances/incomes';
            const wasEdit = this.isEdit;
            const savedFormData = {...this.formData};
            const savedEditId = this.editId;

            try {
                const payload = {...this.formData};
                if (payload.category_id === '__custom__') { payload.category_id = ''; }
                else { delete payload.category_name; }

                // Optimistic: close modal and show toast immediately
                this.modalOpen = false;
                this.loading = false;
                showToast('success', wasEdit ? @js( __('app.saved') ) : @js( __('messages.income_added') ));

                // Inject optimistic row into journal if available
                let optimisticId = null;
                if (!wasEdit && window.journalUpdateTransaction) {
                    optimisticId = 'optimistic_' + Date.now();
                    window.journalUpdateTransaction({
                        id: optimisticId,
                        direction: 'in',
                        amount: parseFloat(savedFormData.amount) || 0,
                        currency: savedFormData.currency || 'UAH',
                        date: savedFormData.date,
                        payment_method: savedFormData.payment_method,
                        notes: savedFormData.notes || '',
                        category_name: savedFormData.category_name || '',
                        is_anonymous: savedFormData.is_anonymous,
                        _optimistic: true
                    }, true);
                }

                const response = await fetch(url, {
                    method: wasEdit ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await response.json().catch(() => ({}));

                if (response.ok && data.success) {
                    if (data.transaction && window.journalUpdateTransaction) {
                        // Replace optimistic row with real data
                        if (optimisticId) {
                            window.journalRemoveTransaction(optimisticId);
                        }
                        window.journalUpdateTransaction(data.transaction, !wasEdit);
                    } else if (!window.journalUpdateTransaction) {
                        // No journal — silently reload page content
                        this._silentReload();
                    }
                } else if (response.status === 422) {
                    // Validation error — remove optimistic row, reopen modal
                    if (optimisticId && window.journalRemoveTransaction) {
                        window.journalRemoveTransaction(optimisticId);
                    }
                    this.errors = data.errors || {};
                    this.isEdit = wasEdit;
                    this.editId = savedEditId;
                    this.formData = savedFormData;
                    this.modalOpen = true;
                    showToast('error', @js( __('app.check_form_errors') ));
                } else {
                    // Server error — remove optimistic row, show error
                    if (optimisticId && window.journalRemoveTransaction) {
                        window.journalRemoveTransaction(optimisticId);
                    }
                    showToast('error', data.message || @js( __('app.save_error') ));
                }
            } catch (e) {
                showToast('error', @js( __('app.connection_error') ));
            }
        },
        _silentReload() {
            // SPA-like reload: re-fetch current page and swap #finance-content
            // Falls back to Livewire.navigate which is still SPA (no full browser reload)
            fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.querySelector('#finance-content');
                    const current = document.querySelector('#finance-content');
                    if (newContent && current) {
                        current.innerHTML = newContent.innerHTML;
                    } else {
                        // Fallback: Livewire.navigate is SPA-like, avoids full page reload
                        Livewire.navigate(window.location.href);
                    }
                })
                .catch(() => Livewire.navigate(window.location.href));
        }
    };
};
</script>

<div x-data="incomeModal()" x-cloak>
    <div x-show="modalOpen"
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-black/50" @click="modalOpen = false"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg relative"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 @click.stop>
                <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white"
                        x-text="isEdit ? @js( __('app.edit_income') ) : @js( __('app.add_income_title') )"></h3>
                    <button @click="modalOpen = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submit()" class="max-h-[70vh] overflow-y-auto p-4 sm:p-6 space-y-4">
                    <!-- Amount + Currency -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.amount') }} <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <input type="number" x-model="formData.amount" step="0.01" min="0.01" required placeholder="0.00"
                                   class="flex-1 px-4 py-2 border rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                   :class="errors.amount ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'">
                            @if(count($enabledCurrencies ?? ['UAH']) > 1)
                            <select x-model="formData.currency"
                                    class="w-24 flex-shrink-0 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500">
                                @foreach($enabledCurrencies as $curr)
                                    <option value="{{ $curr }}">{{ \App\Helpers\CurrencyHelper::symbol($curr) }} {{ $curr }}</option>
                                @endforeach
                            </select>
                            @else
                            <span class="inline-flex items-center px-3 py-2 text-gray-500 dark:text-gray-400">₴</span>
                            @endif
                        </div>
                        @if(count($enabledCurrencies ?? ['UAH']) > 1)
                        <template x-if="formData.currency !== 'UAH' && exchangeRates[formData.currency]">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ __('app.nbu_rate') }}: 1 <span x-text="formData.currency"></span> = <span x-text="exchangeRates[formData.currency]?.toFixed(2)"></span> ₴
                            </p>
                        </template>
                        @endif
                        <p class="text-red-500 text-sm mt-1" x-show="errors.amount" x-text="errors.amount?.[0]"></p>
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.date_column') }} <span class="text-red-500">*</span></label>
                        <input type="date" x-model="formData.date" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                        <p class="text-red-500 text-sm mt-1" x-show="errors.date" x-text="errors.date?.[0]"></p>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.category_column') }} <span class="text-red-500">*</span></label>
                        <select x-model="formData.category_id" :required="formData.category_id !== '__custom__'"
                                x-show="formData.category_id !== '__custom__'"
                                class="w-full px-4 py-2 border rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                :class="errors.category_id ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'">
                            <option value="">{{ __('app.select_category') }}</option>
                            @foreach($incomeCategories ?? [] as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                            <option value="__custom__">{{ __('app.other_enter_manually') }}</option>
                        </select>
                        <div x-show="formData.category_id === '__custom__'" class="flex gap-2">
                            <input type="text" x-model="formData.category_name" placeholder="{{ __('app.category_name_placeholder') }}" :required="formData.category_id === '__custom__'"
                                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <button type="button" @click="formData.category_id = ''; formData.category_name = ''"
                                    class="px-3 py-2 text-gray-500 hover:text-red-500 border border-gray-300 dark:border-gray-600 rounded-xl transition-colors">&#x2715;</button>
                        </div>
                        <p class="text-red-500 text-sm mt-1" x-show="errors.category_id" x-text="errors.category_id?.[0]"></p>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.payment_method_field') }} <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                   :class="formData.payment_method === 'cash' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-300 dark:border-gray-600'">
                                <input type="radio" x-model="formData.payment_method" value="cash" class="sr-only">
                                <span class="text-sm" :class="formData.payment_method === 'cash' ? 'text-primary-600 dark:text-primary-400 font-medium' : 'text-gray-700 dark:text-gray-300'">{{ __('app.cash_payment') }}</span>
                            </label>
                            <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                   :class="formData.payment_method === 'card' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-300 dark:border-gray-600'">
                                <input type="radio" x-model="formData.payment_method" value="card" class="sr-only">
                                <span class="text-sm" :class="formData.payment_method === 'card' ? 'text-primary-600 dark:text-primary-400 font-medium' : 'text-gray-700 dark:text-gray-300'">{{ __('app.card_payment') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.notes_label_simple') }}</label>
                        <textarea x-model="formData.notes" rows="2" placeholder="{{ __('app.internal_notes_placeholder') }}"
                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div>
                            @if(auth()->user()->canDelete('finances'))
                            <button x-show="isEdit" type="button" @click="deleteIncome()"
                                    :disabled="loading"
                                    class="px-4 py-2 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 rounded-xl text-sm font-medium disabled:opacity-50">
                                {{ __('app.delete') }}
                            </button>
                            @endif
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="modalOpen = false"
                                    class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
                                {{ __('app.cancel') }}
                            </button>
                            <button type="submit" :disabled="loading"
                                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl disabled:opacity-50 transition-colors">
                                <span x-show="!loading" x-text="isEdit ? @js( __('app.save_btn') ) : @js( __('app.add_btn') )"></span>
                                <span x-show="loading" class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    {{ __('app.saving_label') }}
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
