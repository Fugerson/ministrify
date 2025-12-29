<div x-data="{
    selectedMinistries: [],
    customMinistry: '',

    toggleMinistry(name) {
        const index = this.selectedMinistries.indexOf(name);
        if (index > -1) {
            this.selectedMinistries.splice(index, 1);
        } else {
            this.selectedMinistries.push(name);
        }
    },

    isSelected(name) {
        return this.selectedMinistries.includes(name);
    },

    addCustom() {
        const trimmed = this.customMinistry.trim();
        if (trimmed && !this.selectedMinistries.includes(trimmed)) {
            this.selectedMinistries.push(trimmed);
            this.customMinistry = '';
        }
    },

    removeMinistry(name) {
        const index = this.selectedMinistries.indexOf(name);
        if (index > -1) {
            this.selectedMinistries.splice(index, 1);
        }
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
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Виберіть служіння</h2>
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
                    <p class="text-sm text-green-600 dark:text-green-300">Ви можете пропустити цей крок або додати ще</p>
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
        <!-- Hidden input to pass selected ministries -->
        <template x-for="(ministry, index) in selectedMinistries" :key="index">
            <input type="hidden" :name="'ministries[' + index + ']'" :value="ministry">
        </template>

        <!-- Popular Ministries Grid -->
        <div class="p-5 bg-gradient-to-br from-gray-50 to-gray-100/50 dark:from-slate-700/50 dark:to-slate-800/50 rounded-2xl border border-gray-200/50 dark:border-slate-600/50">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                Оберіть служіння
                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(можна декілька)</span>
            </h4>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach([
                    ['name' => 'Прославлення', 'icon' => '🎸'],
                    ['name' => 'Звук та техніка', 'icon' => '🎧'],
                    ['name' => 'Дитяче служіння', 'icon' => '👶'],
                    ['name' => 'Привітання', 'icon' => '👋'],
                    ['name' => 'Молодь', 'icon' => '🔥'],
                    ['name' => 'Медіа', 'icon' => '📸'],
                    ['name' => 'Молитва', 'icon' => '🙏'],
                    ['name' => 'Догляд', 'icon' => '❤️'],
                    ['name' => 'Євангелізм', 'icon' => '📖'],
                ] as $suggestion)
                    <button type="button"
                            @click="toggleMinistry('{{ $suggestion['name'] }}')"
                            :class="isSelected('{{ $suggestion['name'] }}')
                                ? 'bg-gradient-to-r from-primary-500 to-primary-700 text-white border-transparent shadow-lg shadow-primary-500/30 scale-[1.02]'
                                : 'bg-white dark:bg-slate-700 border-gray-200 dark:border-slate-600 text-gray-700 dark:text-gray-300 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-md'"
                            class="relative flex items-center gap-2 px-4 py-3 border rounded-xl text-sm font-medium transition-all duration-200">
                        <span class="text-lg">{{ $suggestion['icon'] }}</span>
                        <span>{{ $suggestion['name'] }}</span>
                        <span x-show="isSelected('{{ $suggestion['name'] }}')"
                              class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-green-500 text-white rounded-full flex items-center justify-center shadow-md">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Custom Ministry Input -->
        <div class="p-5 bg-white dark:bg-slate-700/50 rounded-2xl border border-gray-200 dark:border-slate-600">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Або додайте своє
            </h4>
            <div class="flex gap-2">
                <input type="text"
                       x-model="customMinistry"
                       @keydown.enter.prevent="addCustom()"
                       class="flex-1 px-4 py-2.5 border border-gray-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Назва служіння...">
                <button type="button"
                        @click="addCustom()"
                        :disabled="!customMinistry.trim()"
                        class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 dark:disabled:bg-slate-600 text-white font-medium rounded-xl transition-colors">
                    Додати
                </button>
            </div>
        </div>

        <!-- Selected Summary -->
        <div x-show="selectedMinistries.length > 0" x-cloak
             class="p-5 bg-gradient-to-br from-primary-50 to-primary-100/50 dark:from-primary-900/20 dark:to-primary-800/10 rounded-2xl border border-primary-200/50 dark:border-primary-700/30">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-semibold text-primary-800 dark:text-primary-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Обрано служінь: <span x-text="selectedMinistries.length"></span>
                </h4>
            </div>
            <div class="flex flex-wrap gap-2">
                <template x-for="ministry in selectedMinistries" :key="ministry">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm">
                        <span x-text="ministry"></span>
                        <button type="button" @click="removeMinistry(ministry)" class="text-gray-400 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </span>
                </template>
            </div>
        </div>
    </form>
</div>
