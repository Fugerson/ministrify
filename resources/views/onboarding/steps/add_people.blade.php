<div x-data="{
    people: [{ first_name: '', last_name: '', email: '', phone: '' }],
    errors: {},

    validatePerson(index) {
        const person = this.people[index];
        this.errors[index] = {};

        if (person.email && !this.isValidEmail(person.email)) {
            this.errors[index].email = 'Невірний email';
        }

        if (person.phone && !this.isValidPhone(person.phone)) {
            this.errors[index].phone = 'Невірний телефон';
        }
    },

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    isValidPhone(phone) {
        return /^[\d\s\+\-\(\)]{7,20}$/.test(phone);
    },

    addPerson() {
        this.people.push({ first_name: '', last_name: '', email: '', phone: '' });
    },

    removePerson(index) {
        if (this.people.length > 1) {
            this.people.splice(index, 1);
            delete this.errors[index];
        }
    },

    get hasValidPeople() {
        return this.people.some(p => p.first_name.trim() !== '');
    },

    get totalErrors() {
        return Object.values(this.errors).reduce((sum, errs) => sum + Object.keys(errs || {}).length, 0);
    }
}">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shadow-lg shadow-primary-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400 mb-0.5">КРОК 4 - ОПЦІЙНО</p>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Додайте людей</h2>
            </div>
        </div>
    </div>

    @if($peopleCount > 0)
        <div class="mb-6 p-5 bg-gradient-to-br from-green-50 to-emerald-100/50 dark:from-green-900/20 dark:to-emerald-800/10 rounded-2xl border border-green-200/50 dark:border-green-700/30">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-green-800 dark:text-green-200">У вас вже є {{ $peopleCount }} {{ trans_choice('людина|людини|людей', $peopleCount) }}</p>
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
                    <p class="text-sm text-blue-600 dark:text-blue-300">Можете пропустити і додати людей пізніше</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Mode Selector -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <button type="button"
                @click="mode = 'manual'"
                :class="mode === 'manual'
                    ? 'bg-gradient-to-br from-primary-50 to-emerald-50 dark:from-primary-900/30 dark:to-emerald-900/20 border-primary-500 shadow-lg'
                    : 'bg-white dark:bg-slate-700 border-gray-200 dark:border-slate-600 hover:border-gray-300 hover:shadow-md'"
                class="group flex items-center justify-center gap-3 p-5 border-2 rounded-2xl transition-all duration-300">
            <div :class="mode === 'manual' ? 'bg-gradient-to-br from-primary-500 to-emerald-600' : 'bg-gray-100 dark:bg-slate-600'"
                 class="w-10 h-10 rounded-xl flex items-center justify-center transition-all">
                <svg :class="mode === 'manual' ? 'text-white' : 'text-gray-500 dark:text-gray-400'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <span :class="mode === 'manual' ? 'text-primary-700 dark:text-primary-300 font-semibold' : 'text-gray-700 dark:text-gray-300'" class="transition-colors">Вручну</span>
        </button>
        <a href="{{ route('migration.planning-center') }}"
                class="group flex items-center justify-center gap-3 p-5 border-2 rounded-2xl transition-all duration-300 bg-white dark:bg-slate-700 border-gray-200 dark:border-slate-600 hover:border-primary-300 hover:shadow-lg hover:scale-[1.02]">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all bg-gray-100 dark:bg-slate-600 group-hover:bg-gradient-to-br group-hover:from-primary-500 group-hover:to-emerald-600">
                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
            </div>
            <div class="text-left">
                <span class="text-gray-700 dark:text-gray-300 group-hover:text-primary-600 dark:group-hover:text-primary-400 font-medium transition-colors block">Імпорт CSV</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">з мапінгом колонок</span>
            </div>
            <svg class="w-4 h-4 text-gray-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
    </div>

    <form class="space-y-6">
        <input type="hidden" name="mode" value="manual">

        <!-- Manual Entry -->
        <div>
            <template x-for="(person, index) in people" :key="index">
                <div class="mb-4 p-5 bg-gradient-to-br from-gray-50 to-gray-100/50 dark:from-slate-700/50 dark:to-slate-800/50 rounded-2xl border border-gray-200/50 dark:border-slate-600/50">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-primary-500 to-emerald-600 text-white flex items-center justify-center text-sm font-bold shadow" x-text="index + 1"></span>
                            Людина
                        </span>
                        <button type="button"
                                x-show="people.length > 1"
                                @click="removePerson(index)"
                                class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 p-2 rounded-xl transition-all hover:scale-110">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            <input type="text"
                                   :name="'people[' + index + '][first_name]'"
                                   x-model="person.first_name"
                                   placeholder="Ім'я"
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            <input type="text"
                                   :name="'people[' + index + '][last_name]'"
                                   x-model="person.last_name"
                                   placeholder="Прізвище"
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                        </div>
                        <div>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <input type="email"
                                       :name="'people[' + index + '][email]'"
                                       x-model="person.email"
                                       @blur="validatePerson(index)"
                                       :class="errors[index]?.email ? 'ring-2 ring-red-500 border-red-500' : ''"
                                       placeholder="Email"
                                       class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                            </div>
                            <p x-show="errors[index]?.email" x-text="errors[index]?.email" class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-text="errors[index]?.email"></span>
                            </p>
                        </div>
                        <div>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </span>
                                <input type="tel"
                                       :name="'people[' + index + '][phone]'"
                                       x-model="person.phone"
                                       @blur="validatePerson(index)"
                                       :class="errors[index]?.phone ? 'ring-2 ring-red-500 border-red-500' : ''"
                                       placeholder="Телефон"
                                       class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                            </div>
                            <p x-show="errors[index]?.phone" x-text="errors[index]?.phone" class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-text="errors[index]?.phone"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </template>

            <button type="button"
                    @click="addPerson()"
                    class="w-full py-4 border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-2xl text-gray-500 dark:text-gray-400 hover:border-primary-400 dark:hover:border-primary-600 hover:text-primary-500 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-all duration-300 flex items-center justify-center gap-2 group">
                <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-slate-700 group-hover:bg-gradient-to-br group-hover:from-primary-500 group-hover:to-emerald-600 flex items-center justify-center transition-all">
                    <svg class="w-5 h-5 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <span class="font-medium">Додати ще людину</span>
            </button>

            <!-- People counter -->
            <div x-show="hasValidPeople" class="mt-4 p-4 bg-gradient-to-r from-primary-50 to-emerald-50 dark:from-primary-900/20 dark:to-emerald-900/20 rounded-xl text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-bold text-primary-600 dark:text-primary-400 text-lg" x-text="people.filter(p => p.first_name.trim()).length"></span> людей буде додано
                </p>
            </div>
        </div>

    </form>
</div>
