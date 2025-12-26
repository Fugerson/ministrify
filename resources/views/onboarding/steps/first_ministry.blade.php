<div x-data="{
    form: {
        name: '',
        description: ''
    },
    errors: {},
    touched: {},

    validateField(field) {
        this.touched[field] = true;
        this.errors[field] = null;

        if (field === 'name' && this.form.name.trim()) {
            if (this.form.name.length > 255) {
                this.errors.name = 'Максимум 255 символів';
            }
        }
        if (field === 'description' && this.form.description.length > 1000) {
            this.errors.description = 'Максимум 1000 символів';
        }
    },

    selectSuggestion(name) {
        this.form.name = name;
        this.touched.name = true;
        this.validateField('name');
    },

    get hasErrors() {
        return Object.values(this.errors).some(e => e !== null);
    }
}">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shadow-lg shadow-primary-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400 mb-0.5">КРОК 3 - ОПЦІЙНО</p>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Створіть перше служіння</h2>
            </div>
        </div>
    </div>

    @if($ministries->count() > 0)
        <div class="mb-6 p-5 bg-gradient-to-br from-green-50 to-emerald-100/50 dark:from-green-900/20 dark:to-emerald-800/10 rounded-2xl border border-green-200/50 dark:border-green-700/30">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-green-800 dark:text-green-200">У вас вже є {{ $ministries->count() }} {{ trans_choice('служіння|служіння|служінь', $ministries->count()) }}</p>
                    <p class="text-sm text-green-600 dark:text-green-300">Ви можете пропустити цей крок або додати ще одне</p>
                </div>
            </div>
        </div>
    @else
        <div class="mb-6 p-5 bg-gradient-to-br from-blue-50 to-indigo-100/50 dark:from-blue-900/20 dark:to-indigo-800/10 rounded-2xl border border-blue-200/50 dark:border-blue-700/30">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-blue-800 dark:text-blue-200">Цей крок необов'язковий</p>
                    <p class="text-sm text-blue-600 dark:text-blue-300">Можете пропустити і створити служіння пізніше</p>
                </div>
            </div>
        </div>
    @endif

    <form class="space-y-6">
        <!-- Ministry Name -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Назва служіння
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </span>
                <input type="text" name="name"
                       x-model="form.name"
                       @blur="validateField('name')"
                       @input="touched.name && validateField('name')"
                       :class="{'ring-2 ring-red-500 border-red-500': errors.name, 'ring-2 ring-green-500 border-green-500': touched.name && !errors.name && form.name}"
                       class="w-full pl-12 pr-4 py-3 border border-gray-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all text-lg"
                       placeholder="Наприклад: Прославлення">
            </div>
            <div class="flex justify-between mt-2">
                <p x-show="errors.name" x-cloak class="text-sm text-red-500 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-text="errors.name"></span>
                </p>
                <p class="text-xs text-gray-400" x-show="form.name.length > 200">
                    <span x-text="form.name.length"></span>/255
                </p>
            </div>
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Опис</label>
            <div class="relative">
                <textarea name="description" rows="3"
                          x-model="form.description"
                          @blur="validateField('description')"
                          @input="touched.description && validateField('description')"
                          :class="{'ring-2 ring-red-500 border-red-500': errors.description, 'ring-2 ring-green-500 border-green-500': touched.description && !errors.description && form.description}"
                          class="w-full px-4 py-3 border border-gray-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 resize-none transition-all"
                          placeholder="Коротко опишіть це служіння..."></textarea>
            </div>
            <div class="flex justify-between mt-2">
                <p x-show="errors.description" x-cloak class="text-sm text-red-500 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-text="errors.description"></span>
                </p>
                <p class="text-xs text-gray-400" x-show="form.description.length > 0">
                    <span x-text="form.description.length"></span>/1000
                </p>
            </div>
        </div>

        <!-- Popular Suggestions -->
        <div class="p-5 bg-gradient-to-br from-gray-50 to-gray-100/50 dark:from-slate-700/50 dark:to-slate-800/50 rounded-2xl border border-gray-200/50 dark:border-slate-600/50">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                Популярні служіння
            </h4>
            <div class="flex flex-wrap gap-2">
                @foreach(['Прославлення', 'Звук та техніка', 'Дитяче служіння', 'Привітання', 'Молодь', 'Медіа'] as $suggestion)
                    <button type="button"
                            @click="selectSuggestion('{{ $suggestion }}')"
                            :class="form.name === '{{ $suggestion }}'
                                ? 'bg-gradient-to-r from-primary-500 to-primary-700 text-white border-transparent shadow-lg shadow-primary-500/30'
                                : 'bg-white dark:bg-slate-700 border-gray-200 dark:border-slate-600 text-gray-700 dark:text-gray-300 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-md'"
                            class="px-4 py-2 border rounded-xl text-sm font-medium transition-all duration-200 hover:scale-105">
                        {{ $suggestion }}
                    </button>
                @endforeach
            </div>
        </div>
    </form>
</div>
