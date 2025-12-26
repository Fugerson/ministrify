<div x-data="{
    users: [],
    errors: {},

    validateUser(index) {
        const user = this.users[index];
        this.errors[index] = {};

        if (user.name && user.name.length > 255) {
            this.errors[index].name = 'Максимум 255 символів';
        }

        if (user.email && !this.isValidEmail(user.email)) {
            this.errors[index].email = 'Невірний email';
        }
    },

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    addUser() {
        this.users.push({ name: '', email: '', role: 'leader' });
    },

    removeUser(index) {
        this.users.splice(index, 1);
        delete this.errors[index];
    },

    get hasValidUsers() {
        return this.users.some(u => u.name.trim() && u.email.trim());
    },

    get totalErrors() {
        return Object.values(this.errors).reduce((sum, errs) => sum + Object.keys(errs || {}).length, 0);
    },

    getRoleGradient(role) {
        switch(role) {
            case 'admin': return 'from-red-500 to-rose-600';
            case 'leader': return 'from-amber-500 to-orange-600';
            default: return 'from-green-500 to-emerald-600';
        }
    },

    getRoleBg(role) {
        switch(role) {
            case 'admin': return 'from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 border-red-200 dark:border-red-800';
            case 'leader': return 'from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-amber-200 dark:border-amber-800';
            default: return 'from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-green-200 dark:border-green-800';
        }
    }
}">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shadow-lg shadow-primary-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400 mb-0.5">КРОК 5 - ОПЦІЙНО</p>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Налаштуйте ролі</h2>
            </div>
        </div>
    </div>

    @if($users->count() > 1)
        <div class="mb-6 p-5 bg-gradient-to-br from-blue-50 to-indigo-100/50 dark:from-blue-900/20 dark:to-indigo-800/10 rounded-2xl border border-blue-200/50 dark:border-blue-700/30">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Існуючі користувачі ({{ $users->count() }})</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($users as $user)
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 rounded-xl text-sm border border-gray-200 dark:border-slate-600 shadow-sm">
                                <span class="w-2 h-2 rounded-full {{ $user->role === 'admin' ? 'bg-red-500' : ($user->role === 'leader' ? 'bg-amber-500' : 'bg-green-500') }}"></span>
                                {{ $user->name }}
                            </span>
                        @endforeach
                    </div>
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
                    <p class="text-sm text-blue-600 dark:text-blue-300">Можете пропустити і запросити користувачів пізніше</p>
                </div>
            </div>
        </div>
    @endif

    <form class="space-y-6">
        <!-- Users list -->
        <template x-for="(user, index) in users" :key="index">
            <div class="p-5 rounded-2xl border transition-all duration-300 bg-gradient-to-br" :class="getRoleBg(user.role)">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center shadow bg-gradient-to-br text-white" :class="getRoleGradient(user.role)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <template x-if="user.role === 'admin'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </template>
                                <template x-if="user.role === 'leader'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </template>
                                <template x-if="user.role === 'volunteer'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </template>
                            </svg>
                        </span>
                        Користувач <span x-text="index + 1"></span>
                    </span>
                    <button type="button"
                            @click="removeUser(index)"
                            class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 p-2 rounded-xl transition-all hover:scale-110">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            <input type="text"
                                   :name="'users[' + index + '][name]'"
                                   x-model="user.name"
                                   @blur="validateUser(index)"
                                   :class="errors[index]?.name ? 'ring-2 ring-red-500 border-red-500' : user.name ? 'ring-2 ring-green-500 border-green-500' : ''"
                                   placeholder="Ім'я"
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                        </div>
                        <p x-show="errors[index]?.name" x-text="errors[index]?.name" class="mt-1.5 text-xs text-red-500"></p>
                    </div>
                    <div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input type="email"
                                   :name="'users[' + index + '][email]'"
                                   x-model="user.email"
                                   @blur="validateUser(index)"
                                   :class="errors[index]?.email ? 'ring-2 ring-red-500 border-red-500' : user.email ? 'ring-2 ring-green-500 border-green-500' : ''"
                                   placeholder="Email"
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                        </div>
                        <p x-show="errors[index]?.email" x-text="errors[index]?.email" class="mt-1.5 text-xs text-red-500"></p>
                    </div>
                    <div>
                        <select :name="'users[' + index + '][role]'"
                                x-model="user.role"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all appearance-none cursor-pointer">
                            <option value="admin">Адміністратор</option>
                            <option value="leader">Лідер</option>
                            <option value="volunteer">Волонтер</option>
                        </select>
                    </div>
                </div>
            </div>
        </template>

        <button type="button"
                @click="addUser()"
                class="w-full py-4 border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-2xl text-gray-500 dark:text-gray-400 hover:border-primary-400 dark:hover:border-primary-600 hover:text-primary-500 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-all duration-300 flex items-center justify-center gap-2 group">
            <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-slate-700 group-hover:bg-gradient-to-br group-hover:from-primary-500 group-hover:to-emerald-600 flex items-center justify-center transition-all">
                <svg class="w-5 h-5 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <span class="font-medium">Запросити користувача</span>
        </button>

        <!-- Users counter -->
        <div x-show="hasValidUsers" class="p-4 bg-gradient-to-r from-primary-50 to-emerald-50 dark:from-primary-900/20 dark:to-emerald-900/20 rounded-xl text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-bold text-primary-600 dark:text-primary-400 text-lg" x-text="users.filter(u => u.name.trim() && u.email.trim()).length"></span> користувачів буде запрошено
            </p>
        </div>

        <!-- Role Descriptions -->
        <div class="pt-6 border-t border-gray-200 dark:border-slate-700">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                Опис ролей
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-2xl border border-red-200/50 dark:border-red-700/30">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-red-700 dark:text-red-300">Адміністратор</span>
                    </div>
                    <p class="text-sm text-red-600 dark:text-red-400">Повний доступ до всіх функцій системи</p>
                </div>
                <div class="p-4 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-2xl border border-amber-200/50 dark:border-amber-700/30">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-amber-700 dark:text-amber-300">Лідер</span>
                    </div>
                    <p class="text-sm text-amber-600 dark:text-amber-400">Управління своїм служінням та командою</p>
                </div>
                <div class="p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl border border-green-200/50 dark:border-green-700/30">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-green-700 dark:text-green-300">Волонтер</span>
                    </div>
                    <p class="text-sm text-green-600 dark:text-green-400">Перегляд свого розкладу та завдань</p>
                </div>
            </div>
        </div>
    </form>
</div>
