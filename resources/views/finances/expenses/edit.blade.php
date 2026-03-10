@extends('layouts.app')

@section('title', __('app.finance_edit_expense_page'))

@section('content')
<div class="max-w-2xl mx-auto" x-data="expenseEditForm()">
    <a href="{{ request('redirect_to') === 'ministry' && request('ministry') ? route('ministries.show', ['ministry' => request('ministry'), 'tab' => 'expenses']) : route('finances.transactions', ['filter' => 'expense']) }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.finance_back') }}
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.finance_edit_expense_page') }}</h2>
            <x-delete-confirm
                :action="route('finances.expenses.destroy', $expense)"
                :redirect="route('finances.transactions', ['filter' => 'expense'])"
                title="{{ __('app.finance_delete_expense_confirm') }}"
                message="{{ __('app.finance_delete_expense_msg') }}"
                button-text="{{ __('app.finance_delete') }}"
                :icon="false"
                :ajax="true"
                class="text-sm"
            />
        </div>

        <form @submit.prevent="submitForm" enctype="multipart/form-data" class="p-6 space-y-6" x-ref="form">
            <input type="hidden" name="redirect_to" value="{{ old('redirect_to', request('redirect_to')) }}">
            <input type="hidden" name="redirect_ministry_id" value="{{ old('redirect_ministry_id', request('ministry')) }}">

            <div class="space-y-4">
                <div>
                    <label for="ministry_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.team_label') }} <span class="text-red-500">*</span></label>
                    <x-searchable-select
                        name="ministry_id"
                        :items="$ministries"
                        :selected="old('ministry_id', $expense->ministry_id)"
                        labelKey="name"
                        valueKey="id"
                        colorKey="color"
                        placeholder="{{ __('app.search_team') }}"
                        nullText="{{ __('app.select_team') }}"
                        :nullable="false"
                        required
                    />
                    <template x-if="errors.ministry_id">
                        <p class="mt-1 text-sm text-red-500" x-text="errors.ministry_id[0]"></p>
                    </template>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-data="{ currency: '{{ old('currency', $expense->currency ?? 'UAH') }}', exchangeRates: {{ json_encode($exchangeRates) }} }">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.amount_required') }} <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="number" name="amount" id="amount" value="{{ old('amount', $expense->amount) }}" required min="0.01" step="0.01"
                                       class="w-full px-3 py-2 border {{ $errors->has('amount') ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            @if(count($enabledCurrencies) > 1)
                            <select name="currency" x-model="currency"
                                    class="w-20 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                @foreach($enabledCurrencies as $curr)
                                    <option value="{{ $curr }}" {{ old('currency', $expense->currency) == $curr ? 'selected' : '' }}>{{ \App\Helpers\CurrencyHelper::symbol($curr) }}</option>
                                @endforeach
                            </select>
                            @else
                            <input type="hidden" name="currency" value="UAH">
                            <span class="inline-flex items-center px-2 py-2 text-gray-500 dark:text-gray-400">₴</span>
                            @endif
                        </div>
                        <template x-if="currency !== 'UAH' && exchangeRates[currency]">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ __('app.nbu_rate') }}: 1 <span x-text="currency"></span> = <span x-text="exchangeRates[currency]?.toFixed(2)"></span> ₴
                            </p>
                        </template>
                        <template x-if="errors.amount">
                            <p class="mt-1 text-sm text-red-500" x-text="errors.amount[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.date_required') }} <span class="text-red-500">*</span></label>
                        <input type="date" name="date" id="date" value="{{ old('date', $expense->date->format('Y-m-d')) }}" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <template x-if="errors.date">
                            <p class="mt-1 text-sm text-red-500" x-text="errors.date[0]"></p>
                        </template>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.description') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="description" id="description" value="{{ old('description', $expense->description) }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <template x-if="errors.description">
                        <p class="mt-1 text-sm text-red-500" x-text="errors.description[0]"></p>
                    </template>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.category') }}</label>
                        <x-searchable-select
                            name="category_id"
                            :items="$categories"
                            :selected="old('category_id', $expense->category_id)"
                            labelKey="name"
                            valueKey="id"
                            colorKey="color"
                            placeholder="{{ __('app.search_category') }}"
                            nullText="{{ __('app.no_category') }}"
                            nullable
                        />
                    </div>

                    <div>
                        <label for="expense_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.expense_type') }}</label>
                        <select name="expense_type" id="expense_type"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">{{ __('app.not_specified') }}</option>
                            <option value="recurring" {{ old('expense_type', $expense->expense_type) == 'recurring' ? 'selected' : '' }}>{{ __('app.regular_expense') }}</option>
                            <option value="one_time" {{ old('expense_type', $expense->expense_type) == 'one_time' ? 'selected' : '' }}>{{ __('app.one_time_purchase') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.payment_method_label') }}</label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input type="radio" name="payment_method" value="card" {{ old('payment_method', $expense->payment_method ?? 'card') == 'card' ? 'checked' : '' }}
                                   class="text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">{{ __('app.card_payment') }}</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="payment_method" value="cash" {{ old('payment_method', $expense->payment_method) == 'cash' ? 'checked' : '' }}
                                   class="text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">{{ __('app.cash_payment') }}</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.receipts_label') }}</label>

                    <!-- Existing attachments (Alpine-driven) -->
                    <div class="mb-3" x-show="attachments.length > 0">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('app.uploaded_files') }}:</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <template x-for="att in attachments" :key="att.id">
                                <div class="relative group" x-show="!deleteAttachments.includes(att.id)">
                                    <template x-if="att.is_image">
                                        <img :src="att.url" class="w-full h-36 object-cover rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:opacity-80 transition-opacity"
                                             @click="$dispatch('open-lightbox', att.url)">
                                    </template>
                                    <template x-if="!att.is_image">
                                        <a :href="att.url" target="_blank" class="block w-full h-36 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-700">
                                            <svg class="w-10 h-10 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                            </svg>
                                        </a>
                                    </template>
                                    <button type="button" @click="toggleDelete(att.id)"
                                            class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate" x-text="att.original_name"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Drop zone for new files -->
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 text-center hover:border-primary-500 dark:hover:border-primary-400 transition-colors cursor-pointer"
                         @click="$refs.fileInput.click()"
                         @dragover.prevent="dragover = true"
                         @dragleave.prevent="dragover = false"
                         @drop.prevent="handleDrop($event)"
                         :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': dragover }">
                        <input type="file" name="receipts[]" multiple accept="image/*,.heic,.heif,.pdf" class="hidden" x-ref="fileInput" @change="handleFiles($event)">
                        <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('app.add_new_files') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            JPG, PNG, HEIC, PDF — max 10 MB
                        </p>
                    </div>

                    <!-- Preview grid for new files -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-3" x-show="previews.length > 0">
                        <template x-for="(preview, index) in previews" :key="index">
                            <div class="relative group">
                                <template x-if="preview.type === 'image'">
                                    <img :src="preview.url" class="w-full h-36 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                </template>
                                <template x-if="preview.type === 'heic'">
                                    <div class="w-full h-36 flex flex-col items-center justify-center bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-700">
                                        <svg class="w-10 h-10 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-xs text-purple-600 dark:text-purple-400 mt-1">HEIC</span>
                                    </div>
                                </template>
                                <template x-if="preview.type === 'pdf'">
                                    <div class="w-full h-36 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <svg class="w-10 h-10 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </template>
                                <button type="button" @click="removeFile(index)"
                                        class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate" x-text="preview.name"></p>
                            </div>
                        </template>
                    </div>
                    <template x-if="errors.receipts || errors['receipts.0']">
                        <p class="mt-1 text-sm text-red-500" x-text="errors.receipts ? errors.receipts[0] : errors['receipts.0'][0]"></p>
                    </template>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.notes_label_simple') }}</label>
                    <textarea name="notes" id="notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('notes', $expense->notes) }}</textarea>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 sm:gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('finances.transactions', ['filter' => 'expense']) }}" class="w-full sm:w-auto px-4 py-2 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    {{ __('app.cancel') }}
                </a>
                <button type="submit" :disabled="saving"
                        class="w-full sm:w-auto px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                    <span x-show="!saving">{{ __('app.save') }}</span>
                    <span x-show="saving" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ __('app.saving') }}
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function expenseEditForm() {
    return {
        saving: false,
        errors: {},
        dragover: false,
        previews: [],
        files: [],
        attachments: @json($expense->attachments),
        deleteAttachments: [],

        toggleDelete(id) {
            const idx = this.deleteAttachments.indexOf(id);
            if (idx === -1) {
                this.deleteAttachments.push(id);
            } else {
                this.deleteAttachments.splice(idx, 1);
            }
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
                    showToast('error', @js(__('app.unsupported_format')) + ': ' + file.name);
                    continue;
                }
                if (file.size > 10 * 1024 * 1024) {
                    showToast('error', @js(__('app.file_too_large_name')) + ': ' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(1) + ' MB)');
                    continue;
                }
                this.previews.push({
                    name: file.name,
                    type: file.type === 'application/pdf' ? 'pdf' : (isHeic ? 'heic' : 'image'),
                    url: (!isHeic && file.type !== 'application/pdf') ? URL.createObjectURL(file) : null
                });
                this.files.push(file);
            }
            this.updateInput();
        },

        removeFile(index) {
            if (this.previews[index].url) URL.revokeObjectURL(this.previews[index].url);
            this.previews.splice(index, 1);
            this.files.splice(index, 1);
            this.updateInput();
        },

        updateInput() {
            const dt = new DataTransfer();
            this.files.forEach(f => dt.items.add(f));
            this.$refs.fileInput.files = dt.files;
        },

        async submitForm() {
            this.saving = true;
            this.errors = {};

            const formData = new FormData(this.$refs.form);
            formData.append('_method', 'PUT');

            // Add delete_attachments
            this.deleteAttachments.forEach(id => {
                formData.append('delete_attachments[]', id);
            });

            // Add files manually (since the form input may not be in formData correctly)
            formData.delete('receipts[]');
            this.files.forEach(f => formData.append('receipts[]', f));

            try {
                const response = await fetch('{{ route("finances.expenses.update", $expense) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                if (response.status === 413) {
                    showToast('error', @js(__('app.file_too_large_upload', ['size' => '10 MB'])));
                    this.saving = false;
                    return;
                }

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.errors = data.errors;
                        showToast('error', @js(__('app.check_form_errors')));
                    } else {
                        showToast('error', data.message || @js(__('app.save_error')));
                    }
                    this.saving = false;
                    return;
                }

                // Success: update attachments from server response
                if (data.transaction && data.transaction.attachments) {
                    this.attachments = data.transaction.attachments;
                }
                this.deleteAttachments = [];

                // Clear file previews
                this.previews.forEach(p => { if (p.url) URL.revokeObjectURL(p.url); });
                this.previews = [];
                this.files = [];
                this.updateInput();

                showToast('success', data.message || @js(__('app.saved')));

                if (data.budget_warning) {
                    showToast('warning', data.budget_warning);
                }
            } catch (e) {
                showToast('error', @js(__('app.connection_error')));
            }

            this.saving = false;
        }
    }
}
</script>
@endsection
