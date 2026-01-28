@extends('layouts.app')

@section('title', 'Редагувати витрату')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ request('redirect_to') === 'ministry' && request('ministry') ? route('ministries.show', ['ministry' => request('ministry'), 'tab' => 'expenses']) : route('finances.expenses.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Редагувати витрату</h2>
            <x-delete-confirm
                :action="route('finances.expenses.destroy', $expense)"
                title="Видалити витрату?"
                message="Ви впевнені, що хочете видалити цю витрату? Цю дію неможливо скасувати."
                button-text="Видалити"
                :icon="false"
                class="text-sm"
            />
        </div>

        <form method="POST" action="{{ route('finances.expenses.update', $expense) }}" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="redirect_to" value="{{ old('redirect_to', request('redirect_to')) }}">
            <input type="hidden" name="redirect_ministry_id" value="{{ old('redirect_ministry_id', request('ministry')) }}">

            {{-- Budget exceeded warning --}}
            @if(session('budget_exceeded'))
                <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-orange-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">Перевищення бюджету</h3>
                            <p class="mt-1 text-sm text-orange-700 dark:text-orange-300">{{ session('error') }}</p>
                            <label class="mt-3 flex items-center">
                                <input type="checkbox" name="force_over_budget" value="1"
                                       class="rounded border-orange-300 text-orange-600 focus:ring-orange-500">
                                <span class="ml-2 text-sm text-orange-800 dark:text-orange-200">Все одно зберегти (потрібне підтвердження)</span>
                            </label>
                        </div>
                    </div>
                </div>
            @endif

            <div class="space-y-4">
                <div>
                    <label for="ministry_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Команда <span class="text-red-500">*</span></label>
                    <x-searchable-select
                        name="ministry_id"
                        :items="$ministries"
                        :selected="old('ministry_id', $expense->ministry_id)"
                        labelKey="name"
                        valueKey="id"
                        colorKey="color"
                        placeholder="Пошук команди..."
                        nullText="Виберіть команду"
                        :nullable="false"
                        required
                    />
                    @error('ministry_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4" x-data="{ currency: '{{ old('currency', $expense->currency ?? 'UAH') }}', exchangeRates: {{ json_encode($exchangeRates) }} }">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Сума <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="number" name="amount" id="amount" value="{{ old('amount', $expense->amount) }}" required min="0.01" step="0.01"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
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
                                Курс: 1 <span x-text="currency"></span> = <span x-text="exchangeRates[currency]?.toFixed(2)"></span> ₴
                            </p>
                        </template>
                        @error('amount')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата <span class="text-red-500">*</span></label>
                        <input type="date" name="date" id="date" value="{{ old('date', $expense->date->format('Y-m-d')) }}" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('date')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис <span class="text-red-500">*</span></label>
                    <input type="text" name="description" id="description" value="{{ old('description', $expense->description) }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('description')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія</label>
                        <x-searchable-select
                            name="category_id"
                            :items="$categories"
                            :selected="old('category_id', $expense->category_id)"
                            labelKey="name"
                            valueKey="id"
                            colorKey="color"
                            placeholder="Пошук категорії..."
                            nullText="Без категорії"
                            nullable
                        />
                    </div>

                    <div>
                        <label for="expense_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип витрати</label>
                        <select name="expense_type" id="expense_type"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Не вказано</option>
                            <option value="recurring" {{ old('expense_type', $expense->expense_type) == 'recurring' ? 'selected' : '' }}>Регулярна</option>
                            <option value="one_time" {{ old('expense_type', $expense->expense_type) == 'one_time' ? 'selected' : '' }}>Одноразова</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Спосіб оплати</label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input type="radio" name="payment_method" value="card" {{ old('payment_method', $expense->payment_method ?? 'card') == 'card' ? 'checked' : '' }}
                                   class="text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Картка</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="payment_method" value="cash" {{ old('payment_method', $expense->payment_method) == 'cash' ? 'checked' : '' }}
                                   class="text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Готівка</span>
                        </label>
                    </div>
                </div>

                <div x-data="receiptUploaderEdit()">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Чеки / Квитанції</label>

                    <!-- Existing attachments -->
                    @if($expense->attachments->count() > 0)
                    <div class="mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Завантажені файли:</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach($expense->attachments as $attachment)
                            <div class="relative group" x-data="{ deleted: false }" x-show="!deleted">
                                @if($attachment->is_image)
                                    <a href="{{ $attachment->url }}" target="_blank">
                                        <img src="{{ $attachment->url }}" class="w-full h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                    </a>
                                @else
                                    <a href="{{ $attachment->url }}" target="_blank" class="block w-full h-24 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                        </svg>
                                    </a>
                                @endif
                                <button type="button" @click="deleted = true; $refs.deleteInput{{ $attachment->id }}.disabled = false"
                                        class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <input type="hidden" name="delete_attachments[]" value="{{ $attachment->id }}" disabled x-ref="deleteInput{{ $attachment->id }}">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ $attachment->original_name }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Drop zone for new files -->
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 text-center hover:border-primary-500 dark:hover:border-primary-400 transition-colors cursor-pointer"
                         @click="$refs.fileInput.click()"
                         @dragover.prevent="dragover = true"
                         @dragleave.prevent="dragover = false"
                         @drop.prevent="handleDrop($event)"
                         :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': dragover }">
                        <input type="file" name="receipts[]" multiple accept="image/*,.pdf" class="hidden" x-ref="fileInput" @change="handleFiles($event)">
                        <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Додати нові файли
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            JPG, PNG, PDF до 10 МБ
                        </p>
                    </div>

                    <!-- Preview grid for new files -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3" x-show="previews.length > 0">
                        <template x-for="(preview, index) in previews" :key="index">
                            <div class="relative group">
                                <template x-if="preview.type === 'image'">
                                    <img :src="preview.url" class="w-full h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                </template>
                                <template x-if="preview.type === 'pdf'">
                                    <div class="w-full h-24 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
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
                    @error('receipts')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    @error('receipts.*')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <script>
                    function receiptUploaderEdit() {
                        return {
                            dragover: false,
                            previews: [],
                            files: [],
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
                                    if (!file.type.match('image.*') && file.type !== 'application/pdf') continue;
                                    if (file.size > 10 * 1024 * 1024) continue;

                                    const preview = {
                                        name: file.name,
                                        type: file.type === 'application/pdf' ? 'pdf' : 'image',
                                        url: file.type !== 'application/pdf' ? URL.createObjectURL(file) : null
                                    };
                                    this.previews.push(preview);
                                    this.files.push(file);
                                }
                                this.updateInput();
                            },
                            removeFile(index) {
                                if (this.previews[index].url) {
                                    URL.revokeObjectURL(this.previews[index].url);
                                }
                                this.previews.splice(index, 1);
                                this.files.splice(index, 1);
                                this.updateInput();
                            },
                            updateInput() {
                                const dt = new DataTransfer();
                                this.files.forEach(f => dt.items.add(f));
                                this.$refs.fileInput.files = dt.files;
                            }
                        }
                    }
                </script>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нотатки</label>
                    <textarea name="notes" id="notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('notes', $expense->notes) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('finances.expenses.index') }}" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    Скасувати
                </a>
                <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                    Зберегти
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
