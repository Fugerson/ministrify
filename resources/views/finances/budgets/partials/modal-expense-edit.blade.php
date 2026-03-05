{{-- Expense Edit Modal --}}
<!-- Script must be defined BEFORE Alpine component that uses it -->
<script>
window.expenseEditModal = function() {
    return {
        modalOpen: false,
        loading: false,
        loadingData: false,
        editId: null,
        existingAttachments: [],
        deleteAttachments: [],
        selectedFiles: [],
        formData: {
            amount: '',
            currency: 'UAH',
            description: '',
            category_id: '',
            category_name: '',
            ministry_id: '',
            date: ''
        },
        init() {
            window.openExpenseEdit = (id) => this.openEdit(id);
        },
        async openEdit(id) {
            this.editId = id;
            this.loadingData = true;
            this.modalOpen = true;
            this.existingAttachments = [];
            this.deleteAttachments = [];
            this.selectedFiles = [];

            try {
                const response = await fetch(`/finances/expenses/${id}/edit`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json().catch(() => ({}));
                const t = data.transaction;

                this.formData = {
                    amount: t.amount,
                    currency: t.currency || 'UAH',
                    description: t.description || '',
                    category_id: t.category_id || '',
                    category_name: '',
                    ministry_id: t.ministry_id || '',
                    date: t.date.substring(0, 10)
                };
                this.existingAttachments = t.attachments || [];
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            } catch (e) {
                showToast('error', 'Помилка завантаження');
                this.modalOpen = false;
            } finally {
                this.loadingData = false;
            }
        },
        handleFileSelect(event) {
            const files = Array.from(event.target.files);
            const maxSize = 10 * 1024 * 1024; // 10 MB
            const rejected = [];
            const accepted = [];
            for (const file of files) {
                if (accepted.length >= 10) break;
                if (file.size > maxSize) {
                    rejected.push(file.name + ' (' + (file.size / 1024 / 1024).toFixed(1) + ' МБ)');
                    continue;
                }
                accepted.push(file);
            }
            this.selectedFiles = accepted;
            if (rejected.length) {
                showToast('error', 'Файл занадто великий (макс. 10 МБ): ' + rejected.join(', '));
            }
        },
        removeFile(index) {
            this.selectedFiles.splice(index, 1);
            if (this.$refs.fileInput) this.$refs.fileInput.value = '';
        },
        toggleDeleteAttachment(id) {
            const idx = this.deleteAttachments.indexOf(id);
            if (idx === -1) {
                this.deleteAttachments.push(id);
            } else {
                this.deleteAttachments.splice(idx, 1);
            }
        },
        async submit() {
            this.loading = true;
            try {
                const formData = new FormData();
                formData.append('_method', 'PUT');
                formData.append('amount', this.formData.amount);
                formData.append('currency', this.formData.currency);
                formData.append('description', this.formData.description);
                if (this.formData.category_id && this.formData.category_id !== '__custom__') formData.append('category_id', this.formData.category_id);
                if (this.formData.category_id === '__custom__' && this.formData.category_name) formData.append('category_name', this.formData.category_name);
                formData.append('ministry_id', this.formData.ministry_id || '');
                formData.append('date', this.formData.date);

                this.selectedFiles.forEach(file => {
                    formData.append('receipts[]', file);
                });

                this.deleteAttachments.forEach(id => {
                    formData.append('delete_attachments[]', id);
                });

                const response = await fetch(`/finances/expenses/${this.editId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                if (response.status === 413) {
                    showToast('error', 'Файл занадто великий для завантаження. Максимум 10 МБ на файл.');
                    return;
                }

                const data = await response.json().catch(() => ({}));
                if (response.ok && data.success) {
                    this.modalOpen = false;
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 500);
                } else if (response.status === 422) {
                    const errorMsgs = Object.values(data.errors || {}).flat();
                    showToast('error', errorMsgs.length ? errorMsgs[0] : (data.message || 'Помилка валідації'));
                } else {
                    showToast('error', data.message || 'Помилка збереження');
                }
            } catch (e) {
                showToast('error', 'Помилка збереження. Перевірте розмір файлів (макс. 10 МБ).');
            } finally {
                this.loading = false;
            }
        }
    };
};
</script>

<div x-data="expenseEditModal()" x-cloak>
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
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Редагувати витрату</h3>
                    <button @click="modalOpen = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Loading -->
                <div x-show="loadingData" class="p-8 text-center">
                    <svg class="animate-spin h-8 w-8 mx-auto text-primary-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>

                <form x-show="!loadingData" @submit.prevent="submit()" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Сума *</label>
                        <div class="flex gap-2">
                            <input type="number" x-model="formData.amount" step="0.01" min="0.01" required
                                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <select x-model="formData.currency"
                                    class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                @foreach($enabledCurrencies ?? ['UAH'] as $curr)
                                    <option value="{{ $curr }}">{{ $curr }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис *</label>
                        <input type="text" x-model="formData.description" required maxlength="255"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата *</label>
                        <input type="date" x-model="formData.date" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія</label>
                        <select x-model="formData.category_id"
                                :class="{ 'hidden': formData.category_id === '__custom__' }"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Без категорії</option>
                            @foreach($expenseCategories ?? [] as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->icon_emoji ?? '💸' }} {{ $cat->name }}</option>
                            @endforeach
                            <option value="__custom__">Інше (ввести вручну)...</option>
                        </select>
                        <div x-show="formData.category_id === '__custom__'" class="flex gap-2">
                            <input type="text" x-model="formData.category_name" placeholder="Назва категорії..."
                                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <button type="button" @click="formData.category_id = ''; formData.category_name = ''"
                                    class="px-3 py-2 text-gray-500 hover:text-red-500 border border-gray-300 dark:border-gray-600 rounded-xl">✕</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Команда</label>
                        <select x-model="formData.ministry_id"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Без команди</option>
                            @foreach($ministries as $m)
                                <option value="{{ $m['ministry']->id }}">{{ $m['ministry']->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Existing Attachments -->
                    <div x-show="existingAttachments.length > 0">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Прикріплені чеки</label>
                        <div class="space-y-2">
                            <template x-for="att in existingAttachments" :key="att.id">
                                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                     :class="{ 'opacity-50 line-through': deleteAttachments.includes(att.id) }">
                                    <div class="flex items-center gap-2">
                                        <template x-if="att.is_image">
                                            <img :src="att.url" class="w-16 h-16 object-cover rounded cursor-pointer hover:opacity-80 transition-opacity" @click="$dispatch('open-lightbox', att.url)">
                                        </template>
                                        <template x-if="!att.is_image">
                                            <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                        </template>
                                        <div>
                                            <template x-if="att.is_image">
                                                <a href="#" @click.prevent="$dispatch('open-lightbox', att.url)" class="text-sm text-primary-600 dark:text-primary-400 hover:underline" x-text="att.original_name"></a>
                                            </template>
                                            <template x-if="!att.is_image">
                                                <a :href="att.url" target="_blank" class="text-sm text-primary-600 dark:text-primary-400 hover:underline" x-text="att.original_name"></a>
                                            </template>
                                            <p class="text-xs text-gray-500" x-text="att.formatted_size"></p>
                                        </div>
                                    </div>
                                    <button type="button" @click="toggleDeleteAttachment(att.id)"
                                            class="p-1 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded"
                                            :class="{ 'bg-red-100 dark:bg-red-900/30': deleteAttachments.includes(att.id) }">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Додати чеки</label>
                        <input type="file" x-ref="fileInput" @change="handleFileSelect" multiple accept="image/*,.heic,.heif,.pdf"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-900/30 dark:file:text-primary-300">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Максимум 10 файлів по 10 МБ (JPG, PNG, HEIC, PDF)</p>
                            <!-- Selected files preview -->
                            <div x-show="selectedFiles.length > 0" class="mt-2 space-y-1">
                                <template x-for="(file, index) in selectedFiles" :key="index">
                                    <div class="flex items-center justify-between p-2 bg-green-50 dark:bg-green-900/20 rounded-lg text-sm">
                                        <span class="text-green-700 dark:text-green-300 truncate" x-text="file.name"></span>
                                        <button type="button" @click="removeFile(index)" class="p-1 text-red-600 hover:bg-red-50 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" @click="modalOpen = false"
                                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl">
                            Скасувати
                        </button>
                        <button type="submit" :disabled="loading"
                                class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl disabled:opacity-50">
                            <span x-show="!loading">Зберегти</span>
                            <span x-show="loading">Збереження...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
