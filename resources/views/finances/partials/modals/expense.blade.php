{{-- Shared Expense Modal (create + edit, with receipt upload) --}}
{{-- Required variables: $expenseCategories, $ministries, $enabledCurrencies, $exchangeRates --}}

<script>
window.expenseModal = function() {
    return {
        modalOpen: false,
        loading: false,
        isEdit: false,
        editId: null,
        errors: {},
        dragover: false,
        previews: [],
        files: [],
        exchangeRates: @json($exchangeRates ?? []),
        formData: {
            amount: '',
            currency: 'UAH',
            description: '',
            category_id: '',
            category_name: '',
            ministry_id: '',
            expense_type: '',
            date: new Date().toISOString().split('T')[0],
            payment_method: 'card',
            notes: ''
        },
        init() {
            window.openExpenseModal = () => this.openCreate();
            window.openExpenseEditModal = (t) => this.openEdit(t);
        },
        openCreate() {
            this.isEdit = false;
            this.editId = null;
            this.formData = {
                amount: '',
                currency: 'UAH',
                description: '',
                category_id: '',
                category_name: '',
                ministry_id: '',
                expense_type: '',
                date: new Date().toISOString().split('T')[0],
                payment_method: 'card',
                notes: ''
            };
            this.errors = {};
            this.previews = [];
            this.files = [];
            this.dragover = false;
            this.modalOpen = true;
        },
        openEdit(transaction) {
            this.isEdit = true;
            this.editId = transaction.id;
            this.formData = {
                amount: transaction.amount,
                currency: transaction.currency || 'UAH',
                description: transaction.description || '',
                category_id: transaction.category_id || '',
                category_name: '',
                ministry_id: transaction.ministry_id || '',
                expense_type: '',
                date: transaction.date.substring(0, 10),
                payment_method: transaction.payment_method || 'card',
                notes: transaction.notes || ''
            };
            this.errors = {};
            this.previews = [];
            this.files = [];
            this.modalOpen = true;
        },
        handleFiles(event) {
            this.addFiles(event.target.files);
        },
        handleDrop(event) {
            this.dragover = false;
            this.addFiles(event.dataTransfer.files);
        },
        addFiles(fileList) {
            for (let file of fileList) {
                if (this.previews.length >= 10) break;
                const isHeic = file.name.match(/\.heic$/i) || file.name.match(/\.heif$/i);
                if (!file.type.match('image.*') && file.type !== 'application/pdf' && !isHeic) {
                    showToast('error', @js(__('app.unsupported_format') ) + ': ' + file.name);
                    continue;
                }
                if (file.size > 10 * 1024 * 1024) {
                    showToast('error', @js(__('app.file_too_large_name') ) + ': ' + file.name);
                    continue;
                }
                this.previews.push({
                    name: file.name,
                    type: file.type === 'application/pdf' ? 'pdf' : (isHeic ? 'heic' : 'image'),
                    url: (!isHeic && file.type !== 'application/pdf') ? URL.createObjectURL(file) : null
                });
                this.files.push(file);
            }
        },
        removeFile(index) {
            if (this.previews[index].url) URL.revokeObjectURL(this.previews[index].url);
            this.previews.splice(index, 1);
            this.files.splice(index, 1);
        },
        async deleteExpense() {
            if (!confirm(@js( __('messages.confirm_delete_expense') ))) return;
            this.loading = true;
            try {
                const response = await fetch(`/finances/expenses/${this.editId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json().catch(() => ({}));
                if (response.ok && data.success) {
                    this.modalOpen = false;
                    showToast('success', data.message || @js( __('app.expense_deleted') ));
                    if (window.journalRemoveTransaction) {
                        window.journalRemoveTransaction(this.editId);
                    } else {
                        setTimeout(() => Livewire.navigate(window.location.href), 600);
                    }
                } else {
                    showToast('error', data.message || @js( __('app.delete_error') ));
                }
            } catch (e) {
                showToast('error', @js( __('app.connection_error') ));
            } finally {
                this.loading = false;
            }
        },
        async submit() {
            this.loading = true;
            this.errors = {};
            const url = this.isEdit ? `/finances/expenses/${this.editId}` : '/finances/expenses';
            try {
                const fd = new FormData();
                Object.keys(this.formData).forEach(key => {
                    let value = this.formData[key];
                    if (key === 'category_id' && value === '__custom__') { value = ''; }
                    if (key === 'category_name' && this.formData.category_id !== '__custom__') { return; }
                    if (value !== null && value !== undefined) { fd.append(key, value); }
                });
                if (!this.isEdit) {
                    this.files.forEach(f => fd.append('receipts[]', f));
                }
                if (this.isEdit) { fd.append('_method', 'PUT'); }

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                });

                if (response.status === 413) {
                    showToast('error', @js( __('app.file_too_large_upload', ['size' => '10 MB']) ));
                    this.loading = false;
                    return;
                }

                const data = await response.json().catch(() => ({}));
                if (response.ok && data.success) {
                    this.modalOpen = false;
                    showToast('success', data.message || @js( __('app.saved') ));
                    if (data.budget_warning) {
                        showToast('warning', data.budget_warning);
                    }
                    if (data.transaction && window.journalUpdateTransaction) {
                        window.journalUpdateTransaction(data.transaction, !this.isEdit);
                    } else {
                        setTimeout(() => Livewire.navigate(window.location.href), 600);
                    }
                } else if (response.status === 422) {
                    this.errors = data.errors || {};
                    showToast('error', @js( __('app.check_form_errors') ));
                } else {
                    showToast('error', data.message || @js( __('app.save_error') ));
                }
            } catch (e) {
                showToast('error', @js( __('app.connection_error_generic') ));
            } finally {
                this.loading = false;
            }
        }
    };
};
</script>

<div x-data="expenseModal()" x-cloak>
    <div x-show="modalOpen"
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="modalOpen = false"></div>
        <div class="flex items-start justify-center min-h-screen pt-4 px-4 pb-20 sm:p-0">
            <div class="relative w-full max-w-lg mx-auto mt-8 sm:mt-16 bg-white dark:bg-gray-800 rounded-2xl shadow-xl z-10"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 @click.stop>
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white"
                        x-text="isEdit ? @js( __('app.edit_expense') ) : @js( __('app.add_expense_title') )"></h3>
                    <button @click="modalOpen = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submit()" class="max-h-[70vh] overflow-y-auto p-6 space-y-4">
                    <!-- Ministry -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.team_label') }} <span class="text-red-500">*</span></label>
                        <select x-model="formData.ministry_id" required
                                class="w-full px-4 py-2 border rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                :class="errors.ministry_id ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'">
                            <option value="">{{ __('app.select_team') }}</option>
                            @foreach($ministries ?? [] as $ministry)
                                <option value="{{ $ministry->id }}">{{ $ministry->name }}@if($ministry->monthly_budget) ({{ __('app.budget_remaining') }}: {{ number_format($ministry->remaining_budget, 0, ',', ' ') }} ₴)@endif</option>
                            @endforeach
                        </select>
                        <p class="text-red-500 text-sm mt-1" x-show="errors.ministry_id" x-text="errors.ministry_id?.[0]"></p>
                    </div>

                    <!-- Amount + Currency / Date row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.amount') }} <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <input type="number" x-model="formData.amount" step="0.01" min="0.01" required placeholder="0.00"
                                       class="flex-1 px-3 py-2 border rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                       :class="errors.amount ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'">
                                @if(count($enabledCurrencies ?? ['UAH']) > 1)
                                <select x-model="formData.currency"
                                        class="w-20 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 text-sm">
                                    @foreach($enabledCurrencies as $curr)
                                        <option value="{{ $curr }}">{{ \App\Helpers\CurrencyHelper::symbol($curr) }}</option>
                                    @endforeach
                                </select>
                                @else
                                <span class="inline-flex items-center px-2 py-2 text-gray-500 dark:text-gray-400">₴</span>
                                @endif
                            </div>
                            @if(count($enabledCurrencies ?? ['UAH']) > 1)
                            <template x-if="formData.currency !== 'UAH' && exchangeRates[formData.currency]">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('app.exchange_rate_short') }}: 1 <span x-text="formData.currency"></span> = <span x-text="exchangeRates[formData.currency]?.toFixed(2)"></span> ₴
                                </p>
                            </template>
                            @endif
                            <p class="text-red-500 text-sm mt-1" x-show="errors.amount" x-text="errors.amount?.[0]"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.date_column') }} <span class="text-red-500">*</span></label>
                            <input type="date" x-model="formData.date" required
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <p class="text-red-500 text-sm mt-1" x-show="errors.date" x-text="errors.date?.[0]"></p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.description_column') }} <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.description" required maxlength="255" placeholder="{{ __('app.expense_desc_placeholder') }}"
                               class="w-full px-3 py-2 border rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                               :class="errors.description ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'">
                        <p class="text-red-500 text-sm mt-1" x-show="errors.description" x-text="errors.description?.[0]"></p>
                    </div>

                    <!-- Category + Expense Type row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.category_column') }}</label>
                            <select x-model="formData.category_id"
                                    x-show="formData.category_id !== '__custom__'"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                <option value="">{{ __('app.no_category') }}</option>
                                @foreach($expenseCategories ?? [] as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->icon ?? '💸' }} {{ $cat->name }}</option>
                                @endforeach
                                <option value="__custom__">{{ __('app.other_enter_manually') }}</option>
                            </select>
                            <div x-show="formData.category_id === '__custom__'" class="relative">
                                <input type="text" x-model="formData.category_name" placeholder="{{ __('app.category_name_placeholder') }}"
                                       class="w-full px-3 py-2 pr-9 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                <button type="button" @click="formData.category_id = ''; formData.category_name = ''"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-red-500 rounded-full transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.expense_type') }}</label>
                            <select x-model="formData.expense_type"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                <option value="">{{ __('app.not_specified') }}</option>
                                <option value="recurring">{{ __('app.recurring_type') }}</option>
                                <option value="one_time">{{ __('app.one_time_type') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.payment_method_field') }}</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                   :class="formData.payment_method === 'card' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-300 dark:border-gray-600'">
                                <input type="radio" x-model="formData.payment_method" value="card" class="sr-only">
                                <span class="text-sm" :class="formData.payment_method === 'card' ? 'text-primary-600 dark:text-primary-400 font-medium' : 'text-gray-700 dark:text-gray-300'">💳 {{ __('app.card_payment') }}</span>
                            </label>
                            <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                   :class="formData.payment_method === 'cash' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-300 dark:border-gray-600'">
                                <input type="radio" x-model="formData.payment_method" value="cash" class="sr-only">
                                <span class="text-sm" :class="formData.payment_method === 'cash' ? 'text-primary-600 dark:text-primary-400 font-medium' : 'text-gray-700 dark:text-gray-300'">💵 {{ __('app.cash_payment') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Receipt upload (only for new expenses) -->
                    <div x-show="!isEdit">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.receipts_label') }}</label>
                        <div class="border-2 border-dashed rounded-xl p-3 text-center transition-colors cursor-pointer"
                             :class="dragover ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400'"
                             @click="$refs.expenseFileInput.click()"
                             @dragover.prevent="dragover = true"
                             @dragleave.prevent="dragover = false"
                             @drop.prevent="handleDrop($event)">
                            <input type="file" multiple accept="image/*,.heic,.heif,.pdf" class="hidden" x-ref="expenseFileInput" @change="handleFiles($event)">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('app.click_or_drag_files') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500">{{ __('app.file_formats_hint') }}</p>
                        </div>
                        <div class="grid grid-cols-3 gap-2 mt-2" x-show="previews.length > 0">
                            <template x-for="(preview, index) in previews" :key="index">
                                <div class="relative group">
                                    <template x-if="preview.type === 'image'">
                                        <img :src="preview.url" class="w-full h-20 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                    </template>
                                    <template x-if="preview.type === 'heic'">
                                        <div class="w-full h-20 flex flex-col items-center justify-center bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-700">
                                            <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-xs text-purple-600 dark:text-purple-400">HEIC</span>
                                        </div>
                                    </template>
                                    <template x-if="preview.type === 'pdf'">
                                        <div class="w-full h-20 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-700">
                                            <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </template>
                                    <button type="button" @click="removeFile(index)"
                                            class="absolute -top-1.5 -right-1.5 p-0.5 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="preview.name"></p>
                                </div>
                            </template>
                        </div>
                        <p class="text-red-500 text-sm mt-1" x-show="errors.receipts || errors['receipts.0']"
                           x-text="errors.receipts ? errors.receipts[0] : (errors['receipts.0'] ? errors['receipts.0'][0] : '')"></p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.notes_label_simple') }}</label>
                        <textarea x-model="formData.notes" rows="2" placeholder="{{ __('app.internal_notes_placeholder') }}"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div>
                            @if(auth()->user()->canDelete('finances'))
                            <button x-show="isEdit" type="button" @click="deleteExpense()"
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
                                    class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl disabled:opacity-50 transition-colors">
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
